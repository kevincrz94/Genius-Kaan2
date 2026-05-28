<link rel="manifest" href="{{ asset('manifest.json') }}">
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

    (function() {
        var deferredPrompt = null;
        var installButton = null;
        var iosHint = null;
        var dismissButton = null;

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true;
        }

        function isIos() {
            return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
        }

        function isSafari() {
            return /^((?!chrome|android|crios|fxios|edgios).)*safari/i.test(window.navigator.userAgent);
        }

        function showIosHintIfNeeded() {
            if (!iosHint || isStandalone() || !isIos() || !isSafari()) {
                return;
            }

            if (window.localStorage.getItem('geniusKaanIosInstallDismissed') === '1') {
                return;
            }

            iosHint.hidden = false;
        }

        window.addEventListener('beforeinstallprompt', function(event) {
            event.preventDefault();
            deferredPrompt = event;

            if (installButton && !isStandalone()) {
                installButton.hidden = false;
            }
        });

        window.addEventListener('appinstalled', function() {
            deferredPrompt = null;
            if (installButton) {
                installButton.hidden = true;
            }
            if (iosHint) {
                iosHint.hidden = true;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            installButton = document.getElementById('installPwaButton');
            iosHint = document.getElementById('iosInstallHint');
            dismissButton = document.getElementById('dismissIosInstallHint');

            if (installButton) {
                installButton.addEventListener('click', function() {
                    if (!deferredPrompt) {
                        return;
                    }

                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.finally(function() {
                        deferredPrompt = null;
                        installButton.hidden = true;
                    });
                });
            }

            if (dismissButton) {
                dismissButton.addEventListener('click', function() {
                    window.localStorage.setItem('geniusKaanIosInstallDismissed', '1');
                    if (iosHint) {
                        iosHint.hidden = true;
                    }
                });
            }

            showIosHintIfNeeded();
        });
    })();
</script>
