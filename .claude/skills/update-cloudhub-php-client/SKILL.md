---
name: update-cloudhub-php-client
description: >
  Re-sync this PHP client (cloudHubPhpClient, Composer package lacuna/cloudhub-client) with the
  CloudHub .NET API: capture the latest OpenAPI spec from CloudHub, diff it, HAND-APPLY the changes
  to the hand-written classes under app/Cloudhub/, verify (php -l + a Guzzle MockHandler test), and
  bump the version. Use whenever the client must mirror new/changed CloudHub endpoints or models,
  after CloudHub is updated, or when asked to "update / regenerate / sync the cloudhub php client".
---

# Update the CloudHub PHP client

This client is **hand-written** — there is **no code generator** (unlike the sibling
`cloudhub-java-client`, which is OpenAPI-Generator output). "Syncing" means: capture the CloudHub
OpenAPI spec, read its diff, and **edit the PHP classes by hand** to match. The spec is the
source of truth and the change detector; you supply all the code.

## How the pieces fit (read first)

- `openapi/cloudhub.json` is the committed **source of truth**. `git diff` on it = "what changed upstream".
- All library code lives under `app/Cloudhub/` and is **hand-maintained** — every file is the public
  contract (Composer package `lacuna/cloudhub-client`, PSR-4 `Lacuna\Cloudhub\` → `app/Cloudhub`).
  There are no generated files and nothing to protect from a generator; instead, **preserve backward
  compatibility** of existing class names, method names, and constructor signatures.
- `scripts/Update-PhpClient.ps1` does the two mechanical halves (capture spec; lint + test). The
  manual editing happens in between and is what this skill guides.
- The version is **git-tag driven** — `composer.json` has no `version` key. Releasing = tagging.

### Current surface (as of CloudHub 2.0.0)
`CloudhubClient` methods: `createSessionAsync`, `getCertificateAsync`, `signHashAsync` (the three the
PkiSuiteSamples PHP sample uses), plus 2.0.0 additions `createServiceSessionAsync`,
`getServiceAvailabilityAsync`, `getCustomStateAsync`, `getCertificateModelAsync`. Models/enums:
`SessionCreateRequest`, `ServiceSessionCreateRequest`, `ServiceSessionCreateResponse`, `SessionModel`,
`SignHashRequest`, `TrustServiceAuthParametersModel`, `TrustServiceInfoModel`,
`TrustServiceSessionTypes`, `IdentifierTypes`, `GetServiceAvailabilityResponse`, `CertificateModel`,
`Util`. HTTP goes through `RestClient` (Guzzle), which sends the `x-api-key` header on every call.

> `createServiceSessionAsync($name, ServiceSessionCreateRequest)` → `POST /api/sessions/services/{name}`
> starts a session against one named trust service (e.g. `"safeid"`) and needs **no CPF/CNPJ** — the
> provider identifies the signer during its own auth flow (e.g. a QR code). Contrast with
> `createSessionAsync` (`POST /api/sessions`), which either discovers services by identifier or, when
> given an empty identifier + no `discover` flag, returns every configured service.
>
> The client deliberately implements only the endpoints its consumers need — it does **not** cover
> the entire CloudHub API. The one not-yet-implemented 2.0 endpoint is `GET /api/sessions/services`
> (list `TrustServiceInfoModel[]` by identifier, no session/authUrl). Add it only when a consumer
> needs it (see step 4).

## Prerequisites

- **.NET SDK** matching CloudHub's target framework (currently `net10.0`) to build + capture the spec.
  The `swashbuckle.aspnetcore.cli` tool is pinned in `.config/dotnet-tools.json`.
- The **CloudHub repo** checked out, by default a sibling folder `../cloudhub`.
- For verification: **PHP 7.4+** (the code uses typed properties) and **Composer**. If absent, you can
  still capture the spec and hand-edit; verification degrades to a syntax review + flags it.

## Procedure

### 1. Capture the new spec
Capture-only first, so you can inspect the diff before editing:
```
pwsh scripts/Update-PhpClient.ps1 -SpecOnly
```
This builds CloudHub, then writes `openapi/cloudhub.json` via the Swashbuckle CLI (`dotnet swagger
tofile ... v1`). If build/capture fails, see **Troubleshooting**.

### 2. Review what changed upstream
```
git diff -- openapi/cloudhub.json
```
Write a short human summary grouped as:
- **Added** paths / schemas / properties
- **Removed** paths / schemas / properties  ← breaking
- **Changed** types, `required`, enum members, `format`  ← often breaking

### 3. Classify the change → decide the version bump
Use [semver](https://semver.org) against the README constraint / latest git tag:

| Change | Bump |
|---|---|
| Only additive (new endpoints/models/optional fields) | **minor** |
| Removed/renamed endpoint, field, or enum member; a property became `required`; a type or wire `format` changed; an enum's underlying type changed (e.g. integer→string) | **major** |
| Doc-only / cosmetic | **patch** |

State the recommended new version and why. (CloudHub 2.0.0 was a **major** bump because
`TrustServiceSessionTypes` went from integer to string on the wire.)

### 4. Map each spec change to a hand edit (this is the judgment core — FLAG, don't guess)
There is no generator, so **every** change is a manual edit. Walk the diff and apply:

- **New / changed schema property** → edit the matching class under `app/Cloudhub/`. Request models
  (e.g. `SessionCreateRequest`) expose public properties and take them via the constructor — **append
  new optional params to the end** of the constructor so existing callers keep working. Response
  models parse an associative array in their constructor (`$data['field']`); guard nullable fields
  with `isset(...) ? ... : null`.
- **Enum change**:
  - *String enum* (CloudHub 2.0's style): a PHP class of `const NAME = "NAME";`. Just add/rename consts.
  - *Integer enum*: `const NAME = <int>;` — a wire-format change from/to string is **breaking** (major).
- **New endpoint** → add a method on `CloudhubClient` mirroring the existing ones (build
  `$this->baseUrl . "api/..."`, call `$this->client->get()/post()`, wrap the result in a model).
  For a GET that returns an object, pass `true` to `get()` for an associative array, then build the
  model. **Decide whether the endpoint belongs in this thin client at all**: if no consumer
  (PkiSuiteSamples PHP, or the requester) needs it, **flag it and leave it out** rather than growing
  untested surface.
- **Binary payloads** (`type: string, format: byte`): base64 — mirror `getCertificateAsync`
  (returns the base64 string) and `Util::base64Convert` for inputs.
- **Removed endpoint/field** → confirm intentional; call it out as breaking; only delete if you're
  sure no consumer depends on it (this is a published package).
- **New auth scheme** → `RestClient` hard-codes the `x-api-key` header; update it if a new scheme is
  needed, but flag it for a human first.

### 5. Verify
```
pwsh scripts/Update-PhpClient.ps1 -SkipCloudHubBuild        # re-capture (fast) + lint + test
# or, to only lint+test the current code:
pwsh scripts/Update-PhpClient.ps1 -VerifyOnly
```
This runs `php -l` on every file, then `composer install` + `vendor/bin/phpunit`. The MockHandler
test in `tests/CloudhubClientTest.php` asserts the `x-api-key` header is sent and that
`TrustServiceSessionTypes` serializes as a **string**. Extend it when you add endpoints/models.
(`tests/` is version-controlled so a fresh checkout and CI can run it; only `vendor/` and
`.phpunit.result.cache` are git-ignored.)
If PHP/Composer are unavailable, review syntax by eye and **say so** in the report.

### 6. Finalize
- Bump the version constraint in `README.md` (e.g. `^2.0.0`). Do **not** add a `version` key to
  `composer.json` — this package is versioned by git tag.
- Re-run `git status` / `git diff` and sanity-check that only intended files changed and the public
  API stayed backward-compatible (existing method/ctor signatures unchanged).

### 7. Report
Tell the user: the new version, the upstream-change summary, each flagged judgment item and how you
resolved it, and the verification result. Do **not** commit/push/tag unless asked.

## Verifying the result
- `php -l` clean on all `app/Cloudhub/*.php`.
- `vendor/bin/phpunit` green (x-api-key header + string-enum wire format).
- Backward compatibility intact: `new CloudhubClient($baseUrl, $apiKey)` and the existing
  `createSessionAsync` / `getCertificateAsync` / `signHashAsync` signatures unchanged; the
  PkiSuiteSamples PHP cloudhub flows still compile against the new classes.
- Idempotence: re-running `-SpecOnly` with no upstream change leaves an empty `git diff` on
  `openapi/cloudhub.json`.

## Troubleshooting
- **CloudHub build fails on restore**: the private Lacuna NuGet feed isn't configured. Add it
  (`dotnet nuget add source ...`) or build on a machine with a warm package cache. If you only have a
  pre-exported `swagger.json`, copy it to `openapi/cloudhub.json` and skip to step 2.
- **`dotnet swagger tofile` fails**: confirm the Swagger doc name is still `v1`
  (`Site/Startup.cs` → `SwaggerDoc("v1", ...)`); pass the matching name as the last arg.
- **`count(): Argument must be Countable` on PHP 8**: a response model assumed an array that came back
  null — guard with `isset()`/`is_array()` before `count()`/`foreach`.
- **phpunit can't find classes**: `composer install` first (it wires the `Lacuna\Cloudhub\` PSR-4
  autoload that `phpunit.xml` bootstraps via `vendor/autoload.php`).
- **No PHP on the machine**: capture + hand-edit still work; verification degrades to a manual syntax
  review. Install PHP 7.4+ and Composer to run `php -l` + phpunit.
- **`composer install` warns "lock file is not up to date"**: the committed `composer.lock` already
  includes the `phpunit` dev-dependency, so a clean `composer install` works as-is. If you edit
  `composer.json`'s requirements, run `composer update --lock` (hash-only refresh, no re-resolve) or
  `composer update` to resync the lock — otherwise the tests silently get skipped.
- **`composer update` is blocked by a Guzzle security advisory**: the client pins
  `guzzlehttp/guzzle ^6.5.8`, which current Composer flags (there is no non-vulnerable 6.x). Plain
  `composer install` from the committed lock is unaffected (it does not re-resolve), so verification
  still runs. To re-resolve anyway, either `composer config policy.advisories.block false` for that
  run, or migrate to Guzzle 7 (see the `feature/guzzle7` branch) — the latter is the real remediation.
- **`405 Method Not Allowed` on `POST /api/sessions/`**: the configured base URL is `http://`. CloudHub
  301-redirects http→https, and a non-strict redirect downgrades the POST to a GET (`GET /api/sessions`
  isn't a route → 405). Fixed in 2.0.1 — `RestClient` sets `allow_redirects => ['strict' => true]` so the
  POST is preserved to the https location. Prefer configuring an `https://` endpoint regardless.
