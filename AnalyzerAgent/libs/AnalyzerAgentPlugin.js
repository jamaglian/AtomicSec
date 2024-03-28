import logger from './AnalyzerAgentPluginLogger.js';
const cmsWpIdentifiersInUri = [
    'wp-content',
    'wp-includes',
    'wp-admin'
]
class AnalyzerAgentPlugin {
    
	constructor ({
		launchOptions = {}
	} = {}) {
		this.launchOptions = launchOptions;
        this.possibleCMS = false;
        this.possibleCMSType = '';
		logger.info('init plugin', { launchOptions });
	}

	apply (registerAction) {
		registerAction('beforeRequest', async ({requestOptions}) => {
            var uri = requestOptions.headers.referer;
            if(uri !== undefined){
                for (const identifier in cmsWpIdentifiersInUri) {
                    if (uri.includes(identifier)) {
                        this.possibleCMS = true;
                        this.possibleCMSType = 'WordPress';
                    }
                }
            }
			return {requestOptions};
		});
        registerAction('saveResource', async ({resource}) => {return false;});
        registerAction('afterFinish', async () => {
            if(this.possibleCMS){
                console.log("Possivelmente uma aplicaçao:", this.possibleCMSType)
            }
        });
	}
}
export default AnalyzerAgentPlugin;