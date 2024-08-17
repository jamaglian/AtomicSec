// ResultHelper.js
import fs from 'fs'; // Certifique-se de que o fs é importado
import { config } from './config.js';

export class ResultHelper {
    constructor() {
        const out_cli = config.get('result_model');
        const jsonString = JSON.stringify(out_cli);
        const filePath = './result/' + config.get('result_filename');
        
        try {
            fs.writeFileSync(filePath, jsonString);
        } catch (err) {
            console.error(err);
            process.exit(1); // Encerra o programa com código de erro
        }
    }
}
