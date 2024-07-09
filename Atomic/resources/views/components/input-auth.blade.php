@props(['disabled' => false, 'icone'])
<div class="input-group mb-3">
    @if(isset($icone))
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="{{ $icone }}"></i></span>
        </div>
    @endif
    <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-control']) !!}>
</div>