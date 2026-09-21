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
| 6 | High | `/api/attendances` unauthenticated + trusts client `userId` → forge attendance | ✅ Fixed | #27 |
| 7 | High | Public register lets caller choose `roleId` → self-escalate to admin | ✅ Fixed | #28 |
| 8 | Medium | reCAPTCHA not enforced server-side + login 500 on missing token | ✅ Fixed (500 + toggle) | #29 |
| 9 | Medium | Login user enumeration | ✅ Fixed | #30 |
| 10 | Medium | Error envelopes return HTTP 200; unauth `auth:api` → 500 not 401 | 🟡 Partial | #31 |
| 11 | Medium | Horizontal escalation: `user_admin` can write across any division | 🟢 Fixed (project + devision + div-assign) | #32 |

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

**Fase 5 — client-controlled identity (both HIGH)**
- Legacy `POST /api/attendances` (`Attendances()`) now requires `auth:api` and
  derives `userId` from the token instead of the request body — closes anonymous
  attendance forgery. It duplicates `store()` and is a candidate for removal. [#27]
- Public `register` no longer honours a client `roleId`; it always assigns the
  plain `user` role (resolved by name, not a magic id). Admin-driven creation via
  `/api/user` is unchanged. [#28]
- Tests: `Fase5Test`; smoke probes F-01/F-05 flipped from documenting the holes to
  asserting they are closed.

**Fase 6 — login flow (both MEDIUM)**
- `createAssessment()` param made nullable + early-returns on empty token — a
  missing `g-recaptcha-response` no longer TypeErrors into a 500. reCAPTCHA stays
  decorative by default; a new `RECAPTCHA_ENFORCE` env flag (default `false`)
  rejects tokenless logins when switched on. Full score-based gating still needs
  live GCP creds + a verified FE token flow — deferred. [#29]
- Login now returns one uniform error ("Email atau password salah") for both an
  unknown email and a wrong password, and drops the duplicate `auth()->attempt`.
  The old "Email not registered" vs "Invalid password" split let an attacker
  enumerate registered emails. [#30]
- Tests: `Fase6Test`; smoke probes F-04/F-10 flipped to assert the fixes.

**#31 — partial.** The "unauthenticated → 500" half is already correct: the
framework renders `AuthenticationException` as JSON **401** for API requests
(proven by `RoleGateTest::missing token returns 401`). The remaining half — error
payloads returned with HTTP **200** inside a `meta.status` envelope — is an
intentional API contract the Vue FE depends on (it branches on `meta.status`, not
the HTTP code). Flipping it to real status codes is a coordinated FE+BE change and
is deliberately **not** done here to avoid breaking the client.

**Fase 8 — horizontal ownership scoping (#32)**
- A `user_admin` (Pengawas) passed the role gate but could then write to **any**
  division's projects by guessing an id — the controller only did `findOrFail`,
  never checking the resource's division against the actor's assignments.
- New reusable `User::canManageDivision($divisionId)`: full admin (`admin`/`superadmin`)
  is unrestricted; `user_admin` is limited to divisions assigned via
  `user_have_division`. Gated `ProjectController::store/update/destroy` — a
  cross-division write now returns a real **403** (same envelope as `EnsureRole`).
- Tests: `Fase7Test` (own-division write ok, foreign update/destroy/store 403 +
  unchanged, full admin cross-division ok).

**Fase 8b — extend #32 to division & division-assignment**
- `forbiddenDivision()` lifted to the base `Controller` (shared by all controllers,
  no per-controller copy).
- `DevisionController::update/destroy` now gate on `canManageDivision($id)` — a
  Pengawas can no longer rename/delete a division that isn't theirs.
- `UserHaveDivisionController::insertUserAssign` gates on the target `division_id`
  — no assigning users into a foreign division.
- Tests added to `Fase7Test` (own-division update ok / foreign update+destroy 403,
  assign into own division ok / foreign 403).
- **Still deferred (same class, #32):** `shift`, `progres`, `user-project`
  (project-keyed → needs project→division hop), and attendance *reads*
  (list-scoping). Done incrementally to keep each change verifiable.

## Verification

- Backend suite green: `php artisan test` (all feature tests, incl. `RoleGateTest`,
  `Fase3Test`, flipped smoke probes).
- Live curl against the local API: role gate (staff 403 / admin 200), late-calc,
  longitude persistence, profile access-control.
- Frontend verified with Playwright screenshots (NIK mask, cost, pagination, authed export).

## Deploy checklist

- [ ] Prod `.env`: `APP_DEBUG=false`.
- [ ] Rotate any credentials that may have been exposed while `APP_DEBUG` was on.
- [ ] Optionally set `RECAPTCHA_ENFORCE=true` once GCP creds + FE token flow are verified.
- [ ] Decide on #31 (error-envelope HTTP status) as a coordinated FE+BE change.
- [x] Tests isolated to an in-memory sqlite DB (`phpunit.xml`) — no longer wipe live seed data.
- [x] Horizontal ownership: project write ops scoped to the actor's division (#32).
- [x] #32 scoping on devision update/destroy + user-division assign.
- [ ] Extend #32 to shift/progres/user-project (project-keyed) + attendance read-scoping.
- [ ] The production DB dump containing employee PII must never be committed or shared.

## Reporting

Found something? Open a private issue on this (private) repository — do not disclose publicly.
