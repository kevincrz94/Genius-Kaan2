<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="application-name" content="Genius Kaan">
<meta name="description" content="Entrenamiento y seguimiento cognitivo operativo para fuerzas de seguridad pública.">
<meta name="theme-color" content="#0f172a">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Genius Kaan">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="msapplication-TileColor" content="#0f172a">
<meta name="msapplication-TileImage" content="{{ asset('icons/icon-192.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/service-worker.js').catch(function() {});
        });
    }
</script>
