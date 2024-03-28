<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Informações da empresa') }}
        </h2>
    </header>

    <form method="post" action="{{ route('gadmin_companies.register') }}" class="mt-6 space-y-6">
        @csrf

        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Usuário dono da empresa</span>
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <br/>
        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Empresa</span>

        <div>
            <x-input-label for="c_name" :value="__('Name')" />
            <x-text-input id="c_name" name="c_name" type="text" class="mt-1 block w-full" value="{{ old('c_name') }}" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('c_name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Criar') }}</x-primary-button>

            @if (session('status') === 'empresa-criada')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
