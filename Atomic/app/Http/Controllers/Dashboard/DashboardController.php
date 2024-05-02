<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\User;
use App\Models\Companies;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados das empresas.
     */
    public function index(Request $request): View
    {
        // Obtém o usuário autenticado
        $user = Auth::user();
        $empresa = $user->companies->firstWhere('id', $request->session()->get('company'));
        if($user->isGlobalAdmin() || $empresa){
            return view('dashboard', [
                "company" => $empresa
            ]);
        }else{
            throw new \Exception(__("Ocorreu um erro."));
        }
    }

}