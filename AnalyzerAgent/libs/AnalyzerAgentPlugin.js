import fs from 'fs';
import puppeteer from 'puppeteer';
import * as cheerio from 'cheerio';
import isDocker from '../Utils/is_docker.js';
import logger from './AnalyzerAgentPluginLogger.js';
const img_extensions = [
    ".jpg",
    ".jpeg",
    ".png",
    ".gif",
    ".bmp",
    ".svg",
    ".webp",
    ".ico",
    // Adicione outras extensões de imagem aqui, se necessário
];

const cmsWpIdentifiersInUri = [
    'wp-content',
    'wp-includes',
    'wp-admin'
]
class AnalyzerAgentPlugin {
    
	constructor ({
		launchOptions = {},
        ignores = {},
        result_filename = 'resultado.json',
        all_times = false,
        useGot = false,
        resultHelper = undefined
	} = {}) {
        /**
         * Plugin options
         */
		this.launchOptions = launchOptions;
        this.ignores = ignores;
        this.resultadoPath = './result/' + result_filename;
        this.all_times = all_times;
        
        /**
         * Load resultado.json
         */
        this.resultadoJson = JSON.parse(fs.readFileSync(this.resultadoPath, 'utf8'));
        this.serverRequestTimeMap = this.resultadoJson.serverRequestTimeMap;

        /**
         * CMS Verifications
         */
        this.possibleCMS = false;
        this.possibleCMSType = '';
        this.possibleCMSVerison = '';

        /**
         * Waf Verifications
         */
        this.behindWAF = false;
        this.behindWAFType = '';

        /**
         * Puppeteer
         */
        this.use_puppeteer = !useGot;

        /**
         * resultHelper
         */
        this.resultHelper = resultHelper;

        /**
         * Logger
         */
        logger.info('init plugin', { launchOptions });
	}

