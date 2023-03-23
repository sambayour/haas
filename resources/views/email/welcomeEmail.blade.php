@component('mail::message')
{{-- # Introduction --}}
Hi {{$info['first_name']}},<br>

Welcome onboard.<br>

@if(!@empty($info['url']))
<a target="_blank" href="{{$info['url']}}" style=" text-align: center;">Click here to log in<a/>
@endif

<br>
Team,
<br>
{{ config('app.name') }}
@endcomponent
