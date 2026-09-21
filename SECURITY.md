# Security Audit & Hardening

Security review of the DPUTR attendance system (Laravel 9 API + Vue 3 SPA),
conducted against a **local copy only** — the production server was never touched.

## Findings

Severity + remediation status. Each row links to its tracking issue.

| # | Severity | Finding | Status | Issue |
|---|----------|---------|--------|-------|
| 1 | Critical | `/api/migrate` unauthenticated → runs `artisan migrate` | ✅ Fixed | #22 |
| 2 | Critical | No role-gating → vertical privilege escalation on every admin/mutation endpoint | ✅ Fixed | #23 |
| 3 | Critical | `/api/export` + `/api/export-data/{file}` unauthenticated → attendance PII (incl. GPS) leak | ✅ Fixed | #24 |
| 4 | High | Path traversal in `/export-data/{file_path}` | ✅ Fixed | #25 |
| 5 | High | `APP_DEBUG=true` in prod → stack traces & server paths leaked | ✅ Fixed (config) | #26 |
| 6 | High | `/api/attendances` unauthenticated + trusts client `userId` → forge attendance | ⬜ Open | #27 |
| 7 | High | Public register lets caller choose `roleId` → self-escalate to admin | ⬜ Open | #28 |
| 8 | Medium | reCAPTCHA not enforced server-side + login 500 on missing token | ⬜ Open | #29 |
| 9 | Medium | Login user enumeration | ⬜ Open | #30 |
| 10 | Medium | Error envelopes return HTTP 200; unauth `auth:api` → 500 not 401 | ⬜ Open | #31 |

## Fixes applied in this branch

**Fase 0 — kill the two worst holes**
- Removed the `/api/migrate` route and its `Artisan` import.
- `.env.example`: `APP_DEBUG=false` with a warning comment (confirm prod `.env` matches).

**Fase 1 — lock down export + traversal**
- `/export` and `/export-data/{file}` gated with `auth:api` + `role:admin,user_admin`.
- `FileController@downloadExcel`: `basename($file_path)` + route regex `[A-Za-z0-9_\-.]+`.

**Fase 2 — authorization layer (test-first)**
- New `App\Http\Middleware\EnsureRole` (`role:` route-middleware) checking the
  user's role names via the `role_has_users` → `roles` relation.
- Applied to admin/mutation endpoints; self-service (own profile) left open.
- Tests: `RoleGateTest`, plus `Smoke*Test` probes flipped from vacuous to real guards.

**Fase 3 — correctness (auth-adjacent)**
- `ProfileController@update`: honours `{id}` only for admin/user_admin, otherwise
  forces the caller's own profile (closes a cross-user write path + silent no-op);
  `name` added to the writable whitelist.
- Late/on-time calc now compares clock-time to shift time (was datetime vs TIME → always `late`).
- Project `longitude`/`longtitude` key mismatch fixed (coordinate was never saved).

**Fase 4 — frontend**
- NIK masked on Home; project cost formatted; pagination footer shows real total.
- `downloadFile` rewritten to pull the export as an authenticated blob (via the
  JWT-bearing http-client) — the raw `<a href>` broke once `/export-data` was gated.

## Verification

- Backend suite green: `php artisan test` (all feature tests, incl. `RoleGateTest`,
  `Fase3Test`, flipped smoke probes).
- Live curl against the local API: role gate (staff 403 / admin 200), late-calc,
  longitude persistence, profile access-control.
- Frontend verified with Playwright screenshots (NIK mask, cost, pagination, authed export).

## Deploy checklist

- [ ] Prod `.env`: `APP_DEBUG=false`.
- [ ] Rotate any credentials that may have been exposed while `APP_DEBUG` was on.
- [ ] Work the still-open findings #27–#31 before the next release.
- [ ] The production DB dump containing employee PII must never be committed or shared.

## Reporting

Found something? Open a private issue on this (private) repository — do not disclose publicly.
