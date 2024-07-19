<?php

namespace App\Http\Controllers\Aplicacoes;

use App\Models\User;
use App\Models\Companies;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Applications;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class AplicacoesController extends Controller
{
    protected $empresa;

    public function __construct(Request $request)
    {
        // Verificar se a empresa foi selecionada
        if($request->session()->has('company')) {
            $user = Auth::user();
            $this->empresa = $user->companies->firstWhere('id', $request->session()->get('company'));

            if(!$this->empresa) {
                throw new \Exception(__("A empresa não foi encontrada."));
            }
        } else {
            throw new \Exception(__("A empresa não foi selecionada."));
        }
    }
    /**
     * Exibe as aplicacoes da empresa do usuário.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados das empresas.
     */
    public function index(Request $request): View
    {
        return view('atomicsec.dashboard.aplicacoes.index', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications
        ]);
    }

    /**
     * Exibe a página de criação de aplicações para a empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function cadastrof(Request $request): View
    {
        return view('atomicsec.dashboard.aplicacoes.cadastro', [
            "company"     => $this->empresa
        ]);
    }

    /**
     * Exibe a página de criação de aplicações para a empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function cadastro(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url'  => ['required', 'string', 'lowercase', 'url', 'max:255', 'unique:' . Applications::class],
        ]);

        $aplicacao = Applications::create([
            'name' => $request->name,
            'url' => $request->url,
            'company_id' => $this->empresa->id
        ]);

        return redirect(route('aplicacoes.index', absolute: false))->with('success', __('Aplicação cadastrada com sucesso.'));
    }

}