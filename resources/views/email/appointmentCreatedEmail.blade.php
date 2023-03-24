@component('mail::message')
{{-- # Introduction --}}
Hi {{$info['first_name']}},<br>

Thank you for booking an appointment, please remember to complete payment to validate your appointment.<br>

<br>
Team,
<br>
{{ config('app.name') }}
@endcomponent
