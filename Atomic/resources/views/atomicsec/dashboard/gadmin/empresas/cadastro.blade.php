<x-dashboard-layout>
    <h2 class="mb-4">Cadastrar Empresa @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header bg-white font-weight-bold">
            Formulário de Cadastro
        </div>
        <div class="card-body">
            <form id="cadastro_c_form" method="POST" action="{{ route('gadmin_companies.register') }}">
                @csrf
                <b>Informações sobre o responsavel</b>
                <div class="form-row">
                    <x-atomicsec-input
                        id_input="name"
                        type="text" 
                        name="name" 
                        :value="old('name')"  
                        :placeholder="__('Name')"
                        :label_text="__('Name')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('name')" 
                    />
                    <x-atomicsec-input
                        id_input="email"
                        type="text" 
                        name="email" 
                        :value="old('email')"  
                        :placeholder="__('Email')"
                        :label_text="__('Email')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('email')" 
                    />
                </div>
                <div class="form-row">
                    <x-atomicsec-input
                        id_input="password"
                        type="password" 
                        name="password" 
                        :value="old('password')"  
                        :placeholder="__('New Password')"
                        :label_text="__('New Password')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('password')" 
                    />
                    <x-atomicsec-input
                        id_input="password_confirmation"
                        type="password" 
                        name="password_confirmation" 
                        :value="old('password_confirmation')"  
                        :placeholder="__('Confirm Password')"
                        :label_text="__('Confirm Password')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('password_confirmation')" 
                    />
                </div>
                <b>Informações sobre a empresa</b>
                <div class="form-row">
                    <x-atomicsec-input
                        id_input="c_name"
                        type="text" 
                        name="c_name" 
                        :value="old('c_name')"  
                        placeholder="Segurança e Tecnologia LTDA"
                        label_text="Nome da empresa" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('c_name')" 
                    />
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary" type="submit" onclick=" document.getElementById('cadastro_c_form').submit() ">Cadastrar</button>
        </div>
    </div>
</x-dashboard-layout>