@component('mail::message')
{{-- # Introduction --}}
Hi {{$info['first_name']}},<br>

Thank you for validating your appointment by complete your payment.<br>

<br>
Team,
<br>
{{ config('app.name') }}
@endcomponent
