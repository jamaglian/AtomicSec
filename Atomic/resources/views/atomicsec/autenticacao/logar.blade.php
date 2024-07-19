<x-autenticacao-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <x-atomicsec-input
            id_input="email"
            before_icon="fa fa-user"
            groupattr="mb-3 input-group-lg" 
            type="text" 
            name="email" 
            :value="old('email')"  
            :placeholder="__('Email')" 
            required 
            autofocus 
            autocomplete="username"
            :messages="$errors->get('email')" 
        />
        <x-atomicsec-input
            id_input="password"
            before_icon="fa fa-key" 
            :placeholder="__('Password')"
            groupattr="mb-3 input-group-lg"
            type="password"
            name="password"
            :messages="$errors->get('password')"
            required autocomplete="current-password" 
        />
        <div class="form-group input-group-lg form-check mb-0">
                    <input type="checkbox" class="form-check-input" id="remember_me"  name="remember">
                    <label class="form-check-label" for="remember_me">{{ __('Remember me') }}</label>
        </div>
        <div class="row">
            <div class="col pr-2">
                <button type="submit" class="btn btn-block btn-primary col-md-4 mx-auto d-block btn-lg">{{ __('Log in') }}</button>
            </div>
        </div>
    </form>
</x-autenticacao-layout>