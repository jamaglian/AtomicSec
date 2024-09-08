@props(
    [
        'id_input' => rand(0, 9999),
        'disabled' => false,
        'col_classes',
        'label_text',
        'before_text',
        'after_text',
        'before_icon',
        'after_icon',
        'groupattr' => '',
        'messages'
    ]
)
@if(isset($col_classes))
<div class="{{ $col_classes }}">
@endif
    @if(isset($label_text))
    <label for="{{$id_input}}_input">{{$label_text}}</label>
    @endif
    <div class="input-group {{$groupattr}}">
        @if(isset($before_icon) || isset($before_text))
        <div class="input-group-prepend">
            <span class="input-group-text" id="{{$id_input}}_add">
                @if(isset($before_icon))
                <i class="{{ $before_icon }}"></i>
                @endif
                @if(isset($before_text))
                {{ $before_text }}
                @endif
            </span>
        </div>
        @endif
        <input {{ $disabled ? 'disabled' : '' }}  {!! $attributes->merge(['class' => 'form-control ' . ($messages ? 'is-invalid' : ''), 'id' => $id_input . '_input', 'aria-describedby' => $id_input . '_add' ]) !!} >
        @if(isset($after_icon) || isset($after_text))
        <div class="input-group-append">
            <span class="input-group-text" id="{{$id_input}}_add">
                @if(isset($after_icon))
                <i class="{{ $after_icon }}"></i>
                @endif
                @if(isset($after_text))
                {{ $after_text }}
                @endif
            </span>
        </div>
        @endif
        @if ($messages)
        <div class="invalid-feedback">
            @foreach ((array) $messages as $message)
                {{ $message }}</br>
            @endforeach
        </div>
        @endif
    </div>
@if(isset($col_classes))
</div>
@endif