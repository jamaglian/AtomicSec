const makeRequest = async (page, url, searchText = null) => {
  // Navegar até a URL
  const response = await page.goto(url, { waitUntil: 'networkidle2' });

  // Obter o código de status da resposta
  const statusCode = response.status();

  if (searchText === null) {
    // Se searchText for null, não obter o conteúdo da resposta
    return { statusCode };
  }

  // Se você precisa verificar um texto específico
  const responseText = await page.content(); // Obtém o conteúdo da página
  if (responseText.includes(searchText)) {
    console.log('Texto encontrado na resposta.');
  } else {
    console.log('Texto não encontrado na resposta.');
  }

  return { statusCode, responseText };
};

module.exports = { makeRequest };
