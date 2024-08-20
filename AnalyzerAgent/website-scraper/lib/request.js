import got from 'got';
import logger from './logger.js';
import { extend } from './utils/index.js';
import { makeRequest } from '../../Utils/puppeter-requests.js';

function getMimeType (contentType) {
	return contentType ? contentType.split(';')[0] : null;
}

function defaultResponseHandler ({response}) {
	return Promise.resolve(response);
}

function extractEncodingFromHeader (headers) {
	const contentTypeHeader = headers['content-type'];

	return contentTypeHeader && contentTypeHeader.includes('utf-8') ? 'utf8' : 'binary';
}

function getEncoding (response) {
	if (response && typeof response === 'object') {
		if (response.headers && typeof response.headers === 'object') {
			return extractEncodingFromHeader(response.headers);
		} else if (response.encoding) {
			return response.encoding;
		}
	}

	return 'binary';
}

function throwTypeError (result) {
	let type = typeof result;

	if (result instanceof Error) {
		throw result;
	} else if (type === 'object' && Array.isArray(result)) {
		type = 'array';
	}

	throw new Error(`Wrong response handler result. Expected string or object, but received ${type}`);
}

function getData (result) {
	let data = result;
	if (result && typeof result === 'object' && 'body' in result) {
		data = result.body;
	}

	return data;
}

function transformResult (result) {
	const encoding = getEncoding(result);
	const data = getData(result);

	// Check for no data
	if (data === null || data === undefined) {
		return null;
	}

	// Then stringify it.
	let body = null;
	if (data instanceof Buffer) {
		body = data.toString(encoding);
	} else if (typeof data === 'string') {
		body = data;
	} else {
		throwTypeError(result);
	}

	return {
		body,
		encoding,
		metadata: result.metadata || data.metadata || null
	};
}

async function getRequest ({url, referer, options = {}, afterResponse = defaultResponseHandler, urlFilter = undefined}) {
	const requestOptions = extend(options, {url});
	if (referer) {
		requestOptions.headers = requestOptions.headers || {};
		requestOptions.headers.referer = referer;
	}

	logger.debug(`[request] sending request for url ${url}, referer ${referer}`);

	if(requestOptions.puppeteerPage){
		// Variables to store the MIME type and status code
		let content = null;
		let mimeType;
		let statusCode;
		let encoding;
		let metaDate;
		let timing = {};
		let urlToAdd = [];
		let wafEvidence = false;
		let behindWAFType = '';
		await requestOptions.puppeteerPage.setExtraHTTPHeaders(requestOptions.headers);
		// Listen to the 'response' event but store the values for later use
		requestOptions.puppeteerPage.on('response', (response) => {
			if (response.url() === url) {
				if(response.headers()['content-type']){
					mimeType = response.headers()['content-type'].split(';')[0];
				}
				statusCode = response.status();
			}
		});
	
		requestOptions.puppeteerPage.on('requestfinished', async (request) => {
			if (request.url() === url) {
				const response = await request.response();
				timing = JSON.parse(JSON.stringify(response.timing()));
			}else if(
				urlFilter 
				&& request.method() == 'GET' 
				&& urlFilter(request.url()) 
				&& !request.url().startsWith('data:')
				&& !request.url().startsWith('blob:')
			){
				urlToAdd.push(request.url());
			}else if(
				urlFilter  
				&& urlFilter(request.url()) 
				&& !request.url().startsWith('data:')
				&& !request.url().startsWith('blob:')
			){
				if(request.url().indexOf('cdn-cgi') == -1){
					const headers = request.headers();
					const postData = request.postData(); // Captura o corpo da requisição (se existir)

					// Objeto para armazenar os parâmetros do comando curl
					const requestParams = {
						method: request.method(),
						url: request.url(),
						headers,
						body: postData || null
					};

					// Gerando o comando curl a partir dos parâmetros
					let curlCommand = `curl -X ${requestParams.method} '${requestParams.url}'${Object.entries(headers).map(([key, value]) => ` -H '${key}: ${value}'`).join('')}`;

					// Adiciona o corpo da requisição ao comando curl, se existir
					if (postData) {
						curlCommand += ` --data '${postData}'`;
					}

					// Salvando o objeto e o comando curl juntos
					const curlInfo = {
						command: curlCommand,
						params: requestParams
					};

					// Exibindo no console
					//console.log(`URL: ${request.url()} METHOD: ${request.method()} Incompativel`);
					//console.log('Objeto de Parâmetros:', curlInfo.params);
					//console.log('Comando CURL:', curlInfo.command);
				}else{
					wafEvidence = true;
					behindWAFType = 'Cloudflare';
				}
				//console.log(`URL: ${request.url()} METHOD: ${request.method()} Incompativel`);
			}
		});

		
		await requestOptions.puppeteerPage.goto(url, { waitUntil: 'networkidle2' });
		// Aguarda a captura do timing antes de continuar
		content = await requestOptions.puppeteerPage.content();
		
		content = content.replace('</body>', urlToAdd.map(url => `<a href='${url}'></a>`).join('') + '</body>');
		// Extract meta tags from the HTML
		encoding = await requestOptions.puppeteerPage.evaluate(await function () {
			const charsetMeta = document.querySelector('meta[charset]');
			const contentTypeMeta = document.querySelector('meta[http-equiv="Content-Type"]');
			return charsetMeta ? charsetMeta.getAttribute('charset') : (contentTypeMeta ? contentTypeMeta.getAttribute('content').split('charset=')[1] : null);
		});
	
		metaDate = await requestOptions.puppeteerPage.evaluate(await function ()  {
			const dateMeta = document.querySelector('meta[name="date"]') || document.querySelector('meta[property="article:published_time"]');
			return dateMeta ? dateMeta.getAttribute('content') : null;
		});
		await requestOptions.puppeteerPage.close();
		const response = {
			url: url,
			mimeType: mimeType,
			body: content,
			metadata: metaDate,
			encoding: encoding,
			timing: timing,
			wafEvidence: wafEvidence,
			behindWAFType: behindWAFType
		};
		const responseHandlerResult = await afterResponse({response});
		return {
			url: url,
			mimeType: mimeType,
			body: content,
			metadata: metaDate,
			encoding: encoding
		};
	}else{
		const response = await got(requestOptions);
		const responseHandlerResult = transformResult(await afterResponse({response}));
		logger.debug(`[request] received response for ${response.url}, statusCode ${response.statusCode}`);
		if (!responseHandlerResult) {
			return null;
		}
		return {
			url: response.url,
			mimeType: getMimeType(response.headers['content-type']),
			body: responseHandlerResult.body,
			metadata: responseHandlerResult.metadata,
			encoding: responseHandlerResult.encoding
		};
	}


}

export default {
	get: getRequest,
	getEncoding,
	transformResult
};
