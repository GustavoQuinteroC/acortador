# Acortador de enlaces con QR y estadísticas

Acortador de enlaces propio con panel AdminLTE, códigos QR, estadísticas de visitas y API REST. Hecho con Laravel 13, MariaDB y Redis.

**En vivo:** https://go.gustavoquintero.dev/ (entra con la cuenta demo de solo lectura que aparece en la pantalla de acceso).

## Qué hace

- **Enlaces cortos** con slug propio (`/promo`) o generado (`/k7m2qa`, sin caracteres que se confunden como 0/o o 1/l).
- **QR** en SVG y PNG generado en el servidor, sin servicios de terceros.
- **Estadísticas** por día, país, dispositivo, navegador y sitio de origen, con visitantes únicos por día.
- **Vencimiento y pausa:** un enlace vencido o pausado muestra una página 410 en lugar de redirigir.
- **API REST** con tokens personales (`Authorization: Bearer gqa_…`) y límite de 60 solicitudes por minuto.
- **Cuenta demo** de solo lectura con enlaces y visitas de ejemplo.

## Decisiones de diseño

- **Redis guarda el destino de cada enlace.** Una redirección no consulta MariaDB para resolver el slug; solo escribe la visita. La caché se invalida sola cuando el enlace cambia o se borra.
- **No se guarda la IP.** Los visitantes únicos se cuentan con un HMAC de IP + navegador + fecha, recortado a 16 caracteres. Cambia cada día y no se puede revertir.
- **Redirección 302 con `no-store`:** el navegador no la guarda, así cada visita se cuenta.
- **Tokens solo como hash:** en la base queda el SHA-256; el token en claro se muestra una vez. El prefijo `gqa_` ayuda a que los escáneres de secretos lo detecten si alguien lo sube a un repo.
- **Sin JavaScript en línea:** la política CSP solo permite scripts propios y de jsDelivr.
- **Cada usuario ve solo sus enlaces,** en el panel y en la API.

## API

Base: `/api/v1`. Envía `Authorization: Bearer <token>` y `Accept: application/json`.

| Método y ruta | Qué hace |
|---|---|
| `GET /enlaces` | Lista paginada (`?por_pagina=25`, máximo 100) |
| `POST /enlaces` | Crea. Campos: `destino` (obligatorio), `slug`, `titulo`, `expira_en` |
| `GET /enlaces/{slug}` | Consulta uno |
| `PATCH /enlaces/{slug}` | Cambia campos, incluido `activo` |
| `DELETE /enlaces/{slug}` | Elimina el enlace y sus visitas |
| `GET /enlaces/{slug}/estadisticas` | Resumen de los últimos `?dias=30` (1 a 365) |
| `GET /enlaces/{slug}/qr` | QR en SVG |

```bash
curl -X POST https://go.gustavoquintero.dev/api/v1/enlaces \
  -H "Authorization: Bearer gqa_…" -H "Accept: application/json" \
  -d destino=https://ejemplo.mx/promocion -d slug=promo
```

Los errores de validación regresan 422 con mensajes en español.

## Instalación

Requiere PHP 8.3 o mayor con `pdo_mysql`, `redis`, `gd` y `mbstring`, MariaDB y Redis.

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env   # llena DB_* y REDIS_*
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force              # cuenta demo (opcional)
php artisan acortador:admin tu@correo.mx  # pide la contraseña sin mostrarla
```

La raíz pública es `public/`. Cada versión etiquetada (`v*`) publica en Releases un zip con `vendor/` incluido, listo para descomprimir en el servidor.

## Pruebas

GitHub Actions corre en cada push:

- PHPUnit en PHP 8.4 y 8.5 con SQLite.
- PHPUnit contra MariaDB 12 y Redis 7 reales.
- Larastan nivel 6.
- Playwright: portada, acceso con la cuenta demo, gráfica, QR y redirección, sin errores en la consola.

## Licencia

MIT. Hecho por [Gustavo Quintero](https://gustavoquintero.dev).
