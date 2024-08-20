// config.js
import {HttpsProxyAgent} from 'hpagent';
import AnalyzerAgentPlugin from '../libs/AnalyzerAgentPlugin.js'; 
class Config {
    constructor() {
        this.settings = {
            "url_scrape": '',
            "result_filename": 'resultado.json',
            "plugin_configuration": {
                "ignores": {
                    "ignoreCss": true,
                    "ignoreJs": true,
                    "ignoreImages": true,
                    "ignoreFontFiles": true,
                    "ignoreVideoFiles": true
                },
                "all_times": false,
                "useGot": false
            },
            "result_model": {
                "possibleCMS": false,
                "possibleCMSType": '',
                "behindWAF": false,
                'behindWAFType': '',
                "serverRequestTimeMap": {},
                "run": 1
            },
        };
    }

    get(key) {
        return this.settings[key];
    }

    set(key, value) {
        this.settings[key] = value;
    }

    getAll() {
        return this.settings;
    }

    constructScrapeConfig(puppeteer = {}) {
        
        const url_scrape = this.settings['url_scrape'];
        const result_filename = this.settings['result_filename'];

        return {
            urls: [url_scrape],
            urlFilter: function(url) {
              const indexLink = url.indexOf(url_scrape.replace(/^https?:\/\//, '').replace(/\/$/, ''));
              return indexLink > -1 && indexLink < 9;
            },
            recursive: true,
            maxRecursiveDepth: 0,
            filenameGenerator: 'bySiteStructure',
            directory: 'teste/',
            request: {
              headers: {
                'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36'
              },
              //...puppeteer
              /* ,
              agent: {
                https: new HttpsProxyAgent({
                  keepAlive: true,
                  keepAliveMsecs: 1000,
                  maxSockets: 256,
                  maxFreeSockets: 256,
                  scheduling: 'lifo',
                  proxy: 'http://p.webshare.io:9999/'
                })
              }*/
            },
            plugins: [ 
              new AnalyzerAgentPlugin({
                launchOptions: {  },
                ...this.settings['plugin_configuration'],
                result_filename: result_filename
              })
            ]
        };
    }
}

// Exporta uma instância única (singleton)
//module.exports = new Config();
  // Exporta a classe como uma named export
export const config = new Config();