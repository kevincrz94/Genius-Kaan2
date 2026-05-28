# Empaquetado de Genius Kaan

La aplicación queda preparada como PWA para generar paquetes con PWABuilder en Android, Windows e iOS.

## Requisitos en producción

1. Dominio HTTPS activo.
2. `/manifest.webmanifest` debe responder con `Content-Type` compatible con JSON o webmanifest.
3. `/service-worker.js` debe responder desde la raíz del dominio.
4. `/icons/icon-192.png` y `/icons/icon-512.png` deben cargar correctamente.
5. El login y los simuladores deben funcionar en `https://genius-kaan.ceuniv.edu.mx`.

## Android

1. Entrar a `https://www.pwabuilder.com/`.
2. Capturar `https://genius-kaan.ceuniv.edu.mx`.
3. Elegir `Android`.
4. Descargar APK/AAB.
5. Firmar con la llave de publicación.
6. Si se publicará en Play Store, configurar el archivo `assetlinks.json` que PWABuilder indique.

## Windows

1. Entrar a `https://www.pwabuilder.com/`.
2. Capturar `https://genius-kaan.ceuniv.edu.mx`.
3. Elegir `Windows`.
4. Descargar el paquete MSIX.
5. Firmar el paquete con certificado válido para instalación local o Microsoft Store.

## iOS

1. Entrar a `https://www.pwabuilder.com/`.
2. Capturar `https://genius-kaan.ceuniv.edu.mx`.
3. Elegir `iOS`.
4. Descargar el proyecto generado.
5. Abrirlo en Xcode en macOS.
6. Configurar Bundle ID, Apple Developer Team, iconos y firma.
7. Compilar para TestFlight o App Store.

## Validación rápida

```bash
curl -I https://genius-kaan.ceuniv.edu.mx/manifest.webmanifest
curl -I https://genius-kaan.ceuniv.edu.mx/service-worker.js
curl -I https://genius-kaan.ceuniv.edu.mx/icons/icon-512.png
curl -I https://genius-kaan.ceuniv.edu.mx/offline.html
```

## Notas

- La app necesita conexión para Cognifit; el modo offline solo muestra una pantalla institucional de reconexión.
- Para iOS se requiere macOS, Xcode y cuenta de Apple Developer.
- Para Windows se recomienda usar el paquete MSIX generado por PWABuilder.
