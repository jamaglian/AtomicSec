import fs from 'fs';
import {config} from './Utils/config.js';
import {ResultHelper} from './Utils/result_helper.js'; 
import Scraper from './website-scraper/lib/scraper.js'; 
import {testAndParseArguments} from './Utils/process-arguments.js'; 

/**
 * Test and parse arguments
 */
await testAndParseArguments();

/**
 * Load config
 */
const url_scrape = config.get('url_scrape');
const result_filename = config.get('result_filename');

/**
 * Create result file
 */
const resultHelper = new ResultHelper();

/**
 * Scrape
 */
await new Scraper(config.constructScrapeConfig()).scrape()
//await scrape(config.constructScrapeConfig());
/*
const resulta = await scrape(options);
const resultb = await scrape(options);
*/