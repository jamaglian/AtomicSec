import fs from 'fs';
import scrape from 'website-scraper'; 
import {config} from './Utils/config.js';
import {ResultHelper} from './Utils/result_helper.js'; 
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
await scrape(config.constructScrapeConfig());
/*
const resulta = await scrape(options);
const resultb = await scrape(options);
*/