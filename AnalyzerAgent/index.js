import fs from 'fs';
import scrape from 'website-scraper'; // only as ESM, no CommonJS
import AnalyzerAgentPlugin from './libs/AnalyzerAgentPlugin.js'; // only as ESM, no CommonJS

var url_scrape = '';
var ignoreCss = true;
var ignoreJs = true;
var ignoreImages = true;
const regex = /^(?:https?|ftp):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/;


if(process.argv.length < 3 || !regex.test(process.argv[2])){
  console.log("Utilize \"node index.js https://site.para.testar/ [argumentos]\"");
  console.log("-includeAsset=css (pode utilizar 'css,js,img')")
  process.exit(1); // Encerra o programa com código de erro
}

url_scrape = process.argv[2]

if(process.argv.length > 3){
  process.argv.forEach(element => {
    const startIndex = element.indexOf("-includeAsset=");
    if(startIndex !== -1){
      const depois = element.slice(startIndex + "-includeAsset=".length);
      if(depois.indexOf("css") !== -1){
        ignoreCss = false;
      }
      if(depois.indexOf("js") !== -1){
        ignoreJs = false;
      }
      if(depois.indexOf("img") !== -1){
        ignoreImages = false;
      }
    }
  });
}

var out_cli = {
  possibleCMS: false,
  possibleCMSType: '',
  serverRequestTimeMap: {}
};

const jsonString = JSON.stringify(out_cli);
const filePath = 'resultado.json';
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
      ignoreImages: ignoreImages
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