<?php

namespace App\Http\Controllers\Proxys;

use App\Models\Proxy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProxyController extends Controller
{
    // Função para listar os proxies
    public function index()
    {
        $proxys = Proxy::all();
        return view('atomicsec.dashboard.gadmin.proxys.index', compact('proxys'));
    }

    public function importForm()
    {
        return view('atomicsec.dashboard.gadmin.proxys.import');
    }

    // Função para importar proxies a partir de um arquivo .txt
    public function import(Request $request)
    {
        // Validação do arquivo e do tipo
        $validator = Validator::make($request->all(), [
            'proxy_file' => 'required|file|mimes:txt',
            'type' => 'required|string|in:http,https,socks4,socks5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $type = $request->input('type');
        $file = $request->file('proxy_file');

        // Ler o conteúdo do arquivo
        $content = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($content as $line) {
            // Remover espaços em branco extras
            $line = trim($line);
        
            // Verificar se a linha contém o formato esperado (IP:porta)
            if (strpos($line, ':') === false) {
                continue; // Pular linhas inválidas
            }
        
            // Separar IP e porta
            [$ip, $port] = explode(':', $line);
        
            // Remover espaços em branco de ip e port
            $ip = trim($ip);
            $port = trim($port);
        
            // Verificar se o IP é válido
            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                continue; // Pular IPs inválidos
            }
        
            Proxy::firstOrCreate(
                [
                    'ip' => $ip,
                    'port' => $port,
                    'type' => $type
                ]
            );
            
        }

        return redirect()->route('proxys.index')->with('success', 'Proxies importados com sucesso!');
    }
}
