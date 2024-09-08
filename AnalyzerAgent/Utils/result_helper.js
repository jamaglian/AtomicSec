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
    saveAfterRun(resultadoJson, resultadoPath){
        var topValores = []; // array para armazenar os top 3 valores e URLs correspondentes

        Object.keys(resultadoJson.serverRequestTimeMap).forEach(index => {
            var media = 0;
            resultadoJson.serverRequestTimeMap[index].times.forEach(unitMap => {
                media += unitMap.serverProcessingTime;
            });
            resultadoJson.serverRequestTimeMap[index].media = media / (resultadoJson.run - 1);

            // Adicionar ao array dos top valores se houver menos de 3 ou se a nova média for maior que a menor dos top valores
            if (topValores.length < 3 || resultadoJson.serverRequestTimeMap[index].media > topValores[2].valor) {
                topValores.push({ url: index, valor: resultadoJson.serverRequestTimeMap[index].media });
                // Ordenar os top valores pelo valor em ordem decrescente
                topValores.sort((a, b) => b.valor - a.valor);
                // Se a lista tiver mais de 3 valores, remover o último (menor) valor
                if (topValores.length > 3) {
                    topValores.pop();
                }
            }
        });

        console.log("\nTop 3 maiores valores:");
        topValores.forEach(item => {
            console.log(`URL: ${item.url}, Valor: ${item.valor}`);
        });
        console.log("\n\n");
        const novoJsonString = JSON.stringify(resultadoJson, null, 2); // O terceiro argumento é para formatar a saída
        fs.writeFileSync(resultadoPath, novoJsonString);
    }
}
