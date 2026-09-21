# absensi-dputr

Attendance System (Laravel 9 REST API + Vue 3/Vite SPA) — local review copy.

> ⚠️ **Data sensitif tidak disertakan.** Dump DB produksi (`*.sql`), file `.env`,
> dan SQLite dev sengaja di-`.gitignore`. Jangan pernah commit berkas itu.

## Struktur
- `be/api-absensi-master` — REST API (PHP 8.1 / Laravel 9, JWT). `cp .env.example .env` → `composer install` → `php artisan key:generate` → `php artisan migrate`.
- `fe/attendance-system-main` — SPA (Vue 3 + Vite + Tailwind). `cp .env.example .env` → `npm install` → `npm run dev`.

## Catatan review
Probe test ada di `be/api-absensi-master/tests/Feature/{Smoke,Smoke2,Smoke3,Matrix}Test.php`
(mendokumentasikan perilaku saat ini, bukan perilaku yang diinginkan).
