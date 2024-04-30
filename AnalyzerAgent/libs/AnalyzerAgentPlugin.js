import fs from 'fs';
import logger from './AnalyzerAgentPluginLogger.js';
const img_extensions = [
    ".jpg",
    ".jpeg",
    ".png",
    ".gif",
    ".bmp",
    ".svg",
    ".webp",
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
        ignoreCss = true,
        ignoreJs = true,
        ignoreImages = true,
        all_times = false
	} = {}) {
		this.launchOptions = launchOptions;
        this.ignoreCss = ignoreCss;
        this.ignoreJs = ignoreJs;
        this.ignoreImages = ignoreImages;
        this.all_times = all_times;
        this.possibleCMS = false;
        this.resultadoPath = 'resultado.json';
        this.resultadoJson = JSON.parse(fs.readFileSync(this.resultadoPath, 'utf8'));
        this.possibleCMSType = '';
        this.possibleCMSVerison = '';
        this.serverRequestTimeMap = this.resultadoJson.serverRequestTimeMap;
		logger.info('init plugin', { launchOptions });
	}

	apply (registerAction) {
		registerAction('beforeRequest', async ({resource, requestOptions}) => {
            var uri = resource.getUrl().split('?')[0].toLowerCase();
            this.identifyWp(uri);
            if(this.ignoreCss && uri.endsWith(".css")){
                logger.info('Ignorando css:', { uri });
                return Promise.reject(new Error('Solicitação cancelada'));
            }else if (this.ignoreJs && uri.endsWith(".js")){
                logger.info('Ignorando js:', { uri });
                return Promise.reject(new Error('Solicitação cancelada'));
            }else if (this.ignoreImages){
                for (const extension of img_extensions) {
                    if (uri.endsWith(extension)){
                        logger.info('Ignorando imagem:', { uri });
                        return Promise.reject(new Error('Solicitação cancelada'));
                    }
                }
            }
            logger.info('Prosseguindo url:', { uri });
			return {requestOptions};
		});
        registerAction('saveResource', async ({resource}) => {return false;});
        registerAction('afterResponse', ({response}) => {
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
                logger.info('Gravando tempo de resposta:', { url });
                logger.info('O tempo de resposta do servidor foi:', { serverProcessingTime });
            }
            return response;
        });
        registerAction('afterFinish', async () => {
            this.resultadoJson.possibleCMS = this.possibleCMS;
            this.resultadoJson.run = this.resultadoJson.run + 1;
            this.resultadoJson.possibleCMSType = this.possibleCMSType;
            this.resultadoJson.serverRequestTimeMap = this.serverRequestTimeMap;
            var topValores = []; // array para armazenar os top 3 valores e URLs correspondentes

            Object.keys(this.resultadoJson.serverRequestTimeMap).forEach(index => {
                var media = 0;
                this.resultadoJson.serverRequestTimeMap[index].times.forEach(unitMap => {
                    media += unitMap.serverProcessingTime;
                });
                this.resultadoJson.serverRequestTimeMap[index].media = media / (this.resultadoJson.run - 1);

                // Adicionar ao array dos top valores se houver menos de 3 ou se a nova média for maior que a menor dos top valores
                if (topValores.length < 3 || this.resultadoJson.serverRequestTimeMap[index].media > topValores[2].valor) {
                    topValores.push({ url: index, valor: this.resultadoJson.serverRequestTimeMap[index].media });
                    // Ordenar os top valores pelo valor em ordem decrescente
                    topValores.sort((a, b) => b.valor - a.valor);
                    // Se a lista tiver mais de 3 valores, remover o último (menor) valor
                    if (topValores.length > 3) {
                        topValores.pop();
                    }
                }
            });

            console.log("Top 3 maiores valores:");
            topValores.forEach(item => {
                console.log(`URL: ${item.url}, Valor: ${item.valor}`);
            });
            //console.log(this.resultadoJson);
            const novoJsonString = JSON.stringify(this.resultadoJson, null, 2); // O terceiro argumento é para formatar a saída
            fs.writeFileSync(this.resultadoPath, novoJsonString);
        });
	}

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

    async delayRequest(){
        const time = Math.round(Math.random() * 10000);
        await new Promise((resolve) => setTimeout(resolve, time));
    }
}
export default AnalyzerAgentPlugin;