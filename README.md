# Sistem Absensi DPUTR

Aplikasi absensi berbasis lokasi (GPS) untuk pegawai DPUTR: REST API **Laravel 9**
+ SPA **Vue 3**. Pegawai clock-in/out dengan foto & titik lokasi, admin mengelola
proyek/divisi/shift dan menarik rekap kehadiran.

> ⚠️ **Data sensitif tidak ikut repo.** Dump DB produksi (`*.sql`), `.env`, dan
> SQLite dev di-`.gitignore`. Jangan pernah commit berkas itu atau menyebarkannya —
> berisi identitas & lokasi pegawai.

---

## Arsitektur

```
┌────────────────────┐        HTTP + JWT        ┌──────────────────────┐
│  fe/  (Vue 3 SPA)  │ ───────────────────────▶ │  be/  (Laravel 9 API) │
│  Vite · Tailwind   │ ◀─────────────────────── │  JWT · MySQL/SQLite    │
│  Pinia · axios     │      JSON envelope       │  Maatwebsite/Excel     │
└────────────────────┘                          └──────────────────────┘
```

- **Backend** (`be/api-absensi-master`) — PHP 8.1 / Laravel 9, auth JWT
  (`tymon/jwt-auth`), ekspor Excel (`maatwebsite/excel`).
- **Frontend** (`fe/attendance-system-main`) — Vue 3 + Vite + Tailwind, state
  Pinia, HTTP via axios (`src/helpers/http-client.js`, interceptor JWT).

---

## Prasyarat

| Kebutuhan | Versi |
|-----------|-------|
| PHP       | ≥ 8.1 |
| Composer  | 2.x   |
| Node.js   | ≥ 18  |
| DB        | MySQL 8 (prod) / SQLite (dev & test) |

---

## Menjalankan — Backend

```bash
cd be/api-absensi-master
cp .env.example .env          # isi koneksi DB
composer install
php artisan key:generate
php artisan jwt:secret         # kunci JWT
php artisan migrate --seed     # skema + data awal (role, dll)
php artisan serve              # http://127.0.0.1:8000
```

## Menjalankan — Frontend

```bash
cd fe/attendance-system-main
cp .env.example .env          # arahkan VITE base URL ke API
npm install
npm run dev                   # http://127.0.0.1:5173
npm run build                 # produksi → dist/
```

---

## Autentikasi & Peran

Login mengembalikan **JWT**; dikirim balik sebagai `Authorization: Bearer <token>`.
Middleware `role:` (`app/Http/Middleware/EnsureRole.php`) menjaga endpoint admin.

| Peran        | Akses |
|--------------|-------|
| `admin` / `superadmin` | Penuh, lintas divisi. |
| `user_admin` (Pengawas) | Terbatas **hanya divisi yang ditugaskan** padanya. |
| `user`       | Pegawai biasa — absensi & profil sendiri. |

Scoping per-divisi untuk Pengawas dipusatkan di helper `User::canManageDivision()`
/ `canManageProject()` / `canManageShift()` dan scope baca `Attendance::visibleTo()`.

---

## Ringkasan Endpoint

Semua di bawah prefix `/api`. Definisi lengkap: `be/api-absensi-master/routes/api.php`.
🔒 = butuh login, 👑 = butuh peran `admin`/`user_admin`.

| Grup | Endpoint | Ket. |
|------|----------|------|
| Auth | `POST /auth/{login,register,logout,refresh,change-password}` | login publik; register selalu peran `user`. |
| Profil | 🔒 `GET /profile`, `GET /profile/me`, `POST /profile/edit` | ubah profil sendiri. |
| User | 🔒👑 `POST /user/all`, `POST /user`, `GET /user/summary` | kelola akun. |
| Divisi | 🔒 `POST /devision` (list) · 🔒👑 `store/update/destroy` | |
| Proyek | 🔒 `POST /project` (list) · 🔒👑 `store/update/destroy` | |
| Progres | 🔒 `POST /progres` · 🔒👑 `store/update/destroy` | |
| Shift | 🔒 `POST /shift` · 🔒👑 `store/update/destroy`, `add-user`, `delete-user` | |
| Absensi | 🔒 `GET /attendance`, `POST /attendance`, `GET /attendance/summary`, `GET /attendance/log` | clock-in/out & rekap. |
| Ekspor | 🔒👑 `GET /export`, `GET /export-data/{file}` | unduh Excel (nama file dibatasi anti path-traversal). |
| Penugasan | 🔒👑 `/user-project`, `/user-division` | assign pegawai ke proyek/divisi. |

Response dibungkus amplop seragam `{ data, meta: { status, message, code } }`
(`app/Http/Helpers/Json.php`). Kode auth/otorisasi (401/403) memakai HTTP status asli.

---

## Testing

```bash
cd be/api-absensi-master
php artisan test                       # seluruh feature test
php artisan test --filter Fase7Test    # test scoping per-divisi
```

Test diisolasi ke **SQLite in-memory** (`phpunit.xml`) — tidak menyentuh DB seed.

---

## Struktur Direktori

```
absensi-dputr/
├─ be/api-absensi-master/       # Laravel 9 API
│  ├─ app/Http/Controllers/     # 14 controller (Auth, Project, Shift, Attendaces, …)
│  ├─ app/Http/Middleware/       # EnsureRole, VerifyRecaptcha, LogUserActivity, …
│  ├─ app/Models/                # User, Attendance, Project, Devision, Shift, …
│  ├─ routes/api.php             # semua endpoint
│  └─ tests/Feature/             # feature + regression test
└─ fe/attendance-system-main/    # Vue 3 SPA
   └─ src/{views,components,store,router,helpers}/
```

---

## Keamanan

Sudah melewati audit keamanan (11 temuan, seluruhnya ditutup): role-gating,
anti path-traversal ekspor, `userId` diambil dari token (bukan body), register
tak bisa self-escalate, dan Pengawas dikunci ke divisinya. Riwayat lengkap ada di
`git log` pada branch `security/hardening-fase-0-4`.

Kontak isu keamanan: buka issue **privat** di repo ini — jangan diungkap publik.
