<x-dashboard-layout>
    <h2 class="mb-4">Cadastrar Aplicação @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header bg-white font-weight-bold">
        {{ __('Informações da aplicação para empresa ')}} <b> {{$company->name}} </b>
        </div>
        <div class="card-body">
            <form id="cadastro_app_form" method="POST" action="{{ route('aplicacoes.cadastrar') }}">
                @csrf
                <div class="form-row">
                    <x-atomicsec-input
                        id_input="apelido"
                        type="text" 
                        name="name" 
                        :value="old('name')"  
                        :placeholder="__('Apelido')"
                        :label_text="__('Apelido')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('name')" 
                    />
                    <x-atomicsec-input
                        id_input="url"
                        type="text" 
                        name="url" 
                        :value="old('url')"  
                        :placeholder="__('Url')"
                        :label_text="__('Url')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('url')" 
                    />
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary" type="submit" onclick=" document.getElementById('cadastro_app_form').submit() ">Cadastrar</button>
        </div>
    </div>
</x-dashboard-layout>