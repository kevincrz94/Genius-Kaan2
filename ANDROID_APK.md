# APK Android

La web ya incluye manifest PWA y service worker. La forma recomendada de generar una APK para esta fase es una Trusted Web Activity apuntando al dominio:

```text
https://genius-kaan.ceuniv.edu.mx
```

## Requisitos previos en produccion

1. El dominio debe servir por HTTPS.
2. La URL `/manifest.webmanifest` debe responder correctamente.
3. La URL `/service-worker.js` debe responder correctamente.
4. El panel admin debe tener usuarios creados en MySQL.
5. Cognifit debe estar configurado en `.env`.

## Generar APK con PWA Builder

1. Entra a:

```text
https://www.pwabuilder.com/
```

2. Usa esta URL:

```text
https://genius-kaan.ceuniv.edu.mx
```

3. Selecciona plataforma `Android`.
4. Descarga el paquete Android generado.
5. Firma el APK/AAB con la llave de publicacion que vayas a usar para Play Store o instalacion directa.

## Comprobaciones

Despues de desplegar, valida:

```bash
curl https://genius-kaan.ceuniv.edu.mx/manifest.webmanifest
curl https://genius-kaan.ceuniv.edu.mx/service-worker.js
curl https://genius-kaan.ceuniv.edu.mx/api/health
curl https://genius-kaan.ceuniv.edu.mx/api/cognifit/status
```

## API para una app nativa futura

La app nativa puede consumir:

```text
POST /api/auth/login
GET /api/auth/me
GET /api/users
GET /api/users/{user}
GET /api/reports/users/{user}
POST /api/cognifit/users/{user}/register
POST /api/cognifit/users/{user}/launch
GET /api/cognifit/users/{user}/scores
GET /api/cognifit/users/{user}/played-games
```

Para una app nativa real, el siguiente paso seria crear un proyecto Flutter o Android/Kotlin que use esas rutas con Sanctum.
