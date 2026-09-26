@props(['key'])

@php
    $value = config("marketing.company.{$key}");
@endphp

@if(filled($value)){{ $value }}@else<span class="placeholder-value">[à compléter]</span>@endif
