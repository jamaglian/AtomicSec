@props(['disabled' => false, 'icone', 'groupattr' => '', 'messages'])
<div class="input-group mb-3 {{ $groupattr }}">
    @if(isset($icone))
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="{{ $icone }}"></i></span>
        </div>
    @endif
    <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-control ' . ($messages ? 'is-invalid' : '')]) !!}>
    @if ($messages)
        <div class="invalid-feedback">
            @foreach ((array) $messages as $message)
                {{ $message }}</br>
            @endforeach
        </div>
    @endif
</div>