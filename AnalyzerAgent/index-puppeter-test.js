const puppeteer = require('puppeteer');
const { makeRequest } = require('./controller/requests');
const { countLines, readLinesToArray } = require('./utils/files');

(async () => {
    var check_cms = {};

    const regex_url = /^(?:https?|ftp):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/;
    if(process.argv.length < 3 || !regex_url.test(process.argv[2])){
        console.log("Utilize \"node index.js https://site.para.testar/ [Argumentos]\"");
        console.log("--check_cms=wordpress (pode utilizar 'wordpress,joomla,magento,opencart,phpbb')");
        process.exit(1); // Encerra o programa com código de erro
    }
    // Defina a URL para navegação
    const url = process.argv[2];
    if(process.argv.length > 3){
        process.argv.forEach(element => {
            const startIndexIncludeCmsCheck = element.indexOf("-check_cms=");
            if(startIndexIncludeCmsCheck !== -1){
                const depois = element.slice(startIndexIncludeCmsCheck + "-check_cms=".length);
                if(depois.indexOf("wordpress") !== -1 || depois.indexOf("wp") !== -1 ){
                    check_cms["wordpress"] = {
                        "wordlist": "./Web-Content/CMS/trickest-cms-wordlist/wordpress.txt",
                        "total_uri": countLines("./Web-Content/CMS/trickest-cms-wordlist/wordpress.txt"),
                        "total_uri_found": 0
                    };
                }
                if(depois.indexOf("joomla") !== -1){
                    check_cms["joomla"] = {
                        "wordlist": "./Web-Content/CMS/trickest-cms-wordlist/joomla.txt",
                        "total_uri": countLines("./Web-Content/CMS/trickest-cms-wordlist/joomla.txt"),
                        "total_uri_found": 0
                    };
                }
                if(depois.indexOf("magento") !== -1){
                    check_cms["magento"] = {
                        "wordlist": "./Web-Content/CMS/trickest-cms-wordlist/magento.txt",
                        "total_uri": countLines("./Web-Content/CMS/trickest-cms-wordlist/magento.txt"),
                        "total_uri_found": 0
                    };
                }
                if(depois.indexOf("opencart") !== -1){
                    check_cms["opencart"] = {
                        "wordlist": "./Web-Content/CMS/trickest-cms-wordlist/opencart.txt",
                        "total_uri": countLines("./Web-Content/CMS/trickest-cms-wordlist/opencart.txt"),
                        "total_uri_found": 0
                    };
                }
                if(depois.indexOf("phpbb") !== -1){
                    check_cms["phpbb"] = {
                        "wordlist": "./Web-Content/CMS/trickest-cms-wordlist/phpbb.txt",
                        "total_uri": countLines("./Web-Content/CMS/trickest-cms-wordlist/phpbb.txt"),
                        "total_uri_found": 0
                    };
                }
            }
        });
    }
    // Configuração do navegador
    const browser = await puppeteer.launch({
        headless: false, // Defina como false se quiser ver o navegador em ação
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    // Crie uma nova página
    const page = await browser.newPage();

    // Chame a função makeRequest do request_manager.js
    if(Object.keys(check_cms).length > 0){
        for (const [key, value] of Object.entries(check_cms)) {
            const linesArray = readLinesToArray(value.wordlist);
            for (const line of linesArray) {
                try {
                    const { statusCode } = await makeRequest(page, url + line);
                    if (statusCode === 200) {
                        console.log(`URI Found: ${line}`);
                        value.total_uri_found++;
                    }
                } catch (error) {
                console.log(`URI Not Found: ${line}`);
                }
            }
        }
        console.log(check_cms);
    }
    //const { statusCode } = await makeRequest(page, url);

    // Exiba os resultados
    //console.log(`Código de resposta: ${statusCode}`);
    // Feche o navegador
    await browser.close();
})();