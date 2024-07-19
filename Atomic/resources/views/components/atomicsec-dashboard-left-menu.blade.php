<div class="sidebar sidebar-dark bg-dark">
    <ul class="list-unstyled">
        <li {!! (( request()->routeIs('dashboard')? 'class="active"' : '' )) !!} >
            <a href="index.html">
                <i class="fa fa-fw fa-home"></i>
                 Home
            </a>
        </li>
        @if(Auth::user()->isGlobalAdmin() )
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