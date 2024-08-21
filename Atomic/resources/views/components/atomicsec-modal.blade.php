@props(
    [
        'modal_id',
        'titulo',
        'confirm' => false,
        'texto',
        'texto_confirmacao' => 'Confirmar',
        'texto_cancelar' => 'Fechar/Cancelar'
    ]
)
<!-- Modal -->
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="{{ $modal_id }}Title">{{ $titulo }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      @if($texto)
      <div class="modal-body">
        {{ $texto }}
      </div>
      @endif
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ $texto_cancelar }}</button>
        @if($confirm)
        <button type="button" class="btn btn-primary" id="{{$modal_id}}_confirm">{{ $texto_confirmacao }}</button>
        @endif
      </div>
    </div>
  </div>
</div>