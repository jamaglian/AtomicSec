import scrape from 'website-scraper'; // only as ESM, no CommonJS
import AnalyzerAgentPlugin from './libs/AnalyzerAgentPlugin.js'; // only as ESM, no CommonJS
const url_scrape = '';
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
console.log(process.argv)
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
  plugins: [ 
    new AnalyzerAgentPlugin({
      launchOptions: {  }
    })
  ]
};

// with async/await
const result = await scrape(options);
