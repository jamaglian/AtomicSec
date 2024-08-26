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
use App\Jobs\ApplicationsAnalysisJob;
use App\Jobs\AttackHttpKeepAliveJob;
use App\Models\ApplicationAttack;
use App\Models\ApplicationsAnalysis;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Redirect;

class AttacksController extends Controller
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
    * Apaga um ataque.
    *
    * @param Request $request A requisição HTTP recebida.
    * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
    */
    public function delete($id): RedirectResponse
    {
        $attack = ApplicationAttack::findOrFail($id);
        $aplication = Applications::findOrFail($attack->application_id);
        if($this->empresa->id != $aplication->company_id) {
            return redirect(route('dashboard', absolute: false))->with('error', __('Você não tem permissão para deletar esse ataque.'));
        }
        $attack_type = $attack->attacks_types_id;
        $attack->delete();
        return redirect(route((($attack_type == 1)? 'ataques.http-keep-alive' : 'dashboard'), absolute: false))->with('success', __('Ataque deletado com sucesso.'));
    }
    /**
     * Exibe os ataques HTTP Keep alive da empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados dos ataques HTTP Keep alive da empresa.
     */
    public function http_keep_alive_index(Request $request): View
    {
        $applications = $this->empresa->applications;

        $ataques = $this->empresa->applications->flatMap(function ($application) {
            return $application->attacks()->where('attacks_types_id', 1)->get();
        });

        return view('atomicsec.dashboard.attacks.http-keep-alive.index', [
            "company"      => $this->empresa,
            "ataques"      => $ataques, 
            "applications" => $this->empresa->applications
        ]);
    }
    /**
     * Exibe a página de criação de um novo ataque HTTP Keep alive.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro do ataque HTTP Keep alive.
     */
    public function http_keep_alive_cadastrof(Request $request): View
    {
        return view('atomicsec.dashboard.attacks.http-keep-alive.cadastrar', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications()->whereHas('analysis', function ($query) {
                $query->where('status', 'Finalizada.');
            })->get(),
        ]);
    }
    /**
     * Exibe a página de criação ataque HTTP Keep alive.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function http_keep_alive_cadastro(Request $request): RedirectResponse
    {
        $request->validate([
            'aplicacao' => ['required', 'int'],
            'atacantes' => ['required', 'int'],
            'use_proxy' => ['required', 'in:yes,no'],
            'tempo'     => ['required', 'string', 'max:5'],
        ]);
        $applications = $this->empresa->applications;

        $aplication = $applications->firstWhere('id', $request->aplicacao);

        if(!$aplication) {
            throw new \Exception(__("A aplicação não foi encontrada."));
        }
        if($aplication->waf != 'Não definido' || $request->use_proxy == 'yes') {
            return redirect(route('ataques.http-keep-alive', absolute: false))->with('fail', __('Ataque cadastrado e adicionado a fila de execução com sucesso.'));
            //throw new \Exception(__("Atualmente não temos proxy. Não é possível realizar o ataque."));
        }

        $atacar = ApplicationAttack::create([
            'application_id'    => $request->aplicacao,
            'attacks_types_id'  => 1,
            'attack_params'     => json_encode([
                'atacantes' => $request->atacantes,
                'tempo'     => $request->tempo,
                'use_proxy' => $request->use_proxy,
            ])
        ]);
        AttackHttpKeepAliveJob::dispatch($atacar);
        return redirect(route('ataques.http-keep-alive', absolute: false))->with('success', __('Ataque cadastrado e adicionado a fila de execução com sucesso.'));
    }   
        /**
     * Exibe a página de informações do ataque HTTP Keep alive.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function http_keep_alive_attack($id): View
    {
        $attack = ApplicationAttack::findOrFail($id);
        if($attack->attacks_types_id == 1) {
            $aplication = Applications::findOrFail($attack->application_id);
            if($this->empresa->id != $aplication->company_id) {
                return redirect(route('ataques.http-keep-alive', absolute: false))->with('fail', __('Você não tem permissão para acessar esse ataque.'));
            }
            return view('atomicsec.dashboard.attacks.http-keep-alive.ataque', [
                "attack"                => $attack
            ]);
        }else{
            return redirect(route('ataques.http-keep-alive', absolute: false))->with('fail', __('Ataque não correspondente.'));
        }
    }
}