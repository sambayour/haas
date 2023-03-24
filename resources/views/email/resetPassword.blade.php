@component('mail::message')
{{-- # Introduction --}}
Hi {{$info['first_name']}},<br>

Use this OTP to complete your password reset.
{{-- @slot('subcopy') --}}
@component('mail::panel')
{{$info['token']}}
@endcomponent
{{-- @endslot --}}

<br>
<p>Cheers,</p>
<br>
{{ config('app.name') }}
@endcomponent
