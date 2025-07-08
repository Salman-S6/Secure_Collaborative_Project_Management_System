<x-mail::message>
# New Comment

**{{ $comment->user->name }}** added a new comment:

"{{ $comment->content }}"

<x-mail::button :url="''">
View
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
