<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGlobalAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if ($request->user() && $request->user()->isGlobalAdmin()) {
            // Se for um global admin, permite que a requisição prossiga
            return $next($request);
        }

        // Se não for um global admin, redireciona ou retorna uma resposta de erro
        return redirect('/')->with('error', 'Você não tem permissão para acessar esta página.');
    }
}
