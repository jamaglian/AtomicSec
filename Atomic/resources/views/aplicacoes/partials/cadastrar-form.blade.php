<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Informações da aplicação para empresa ')}} <b> {{$company->name}} </b>
        </h2>
    </header>

    <form method="post" action="{{ route('aplicacoes.cadastrar') }}" class="mt-6 space-y-6">
        @csrf
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="url" :value="__('URL')" />
            <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" value="{{ old('url') }}" required autocomplete="url" />
            <x-input-error class="mt-2" :messages="$errors->get('url')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Cadastrar') }}</x-primary-button>

            @if (session('status') === 'aplicacao-cadastrada')
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
