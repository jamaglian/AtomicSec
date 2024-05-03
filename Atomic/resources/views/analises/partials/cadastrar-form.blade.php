<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Analisar Aplicação da empresa ')}} <b> {{$company->name}} </b>
        </h2>
    </header>

    <form method="post" action="{{ route('analysis.cadastro') }}" class="mt-6 space-y-6">
        @csrf


        <div class="flex items-center gap-4">
            <label for="aplicacao">Escolha uma aplicação:</label>
            
            <select name="aplicacao" id="aplicacao">
                @foreach($applications as $application)
                    <option value="{{$application->id}}">{{$application->name}}</option>
                @endforeach
            </select>
            
            <x-primary-button>{{ __('Analisar') }}</x-primary-button>

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
