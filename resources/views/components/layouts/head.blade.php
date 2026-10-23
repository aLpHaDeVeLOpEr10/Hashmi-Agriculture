@props(['title'])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#2f855a">
<title>{{ $title }} · {{ config('agri.name') }}</title>
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/icon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
@auth
    <meta name="offline-sync" content="{{ route('offline.sync') }}" data-token="{{ route('offline.token') }}" data-user="{{ auth()->id() }}"
        data-synced-message="{{ __(':count offline expenses synced.') }}" data-failed-message="{{ __('Could not sync offline expenses. Will retry when the connection is better.') }}">
@endauth
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
@if (app()->getLocale() === 'ur')
    <link href="https://fonts.bunny.net/css?family=noto-nastaliq-urdu:400,600,700&display=swap" rel="stylesheet">
@endif
<script>
    try {
        if (localStorage.getItem('sidebar-collapsed') === '1') document.documentElement.classList.add('sidebar-collapsed');
    } catch (e) {}
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
