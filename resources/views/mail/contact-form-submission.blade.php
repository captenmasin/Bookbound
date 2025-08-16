<x-mail::message>
You have received a new contact form submission from {{ $name }}.
## Details
- **Name:** {{ $name }}
- **Email:** {{ $email }}
- **User ID:** {{ $userId ?? 'None' }}


## Message
{{ $message }}
</x-mail::message>
