<div class="sidebar sidebar-dark bg-dark">
    <ul class="list-unstyled">
        <li {!! (( request()->routeIs('dashboard')? 'class="active"' : '' )) !!} >
            <a href="{{route('dashboard')}}">
                <i class="fa fa-fw fa-home"></i>
                 Home
            </a>
        </li>
        @if( !Auth::user()->isGlobalAdmin() )
            <li {!! (( request()->routeIs('aplicacoes.index')? 'class="active"' : '' )) !!} >
                <a href="{{route('aplicacoes.index')}}">
                    <i class="fa fa-fw fa-laptop-code"></i>
                    Aplicações Cadastradas
                </a>
            </li>
            <li {!! (
                ( request()->routeIs('analysis.index') ?
                    'class="active"'
                        : 
                    '' 
                )
                ) !!} >
                <a href="#sm_analises" data-toggle="collapse">
                    <i class="fa fa-fw fa-chart-bar"></i> Análises
                </a>
                <ul 
                    id="sm_analises" 
                    {!! 
                        (
                            ( 
                                request()->routeIs('analysis.index')
                            )? 
                                'class="list-unstyled collapse show"' :
                                'class="list-unstyled collapse"' 
                        ) 
                    !!}
                    class="list-unstyled collapse"
                >
                    <li><a href="{{route('analysis.index')}}">{{ __('Análises de Scraping') }}</a></li>
                </ul>
            </li>
            <li {!! (
                (   (
                        request()->routeIs('ataques.http-keep-alive')
                        || request()->routeIs('ataques.http-slow-post')
                    ) ?
                    'class="active"'
                        : 
                    '' 
                )
                ) !!} >
                <a href="#sm_attacks" data-toggle="collapse">
                    <i class="fa fa-fw fa-skull-crossbones"></i> Ataques
                </a>
                <ul 
                    id="sm_attacks" 
                    {!! 
                        (
                            ( 
                                request()->routeIs('ataques.http-keep-alive')
                                || request()->routeIs('ataques.http-slow-post')
                            )? 
                                'class="list-unstyled collapse show"' :
                                'class="list-unstyled collapse"' 
                        ) 
                    !!}
                    class="list-unstyled collapse"
                >
                    <li><a href="{{route('ataques.http-keep-alive')}}">{{ __('HTTP Keep-Alive') }}</a></li>
                    <li><a href="{{route('ataques.http-slow-post')}}">{{ __('HTTP Slow-Post') }}</a></li>
                </ul>
            </li>
        @else
            <li>
                <a href="#sm_admin" data-toggle="collapse">
                    <i class="fa fa-fw fa-tools"></i> Admin
                </a>
                <ul 
                    id="sm_admin" 
                    {!! 
                        (
                            ( 
                                request()->routeIs('gadmin_companies.list')
                            )? 
                                'class="list-unstyled collapse show"' :
                                'class="list-unstyled collapse"' 
                        ) 
                    !!}
                    class="list-unstyled collapse"
                >
                    <li><a href="{{route('gadmin_companies.list')}}">{{ __('Empresas') }}</a></li>
                </ul>
            </li>
        @endif
    </ul>
</div>