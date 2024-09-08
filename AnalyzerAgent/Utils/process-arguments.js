import {config} from './config.js'; // Use import em vez de require

export const testAndParseArguments = async () => {
    const regex = /^(?:https?|ftp):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/;

    if(process.argv.length < 3 || !regex.test(process.argv[2])){
      console.log("Utilize \"node index.js https://site.para.testar/ [argumentos]\"");
      console.log("-includeAsset=css (pode utilizar 'css,js,img')");
      console.log("-all_times (para mostrar o tempo de requisição completo)");
      console.log("-use_got (para utilizar o pacote got ao invés do puppeteer)");
      process.exit(1); // Encerra o programa com código de erro
    }

    config.set('url_scrape', process.argv[2]);

    if(process.argv.length > 3){
        const pluginsConfig = config.get('plugin_configuration');
        for (const element of process.argv) {
            const startIndexIncludeCustomResultFile = element.indexOf("--result_filename=");
            if(startIndexIncludeCustomResultFile !== -1){
                var result = element.substring(startIndexIncludeCustomResultFile + "--result_filename=".length);
                config.set('result_filename', result + ".json");
            }
            const startIndexIncludeAsset = element.indexOf("-includeAsset=");
            if(startIndexIncludeAsset !== -1){
                const depois = element.slice(startIndexIncludeAsset + "-includeAsset=".length);
                if(depois.indexOf("css") !== -1){
                    pluginsConfig.ignores.ignoreCss = false;
                }
                if(depois.indexOf("js") !== -1){
                    pluginsConfig.ignores.ignoreJs = false;
                }
                if(depois.indexOf("img") !== -1){
                    pluginsConfig.ignores.ignoreImages = false;
                }
                if(depois.indexOf("font") !== -1){
                    pluginsConfig.ignores.ignoreFontFiles = false;
                }
                if(depois.indexOf("vide") !== -1){
                    pluginsConfig.ignores.ignoreVideoFiles = false;
                }
            }
            const startIndexAllTimes = element.indexOf("-all_times");
            if(startIndexAllTimes !== -1){
                pluginsConfig.all_times = true;
            }
            const startIndexUseGot = element.indexOf("-use_got");
            if(startIndexUseGot !== -1){
                pluginsConfig.useGot = true;
            }
        }
        config.set('plugin_configuration', pluginsConfig);
    }

};
