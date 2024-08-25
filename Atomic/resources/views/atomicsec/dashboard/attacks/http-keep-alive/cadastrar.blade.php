<x-dashboard-layout>
    <h2 class="mb-4">Cadastrar Ataque HTTP Keep-Alive @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="alert alert-danger" role="alert"> 
        <div class="d-flex justify-content-center">
            <h4 class="alert-heading">Área de risco!</h4>
        </div>
        <div class="d-flex justify-content-center">
            <b>O ataque pode causar indisponibilidade do serviço, lentidão e até mesmo a quebra do serviço. Só utilize esta ferramenta se tiver certeza do que está fazendo.</b>
        </div>
    </div>
    <div class="card mb-4">
        @if(count($applications) > 0)
            <div class="card-header bg-white font-weight-bold">
            {{ __('Ataque HTTP Keep-Alive para Aplicação da empresa ')}} <b> {{$company->name}} </b>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('ataques.http-keep-alive.cadastro') }}">
                    @csrf
                    <div class="form-group">
                        <label for="aplicacao">Escolha uma aplicação: </label>
                        <select class="form-control" id="aplicacao" name="aplicacao">
                            @foreach($applications as $application)
                                <option value="{{$application->id}}">{{$application->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="atacantes">Número de atacantes: </label>
                        <select class="form-control" id="atacantes" name="atacantes">
                            <option value="1">1</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="75">75</option>
                            <option value="100">100</option>
                            <option value="125">125</option>
                            <option value="150">150</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="use_proxy">Usar Proxy (Para aplicações com WAF essa configuração não tem efeito.): </label>
                        <select class="form-control" id="use_proxy" name="use_proxy">
                            <option value="yes">Sim</option>
                            <option value="no" selected>Não</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white">
                <button type="submit" class="btn btn-danger">{{ __('Atacar') }}</button>
            </div>
        @else
            <div class="card-header bg-white font-weight-bold">
                {{ __('Não há aplicações/analises cadastradas para a empresa ')}} <b> {{$company->name}} </b>
            </div>
            <div class="card-body">
                <p>
                    Não existem aplicações cadastradas ou nenhuma aplicação foi analisada. Para realizar um ataque é necessário que exista ao menos uma aplicação cadastrada e analisada.
                </p>
                <a href="{{ route('analysis.cadastrof', absolute: false) }}" class="btn btn-primary">{{ __('Cadastrar Analise') }}</a>
                <a href="{{ route('aplicacoes.cadastrarf', absolute: false) }}" class="btn btn-primary">{{ __('Cadastrar Aplicação') }}</a>
            </div>
        @endif
    </div>
</x-dashboard-layout>