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
        ignoreImages = true
	} = {}) {
		this.launchOptions = launchOptions;
        this.ignoreCss = ignoreCss;
        this.ignoreJs = ignoreJs;
        this.ignoreImages = ignoreImages;
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
            var uri = resource.getUrl().split('?')[0];
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
            const excludedPhases = ['dns', 'tcp', 'tls', 'request']; // Fases a serem excluídas
            const totalExcludedTime = excludedPhases.reduce((acc, phase) => {
                return acc + (response.timings.phases[phase] || 0); // Soma os tempos das fases excluídas
            }, 0);
            const serverProcessingTime = response.timings.phases.total - totalExcludedTime;
            if(this.serverRequestTimeMap[url] !== undefined){
                this.serverRequestTimeMap[url].push({ 
                    serverProcessingTime: serverProcessingTime,
                    timings: response.timings.phases
                });
            }else{
                this.serverRequestTimeMap[url] = [];
                this.serverRequestTimeMap[url].push({ 
                    serverProcessingTime: serverProcessingTime,
                    timings: response.timings.phases
                });
            }
            logger.info('Gravando tempo de resposta:', { url });
            logger.info('O tempo de resposta do servidor foi:', { serverProcessingTime });
            //console.log(response.timings.phases)
            return response;
        });
        registerAction('afterFinish', async () => {
            this.resultadoJson.possibleCMS = this.possibleCMS;
            this.resultadoJson.possibleCMSType = this.possibleCMSType;
            this.resultadoJson.serverRequestTimeMap = this.serverRequestTimeMap;
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