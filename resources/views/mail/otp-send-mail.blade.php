<x-mail::message>
# Introduction

The body of your message.
this is your 8 digits code {{ $otp }}

<x-mail::button :url="''">
Button Text
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
