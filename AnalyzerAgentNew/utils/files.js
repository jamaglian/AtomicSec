const fs = require('fs');

function countLines(filePath) {
  // Ler o conteúdo do arquivo
  const fileContent = fs.readFileSync(filePath, 'utf-8');

  // Dividir o conteúdo em linhas e contar o número de linhas
  const lines = fileContent.split('\n');
  return lines.length;
}

// Função para ler as linhas de um arquivo e colocá-las em um array
function readLinesToArray(filePath) {
    // Ler o conteúdo do arquivo
    const fileContent = fs.readFileSync(filePath, 'utf-8');
  
    // Dividir o conteúdo em linhas e remover possíveis quebras de linha vazias no final
    const lines = fileContent.split('\n').filter(line => line.trim() !== '');
    return lines;
}
module.exports = { countLines, readLinesToArray };
