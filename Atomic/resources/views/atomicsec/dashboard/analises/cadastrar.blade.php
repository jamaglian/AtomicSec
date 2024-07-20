<x-dashboard-layout>
    <h2 class="mb-4">Cadastrar Analise @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header bg-white font-weight-bold">
        {{ __('Analisar Aplicação da empresa ')}} <b> {{$company->name}} </b>
        </div>
        <div class="card-body">
            <form class="form-inline" method="POST" action="{{ route('analysis.cadastro') }}">
                @csrf
                <div class="form-group">
                    <label for="aplicacao" class="mr-sm-2">Escolha uma aplicação: </label>
                    <select class="form-control mr-sm-2" id="aplicacao" name="aplicacao">
                        @foreach($applications as $application)
                            <option value="{{$application->id}}">{{$application->name}}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">{{ __('Analisar') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>