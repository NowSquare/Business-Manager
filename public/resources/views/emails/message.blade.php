@component('mail::layout')
    {{-- Header --}}
    @slot('header')

    @endslot

{{-- Body --}}
{{ $salutation }}

{{ $body_top }}

{{ $body_footer }}

{{ trans('g.message_signature', ['name' => $sender_name, 'email' => $sender_email, 'company' => $sender_company]) }}

{!! $sender_name !!}  
{!! $sender_email !!}  
{!! $sender_company !!} 

    {{-- Footer --}}
    @slot('footer')

    @endslot
@endcomponent