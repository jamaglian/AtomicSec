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
        <li {!! (( request()->routeIs('analysis.index') ? 'class="active"' : '' )) !!} >
            <a href="{{route('analysis.index')}}">
                <i class="fa fa-fw fa-chart-bar"></i>
                Análises
            </a>
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
                                || request()->routeIs('gadmin_proxys.index')
                            )? 
                                'class="list-unstyled collapse show"' :
                                'class="list-unstyled collapse"' 
                        ) 
                    !!}
                    class="list-unstyled collapse"
                >
                    <li><a href="{{route('gadmin_companies.list')}}">{{ __('Empresas') }}</a></li>
                    <li><a href="{{route('gadmin_proxys.index')}}">{{ __('Proxys') }}</a></li>
                </ul>
            </li>
        @endif
    </ul>
</div>