	apply (registerAction) {
        registerAction('beforeStart', async () => {
            if(this.use_puppeteer){
                /**
                 * Setup puppeteer
                 */
                // Configuração do navegador
                if(isDocker()){
                    this.browser = await puppeteer.launch({
                        headless: true, // Defina como false se quiser ver o navegador em ação
                        executablePath: '/usr/bin/chromium-browser',
                        args: ['--no-sandbox', '--disable-setuid-sandbox', '--headless', '--disable-gpu']
                    });
                }else{
                    this.browser = await puppeteer.launch({
                        headless: true, // Defina como false se quiser ver o navegador em ação
                        args: ['--no-sandbox', '--disable-setuid-sandbox']
                    });
                }
            }
		});
		registerAction('beforeRequest', async ({resource, requestOptions}) => {
            /**
             * Identify if the request is a wordpress
             */
            var uri = resource.getUrl().split('?')[0].toLowerCase();
            this.identifyWp(uri);
            /**
             * Verify if the request is a css, js, image or font file
             * and ignore it if necessary
             */
            if(await this.ignoreLink(uri)){
                return Promise.reject(new Error('Solicitação cancelada'));
            }
            logger.info('Prosseguindo url:', { uri });

            if(this.use_puppeteer){
                // Crie uma nova página
                this.page = await this.browser.newPage();
                requestOptions.puppeteerPage = this.page;
            }
			return {requestOptions};
		});
        /**
         * Prevents the resource from being saved
         */
        registerAction('saveResource', async ({resource}) => {return false;});
        registerAction('afterResponse', ({response}) => {
            /**
             * Save the server processing times
             */
            const url = response.url
            var serverProcessingTime = 0

            if(!this.use_puppeteer){
                serverProcessingTime = this.parseRequestTimngsGot(response)
            }else{
                serverProcessingTime = this.parseRequestTimngsAndParamsPuppeteer(response)
            }
            return response;
        });
        registerAction('afterFinish', async () => {
            this.resultadoJson.possibleCMS = this.possibleCMS;
            this.resultadoJson.run = this.resultadoJson.run + 1;
            this.resultadoJson.possibleCMSType = this.possibleCMSType;
            this.resultadoJson.serverRequestTimeMap = this.serverRequestTimeMap;
            this.resultadoJson.behindWAF = this.behindWAF;
            this.resultadoJson.behindWAFType = this.behindWAFType;
            /**
             * Save the result
             */
            await this.resultHelper.saveAfterRun(this.resultadoJson, this.resultadoPath);
            /**
             * Close the browser
             */
            if(this.browser){
                this.browser.close();
            }
        });
	}
    /**
     * Identifica se a url é um wordpress
     * @param {*} uri
     */
    identifyWp(uri){
        if(this.possibleCMSType === ''){
            for (const identifier of cmsWpIdentifiersInUri) {
                if (uri.includes(identifier)) {
                    this.possibleCMS = true;
                    this.possibleCMSType = 'WordPress';
                    logger.info('Possivel wordpress identificado por ' + identifier + " na url ", { uri });
                }
            }
        }
    }
    /**
     * Obtem as informações dos formulários encontrados na página
     * @param {*} url 
     * @param {*} content 
     */
    getForms(url, content){

        // Carregue o HTML com cheerio
        const $ = cheerio.load(content);
        // Armazena os dados dos formulários encontrados
        let forms = [];

        $('form').each((i, form) => {
            const action = $(form).attr('action') || url;
            const method = ($(form).attr('method') || 'GET').toUpperCase();
            // Coletando todos os campos do formulário
            let formData = {};
            $(form).find('input, textarea, select').each((j, field) => {
                const name = $(field).attr('name');
                if (name) {
                    const value = $(field).attr('value') || '';
                    formData[name] = {
                        type: $(field).attr('type') || $(field).prop("tagName").toLowerCase(),
                        value: value
                    };
                    if (formData[name].type === 'select') {
                        formData[name].options = [];
                        $(field).find('option').each((k, option) => {
                            formData[name].options.push({
                                value: $(option).attr('value'),
                                text: $(option).text(),
                                selected: $(option).is(':selected')
                            });
                        });
                    }
                }
            });
            forms.push({
                params: {
                    method: method,
                    url: action,
                    headers: {}, // Adicione cabeçalhos conforme necessário
                    formData: formData
                }
            });

        });

        // Se a propriedade forms já existe, apenas concatena os novos formulários
        if (this.serverRequestTimeMap[url].forms !== undefined) {
            this.serverRequestTimeMap[url].forms = [
                ...this.serverRequestTimeMap[url].forms,
                ...forms
            ];
        } else {
            this.serverRequestTimeMap[url].forms = forms;
        }
    }
    /**
     * Obtem o tempo de resposta do servidor para a requisição Puppeteer
     * @param {*} response
     * @returns
     */
    parseRequestTimngsAndParamsPuppeteer(response){
        const url = response.url
        var serverProcessingTime = 0
        if (typeof response.timing.receiveHeadersStart === 'number' && typeof response.timing.sendEnd === 'number') {
            if(this.serverRequestTimeMap[url] !== undefined){
                this.serverRequestTimeMap[url].times.push({ 
                    serverProcessingTime: response.timing.receiveHeadersStart - response.timing.sendEnd,
                    timings: this.all_times ? response.timing : null
                });
            }else{
                this.serverRequestTimeMap[url] = {}
                this.serverRequestTimeMap[url].times = [];
                this.serverRequestTimeMap[url].times.push({ 
                    serverProcessingTime: response.timing.receiveHeadersStart - response.timing.sendEnd,
                    timings: this.all_times ? response.timing : null
                });
            }
            if(response.wafEvidence){
                this.behindWAF = true;
                this.behindWAFType = response.behindWAFType;
            }
            if(Object.keys(response.postCalls).length > 0){
                if(this.serverRequestTimeMap[url].postCalls !== undefined){
                    this.serverRequestTimeMap[url].postCalls = { ...this.serverRequestTimeMap[url].postCalls, ...response.postCalls};
                }else{
                    this.serverRequestTimeMap[url].postCalls = response.postCalls;
                }
            }
            this.getForms(url, response.body);
            console.log("O tempo para o primeiro byte da url " + response.url + " foi de " + (response.timing.receiveHeadersStart - response.timing.sendEnd));
            logger.info('Gravando tempo de resposta:', { url });
            logger.info('O tempo de resposta do servidor foi:', { serverProcessingTime });
        }
        return serverProcessingTime;
    }
    /**
     * Obtem o tempo de resposta do servidor para a requisição Got
     * @param {*} response 
     * @returns 
     */
    parseRequestTimngsGot(response){
        const url = response.url
        var serverProcessingTime = 0
        if(this.serverRequestTimeMap[url] === undefined || this.resultadoJson.run > this.serverRequestTimeMap[url].times.length){
            if(response.timings.secureConnect !== undefined){
                const excludeUpload = response.timings.upload - response.timings.secureConnect
                serverProcessingTime = (response.timings.response - response.timings.secureConnect) - excludeUpload;
            }else{
                const excludeUpload = response.timings.upload - response.timings.connect
                serverProcessingTime = (response.timings.response - response.timings.connect) - excludeUpload;
            }
            if(this.serverRequestTimeMap[url] !== undefined){
                this.serverRequestTimeMap[url].times.push({ 
                    serverProcessingTime: serverProcessingTime,
                    timings: this.all_times ? response.timings : null
                });
            }else{
                this.serverRequestTimeMap[url] = {}
                this.serverRequestTimeMap[url].times = [];
                this.serverRequestTimeMap[url].times.push({ 
                    serverProcessingTime: serverProcessingTime,
                    timings: this.all_times ? response.timings : null
                });
            }
            console.log("O tempo para o primeiro byte da url " + url + " foi de " + serverProcessingTime);
            logger.info('Gravando tempo de resposta:', { url });
            logger.info('O tempo de resposta do servidor foi:', { serverProcessingTime });
        }
        return serverProcessingTime;
    }
    /**
     * Ignora links de acordo com as configurações
     * @param {*} uri 
     * @returns 
     */
    ignoreLink(uri){
        if(this.ignores.ignoreCss && uri.endsWith(".css")){
            logger.info('Ignorando css:', { uri });
            return true;
        }else if (this.ignores.ignoreJs && uri.endsWith(".js")){
            logger.info('Ignorando js:', { uri });
            return true;
        }else if (this.ignores.ignoreFontFiles && (uri.endsWith(".eot") || uri.endsWith(".ttf") || uri.endsWith(".woff2") || uri.endsWith(".woff"))){
            logger.info('Ignorando font:', { uri });
            return true;
        }else if (this.ignores.ignoreVideoFiles && uri.endsWith(".mp4")){
            logger.info('Ignorando video:', { uri });
            return true;
        }else if (this.ignores.ignoreImages){
            for (const extension of img_extensions) {
                if (uri.endsWith(extension)){
                    logger.info('Ignorando imagem:', { uri });
                    return true;
                }
            }
        }
    }
    async delayRequest(){
        const time = Math.round(Math.random() * 10000);
        await new Promise((resolve) => setTimeout(resolve, time));
    }
}
export default AnalyzerAgentPlugin;