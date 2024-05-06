import fs from 'fs';
import scrape from 'website-scraper'; // only as ESM, no CommonJS
import AnalyzerAgentPlugin from './libs/AnalyzerAgentPlugin.js'; // only as ESM, no CommonJS

var url_scrape = '';
var result_filename = 'resultado.json';
var ignoreCss = true;
var ignoreJs = true;
var ignoreImages = true;
var ignoreFontFiles = true;
var ignoreVideoFiles = true;
var all_times = false;
const regex = /^(?:https?|ftp):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/;


if(process.argv.length < 3 || !regex.test(process.argv[2])){
  console.log("Utilize \"node index.js https://site.para.testar/ [argumentos]\"");
  console.log("-includeAsset=css (pode utilizar 'css,js,img')")
  console.log("-all_times (para mostrar o tempo de requisição completo)")
  process.exit(1); // Encerra o programa com código de erro
}

url_scrape = process.argv[2]

if(process.argv.length > 3){
  process.argv.forEach(element => {
    const startIndexIncludeCustomResultFile = element.indexOf("--result_filename=");
    if(startIndexIncludeCustomResultFile !== -1){
      var result = element.substring(startIndexIncludeCustomResultFile + "--result_filename=".length);
      result_filename = result + ".json";
    }
    const startIndexIncludeAsset = element.indexOf("-includeAsset=");
    if(startIndexIncludeAsset !== -1){
      const depois = element.slice(startIndexIncludeAsset + "-includeAsset=".length);
      if(depois.indexOf("css") !== -1){
        ignoreCss = false;
      }
      if(depois.indexOf("js") !== -1){
        ignoreJs = false;
      }
      if(depois.indexOf("img") !== -1){
        ignoreImages = false;
      }
      if(depois.indexOf("font") !== -1){
        ignoreFontFiles = false;
      }
      if(depois.indexOf("vide") !== -1){
        ignoreVideoFiles = false;
      }
    }
    const startIndexAllTimes = element.indexOf("-all_times");
    if(startIndexAllTimes !== -1){
      all_times = true;
    }
  });
}

var out_cli = {
  possibleCMS: false,
  possibleCMSType: '',
  serverRequestTimeMap: {},
  run: 1
};

const jsonString = JSON.stringify(out_cli);
const filePath = './result/' + result_filename;
fs.writeFileSync(filePath, jsonString);

const options = {
  urls: [url_scrape],
  urlFilter: (url) => url.startsWith(url_scrape), // Filter links to other websites
  recursive: true,
  filenameGenerator: 'bySiteStructure',
  directory: 'teste/',
  request: {
    headers: {
      'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36'
    }
  },
  contrxt: { out_cli },
  plugins: [ 
    new AnalyzerAgentPlugin({
      launchOptions: {  },
      ignoreCss: ignoreCss,
      ignoreJs: ignoreJs,
      ignoreImages: ignoreImages,
      ignoreFontFiles: ignoreFontFiles,
      ignoreVideoFiles: ignoreVideoFiles,
      result_filename: result_filename,
      all_times: all_times
    })
  ]
};

// with async/await
const result = await scrape(options);
const resulta = await scrape(options);
const resultb = await scrape(options);
/*
class MyPlugin {
	apply(registerAction) {
		registerAction('beforeStart', async ({options}) => {});
		registerAction('afterFinish', async () => {});
		registerAction('error', async ({error}) => {console.error(error)});
		registerAction('beforeRequest', async ({resource, requestOptions}) => ({requestOptions}));
		registerAction('afterResponse', async ({response}) => response.body);
		registerAction('onResourceSaved', ({resource}) => {});
		registerAction('onResourceError', ({resource, error}) => {});
		registerAction('saveResource', async ({resource}) => {});
		registerAction('generateFilename', async ({resource}) => {})
		registerAction('getReference', async ({resource, parentResource, originalReference}) => {})
	}
}
*/