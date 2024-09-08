<x-dashboard-layout>
    <h2 class="mb-4">Dashboard @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    @if(Auth::user()->isGlobalAdmin())
        <div class="row mb-4">
            <div class="col-md">
                <div class="d-flex border">
                    <div class="bg-primary text-light p-4">
                        <div class="d-flex align-items-center h-100">
                            <i class="fa fa-3x fa-fw fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 bg-white p-4">
                        <p class="text-uppercase text-secondary mb-0">Analises</p>
                        <h3 class="font-weight-bold mb-0">10</h3>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="d-flex border">
                    <div class="bg-danger text-light p-4">
                        <div class="d-flex align-items-center h-100">
                            <i class="fa fa-3x fa-fw fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 bg-white p-4">
                        <p class="text-uppercase text-secondary mb-0">Problemas Encontrados</p>
                        <h3 class="font-weight-bold mb-0">10</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-dashboard-layout>