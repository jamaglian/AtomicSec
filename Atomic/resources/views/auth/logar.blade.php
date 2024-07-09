<x-autenticacao-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <x-input-auth icone="fa fa-user" type="text" name="email" :value="old('email')"  :placeholder="__('Email')" required autofocus autocomplete="username" />

        <x-input-auth icone="fa fa-key" :placeholder="__('Password')"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

        <div class="row">
            <div class="col pr-2">
                <button type="submit" class="btn btn-block btn-primary">Login</button>
            </div>
        </div>
    </form>
</x-autenticacao-layout>