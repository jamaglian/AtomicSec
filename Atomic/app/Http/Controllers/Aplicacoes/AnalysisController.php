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
use App\Models\ApplicationsAnalysis;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Redirect;

class AnalysisController extends Controller
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
        $applications = $this->empresa->applications;

        $allAnalyses = [];

        foreach ($applications as $application) {
            $analyses = $application->analysis;
            foreach ($analyses as $analysis) {
                $allAnalyses[] = $analysis;
            }
        }
        return view('atomicsec.dashboard.analises.index', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications,
            "analises"     => $allAnalyses
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
        return view('atomicsec.dashboard.analises.cadastrar', [
            "company"      => $this->empresa,
            "applications" => $this->empresa->applications,
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
            'aplicacao' => ['required', 'int'],
        ]);
        $applications = $this->empresa->applications;

        $aplication = $applications->firstWhere('id', $request->aplicacao);

        if(!$aplication) {
            throw new \Exception(__("A aplicação não foi encontrada."));
        }

        $analisar = ApplicationsAnalysis::create([
            'application_id' => $request->aplicacao,
            'analysis'       => json_encode(array()),
            'log'            => ''
        ]);
        ApplicationsAnalysisJob::dispatch($analisar);
        return redirect(route('analysis.index', absolute: false))->with('success', __('Analise cadastrada e adicionada a fila de execução com sucesso.'));
    }

    /**
     * Exibe a página de criação de aplicações para a empresa.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return View Uma resposta de visualização para o formulário de cadastro de empresas.
     */
    public function analise($id): View
    {
        $analysis = ApplicationsAnalysis::findOrFail($id);
        $aplication = Applications::findOrFail($analysis->application_id);
        if($this->empresa->id != $aplication->company_id) {
            return redirect(route('analysis.index', absolute: false))->with('error', __('Você não tem permissão para acessar essa análise.'));
        }
        $analysis_data = json_decode($analysis->analysis);
        return view('atomicsec.dashboard.analises.analise', [
            "analise"                => $analysis,
            "links_encontrados"      => ((isset($analysis_data->serverRequestTimeMap))?$analysis_data->serverRequestTimeMap:null),
        ]);
    }
    /**
     * Apaga uma análise.
     *
     * @param Request $request A requisição HTTP recebida.
     * @return RedirectResponse Uma resposta de redirecionamento, se aplicável.
     */
    public function delete($id): RedirectResponse
    {
        $analysis = ApplicationsAnalysis::findOrFail($id);
        $aplication = Applications::findOrFail($analysis->application_id);
        if($this->empresa->id != $aplication->company_id) {
            return redirect(route('analysis.index', absolute: false))->with('error', __('Você não tem permissão para deletar essa análise.'));
        }
        $analysis->delete();
        return redirect(route('analysis.index', absolute: false))->with('success', __('Analise deletada com sucesso.'));
    }
}