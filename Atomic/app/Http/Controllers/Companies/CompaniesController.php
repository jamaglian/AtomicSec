<?php

namespace App\Http\Controllers\Companies;

use App\Models\User;
use App\Models\Companies;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CompaniesController extends Controller
{
    /**
     * Exibe a página de visualização de empresas para o administrador global.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados das empresas.
     */
    public function view_gadmin(Request $request): View
    {
        $companies = Companies::with('owner')->get();
        return view('gadmin/empresas', [ 
            'registros' => $companies
        ]);
    }

    /**
     * Exibe a página de criação de empresas para o administrador global.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function cadastrof_gadmin(Request $request): View
    {
        return view('gadmin/empresas/cadastrar');
    }

    /**
     * Exibe a página de criação de empresas para o administrador global.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function cadastro_gadmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'c_name' => ['required', 'string', 'max:255']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $userId = User::where('email', $user->email)->value('id');

        $company = Companies::create([
            'company_owner_id' => $userId,
            'name' => $request->c_name
        ]);

        return redirect(route('gadmin_companies.list', absolute: false))->with('success', 'Dados salvos com sucesso!');
    }

}