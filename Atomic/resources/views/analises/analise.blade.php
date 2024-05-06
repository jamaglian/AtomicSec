<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analise') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div>
                        <b>Status:</b> {{$analise->status}}</br>
                        <b>Log:</b></br>
                        <textarea id="w3review" name="w3review" rows="30" cols="130" disabled>
                            {{$analise->log}}
                        </textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
