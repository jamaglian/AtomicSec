<?php

namespace App\Http\Controllers\Aplicacoes;

use App\Models\User;
use App\Models\Companies;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Applications;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Jobs\ApplicationsAnalysisJob;
use App\Jobs\AttackHttpSlowPostJob;
use App\Jobs\AttackHttpKeepAliveJob;
use App\Jobs\AttackXMLRPCFloodJob;
use App\Jobs\AttackPostFloodJob;
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
     * **************************************************************************************************************************
     * XML RPC Flood
     * **************************************************************************************************************************
     */

       /**
     * Exibe a página de informações do ataque XML RPC Flood.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function xml_rpc_flood_attack($id): View
    {
        $attack = ApplicationAttack::findOrFail($id);
        if($attack->attacks_types_id == 4) {
            $aplication = Applications::findOrFail($attack->application_id);
            if($this->empresa->id != $aplication->company_id) {
                return redirect(route('ataques.xml-rpc-flood', absolute: false))->with('fail', __('Você não tem permissão para acessar esse ataque.'));
            }
            return view('atomicsec.dashboard.attacks.xml-rpc-flood.ataque', [
                "attack"                => $attack
            ]);
        }else{
            return redirect(route('ataques.xml-rpc-flood', absolute: false))->with('fail', __('Ataque não correspondente.'));
        }
    }
    /**
     * Exibe os ataques XML RPC Flood da empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados dos ataques XML RPC Flood da empresa.
     */
    public function xml_rpc_flood_index(Request $request): View
    {
        $applications = $this->empresa->applications;

        $ataques = $this->empresa->applications->flatMap(function ($application) {
            return $application->attacks()->where('attacks_types_id', 4)->get();
        });

        return view('atomicsec.dashboard.attacks.xml-rpc-flood.index', [
            "company"      => $this->empresa,
            "ataques"      => $ataques, 
            "applications" => $this->empresa->applications
        ]);
    }
    /**
     * Exibe a página de criação de um novo ataque XML RPC Flood.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro do ataque XML RPC Flood.
     */
    public function xml_rpc_flood_cadastrof(Request $request): View
    {
        return view('atomicsec.dashboard.attacks.xml-rpc-flood.cadastrar', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications()->where('type', 'WordPress')->whereHas('analysis', function ($query) {
                $query->where('status', 'Finalizada.');
            })->get(),
        ]);
    }
    /**
     * Exibe a página de criação ataque XML RPC Flood.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function xml_rpc_flood_cadastro(Request $request): RedirectResponse
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
        $request->use_proxy = (($aplication->waf != 'Não definido')?'yes':$request->use_proxy);
        if($request->use_proxy == 'yes' && env('PROXY_TO_USE', '') == ''){//$aplication->waf != 'Não definido' || $request->use_proxy == 'yes') {
            return redirect(route('ataques.xml-rpc-flood', absolute: false))->with('fail', __('Atualmente não temos proxy. Não é possível realizar o ataque.'));
            //throw new \Exception(__("Atualmente não temos proxy. Não é possível realizar o ataque."));
        }

        $atacar = ApplicationAttack::create([
            'application_id'    => $request->aplicacao,
            'attacks_types_id'  => 4,
            'attack_params'     => json_encode([
                'atacantes' => $request->atacantes,
                'tempo'     => $request->tempo,
                'use_proxy' => $request->use_proxy,
            ])
        ]);
        AttackXMLRPCFloodJob::dispatch($atacar);
        return redirect(route('ataques.xml-rpc-flood', absolute: false))->with('success', __('Ataque cadastrado e adicionado a fila de execução com sucesso.'));
    }   

    /**
     * **************************************************************************************************************************
     * Post Flood
     * **************************************************************************************************************************
     */

    /**
     * Exibe os ataques Post Flood da empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados dos ataques POST Flood da empresa.
     */
    public function post_flood_index(Request $request): View
    {
        $applications = $this->empresa->applications;

        $ataques = $this->empresa->applications->flatMap(function ($application) {
            return $application->attacks()->where('attacks_types_id', 3)->get();
        });

        return view('atomicsec.dashboard.attacks.post-flood.index', [
            "company"      => $this->empresa,
            "ataques"      => $ataques, 
            "applications" => $this->empresa->applications
        ]);
    }
    /**
     * Exibe a página de criação de um novo ataque POST Flood.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro do ataque POST Flood.
     */
    public function post_flood_cadastrof(Request $request): View
    {
        return view('atomicsec.dashboard.attacks.post-flood.cadastrar', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications()->whereHas('analysis', function ($query) {
                $query->where('status', 'Finalizada.');
            })->get(),
        ]);
    }

    /**
     * Exibe a página de criação ataque POST Flood
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function post_flood_cadastro(Request $request): RedirectResponse
    {
        $request->validate([
            'aplicacao'     => ['required', 'int'],
            'atacantes'     => ['required', 'int'],
            'use_proxy'     => ['required', 'in:yes,no'],
            'tempo'         => ['required', 'string', 'max:5'],
            'params_post'   => ['required', 'string'],
            'action_url'    => ['required', 'string'],
            'tamanho_corpo' => ['required', 'int']
        ]);
        $applications = $this->empresa->applications;

        $aplication = $applications->firstWhere('id', $request->aplicacao);

        if(!$aplication) {
            throw new \Exception(__("A aplicação não foi encontrada."));
        }

        $request->use_proxy = (($aplication->waf != 'Não definido')?'yes':$request->use_proxy);
        if($request->use_proxy == 'yes' && env('PROXY_TO_USE', '') == ''){
            return redirect(route('ataques.post-flood', absolute: false))->with('fail', __('Atualmente não temos proxy. Não é possível realizar o ataque.'));
        }
        
        $action_url = $request->action_url;
        // Check if the URL is a relative path
        if (Str::startsWith($action_url, '/')) {
            // Prepend the base URL
            $action_url = rtrim($aplication->url, '/') . $action_url;
        }

        $atacar = ApplicationAttack::create([
            'application_id'    => $request->aplicacao,
            'attacks_types_id'  => 3,
            'attack_params'     => json_encode([
                'url'           => $action_url,
                'atacantes'     => $request->atacantes,
                'tempo'         => $request->tempo,
                'use_proxy'     => $request->use_proxy,
                'params_post'   => $request->params_post,
                'tamanho_corpo' => $request->tamanho_corpo
            ])
        ]);
        AttackPostFloodJob::dispatch($atacar);
        return redirect(route('ataques.post-flood', absolute: false))->with('success', __('Ataque cadastrado e adicionado a fila de execução com sucesso.'));
    }   
    /**
     * Exibe a página de informações do ataque HTTP Keep alive.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function post_flood_attack($id): View
    {
        $attack = ApplicationAttack::findOrFail($id);
        if($attack->attacks_types_id == 3) {
            $aplication = Applications::findOrFail($attack->application_id);
            if($this->empresa->id != $aplication->company_id) {
                return redirect(route('ataques.post-flood', absolute: false))->with('fail', __('Você não tem permissão para acessar esse ataque.'));
            }
            return view('atomicsec.dashboard.attacks.post-flood.ataque', [
                "attack"                => $attack
            ]);
        }else{
            return redirect(route('ataques.post-flood', absolute: false))->with('fail', __('Ataque não correspondente.'));
        }
    }
    /**
     * **************************************************************************************************************************
     * HTTP Slow POST
     * **************************************************************************************************************************
     */

    /**
     * Exibe os ataques Slow POST da empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização contendo os dados dos ataques HTTP Slow POST da empresa.
     */
    public function http_slow_post_index(Request $request): View
    {
        $applications = $this->empresa->applications;

        $ataques = $this->empresa->applications->flatMap(function ($application) {
            return $application->attacks()->where('attacks_types_id', 2)->get();
        });

        return view('atomicsec.dashboard.attacks.http-slow-post.index', [
            "company"      => $this->empresa,
            "ataques"      => $ataques, 
            "applications" => $this->empresa->applications
        ]);
    }
    /**
     * Exibe a página de criação de um novo ataque HTTP Slow POST.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro do ataque HTTP Slow POST.
     */
    public function http_slow_post_cadastrof(Request $request): View
    {
        return view('atomicsec.dashboard.attacks.http-slow-post.cadastrar', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications()->whereHas('analysis', function ($query) {
                $query->where('status', 'Finalizada.');
            })->get(),
        ]);
    }

    /**
     * Exibe a página de criação ataque HTTP Slow POST.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function http_slow_post_cadastro(Request $request): RedirectResponse
    {
        $request->validate([
            'aplicacao'     => ['required', 'int'],
            'atacantes'     => ['required', 'int'],
            'use_proxy'     => ['required', 'in:yes,no'],
            'tempo'         => ['required', 'string', 'max:5'],
            'params_post'   => ['required', 'string'],
            'action_url'    => ['required', 'string'],
            'tamanho_corpo' => ['required', 'int']
        ]);
        $applications = $this->empresa->applications;

        $aplication = $applications->firstWhere('id', $request->aplicacao);

        if(!$aplication) {
            throw new \Exception(__("A aplicação não foi encontrada."));
        }

        $request->use_proxy = (($aplication->waf != 'Não definido')?'yes':$request->use_proxy);
        if($request->use_proxy == 'yes' && env('PROXY_TO_USE', '') == ''){
            return redirect(route('ataques.http-slow-post', absolute: false))->with('fail', __('Atualmente não temos proxy. Não é possível realizar o ataque.'));
        }
        
        $action_url = $request->action_url;
        // Check if the URL is a relative path
        if (Str::startsWith($action_url, '/')) {
            // Prepend the base URL
            $action_url = rtrim($aplication->url, '/') . $action_url;
        }

        $atacar = ApplicationAttack::create([
            'application_id'    => $request->aplicacao,
            'attacks_types_id'  => 2,
            'attack_params'     => json_encode([
                'url'           => $action_url,
                'atacantes'     => $request->atacantes,
                'tempo'         => $request->tempo,
                'use_proxy'     => $request->use_proxy,
                'params_post'   => $request->params_post,
                'tamanho_corpo' => $request->tamanho_corpo
            ])
        ]);
        AttackHttpSlowPostJob::dispatch($atacar);
        return redirect(route('ataques.http-slow-post', absolute: false))->with('success', __('Ataque cadastrado e adicionado a fila de execução com sucesso.'));
    }   
           /**
     * Exibe a página de informações do ataque HTTP Keep alive.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function http_slow_post_attack($id): View
    {
        $attack = ApplicationAttack::findOrFail($id);
        if($attack->attacks_types_id == 2) {
            $aplication = Applications::findOrFail($attack->application_id);
            if($this->empresa->id != $aplication->company_id) {
                return redirect(route('ataques.http-slow-post', absolute: false))->with('fail', __('Você não tem permissão para acessar esse ataque.'));
            }
            return view('atomicsec.dashboard.attacks.http-slow-post.ataque', [
                "attack"                => $attack
            ]);
        }else{
            return redirect(route('ataques.http-slow-post', absolute: false))->with('fail', __('Ataque não correspondente.'));
        }
    }
    /**
     * **************************************************************************************************************************
     * HTTP Keep Alive
     * **************************************************************************************************************************
     */

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
        $request->use_proxy = (($aplication->waf != 'Não definido')?'yes':$request->use_proxy);
        if($request->use_proxy == 'yes' && env('PROXY_TO_USE', '') == ''){//$aplication->waf != 'Não definido' || $request->use_proxy == 'yes') {
            return redirect(route('ataques.http-keep-alive', absolute: false))->with('fail', __('Atualmente não temos proxy. Não é possível realizar o ataque.'));
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
    /**
     * **************************************************************************************************************************
     * Genéricos
     * **************************************************************************************************************************
     */

    public function get_return_to($attack_type){
        switch($attack_type){
            case 1:
                return 'ataques.http-keep-alive';
            case 2:
                return 'ataques.http-slow-post';
            case 3:
                return 'ataques.post-flood';
            case 4:
                return 'ataques.xml-rpc-flood';
            default:
                return 'dashboard';
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
        return redirect(route( $this->get_return_to($attack_type), absolute: false))->with('success', __('Ataque deletado com sucesso.'));
    }
    /**
     * Cancelar ataque
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function cancel_attack($id): RedirectResponse
    {
        $attack = ApplicationAttack::findOrFail($id);
        $aplication = Applications::findOrFail($attack->application_id);
        if($this->empresa->id != $aplication->company_id) {
            return redirect(route($this->get_return_to($attack->attacks_types_id), absolute: false))->with('fail', __('Você não tem permissão para acessar esse ataque.'));
        }
        if($attack->status != "Rodando..." || !isset($attack->pid) || $attack->pid < 1){
            return redirect(route($this->get_return_to($attack->attacks_types_id), absolute: false))->with('fail', __('Algo deu errado.'));
        }

        exec("kill -9 " . $attack->pid, $output, $return_var);

        if ($return_var !== 0) {
            return redirect(route($this->get_return_to($attack->attacks_types_id), absolute: false))->with('fail', __('Algo deu errado. O processo não foi finalizado. (' . $attack->pid . ')'));
        }
        return redirect(route($this->get_return_to($attack->attacks_types_id), absolute: false))->with('success', __('Ataque cancelado com sucesso, pode demorar um pouco para que o status seja alterado.'));
    }
}