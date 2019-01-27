@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ $header }}
        @endcomponent
    @endslot

{{-- Body --}}
{{ $salutation }}

{{ $body_top }}

@component('mail::button', ['url' => $actionURL, 'color' => 'primary'])
{{ $actionText }}
@endcomponent

{{ $body_footer }}

{{ trans('g.email_signature', ['app_name' => config('system.name')]) }}

    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
            {{ $subcopy }}
        @endcomponent
    @endslot

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            {{ $footer }}
        @endcomponent
    @endslot
@endcomponent




