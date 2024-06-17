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
                        @if($links_encontrados != null)
                        </br></br>
                        <b>Informações por link:</b>
                            @php
                                $counter = 1;
                            @endphp
                            <dl>
                            @foreach($links_encontrados as $key => $links)
                            <b>{{$key}}</b></br>
                            <b>Tempo médio de resposta:</b> {{$links->media}}ms </br>
                                    <dt> Tempo de resposta em cada requisição:</b> </dt>
                                    @foreach($links->times as $key => $time)
                                        <dd><b>{{$key + 1}}:</b> {{$time->serverProcessingTime}}ms </dd>
                                    @endforeach
                            <!-- div class="card-header" id="heading{{$counter}}">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{$counter}}" aria-expanded="true" aria-controls="collapse{{$counter}}">
                                    {{$key}}
                                    </button>
                                </h5>
                                </div>
                                <div id="collapse{{$counter}}" class="collapse" aria-labelledby="heading{{$counter}}" data-parent="#accordion">
                                    <div class="card-body">
                                        <b>Tempo médio de resposta:</b> {{$links->media}}ms <br>
                                        <b> Tempo de resposta em cada requisição:</b> <br>
                                        @foreach($links->times as $key => $time)
                                            <b>{{$key + 1}}:</b> {{$time->serverProcessingTime}}ms <br>
                                        @endforeach
                                    </div>
                                </div>
                            </div -->
                            @php
                                $counter++;
                            @endphp
                            @endforeach
                            
                            </dl></br></br>
                        @endif
                        
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
