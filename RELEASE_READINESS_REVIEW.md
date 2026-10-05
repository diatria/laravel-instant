# Review Kesiapan Rilis `diatria/laravel-instant` menuju v1.0

Tanggal review: 5 Oktober 2026  
Scope: struktur package, Composer metadata, service provider, route/config, source code, asset publish, dokumentasi, dan tooling.

## Ringkasan eksekutif

Package ini sudah memiliki fondasi yang berguna: PSR-4 autoload, auto-discovery Laravel, trait CRUD, command generator, contoh penggunaan, dan mekanisme publish asset. Namun, dalam kondisi sekarang saya belum menyarankan rilis sebagai `1.0.0` di Packagist.

## Keputusan scope v1

RBAC tetap menjadi fitur bawaan package untuk versi 1.0. Konsekuensinya, package harus menyediakan kontrak model, migration, service, controller, route, permission, dan konfigurasi RBAC yang konsisten. RBAC tidak akan dipisahkan menjadi package lain pada tahap ini, tetapi route dan migration tetap opt-in agar instalasi package tidak mengubah aplikasi secara diam-diam.

Prioritas sebelum v1.0:

1. Pastikan kontrak RBAC tidak bergantung pada model `App\` yang tidak tersedia.
2. Selesaikan publish model/migration tanpa risiko overwrite yang tidak disengaja.
3. Bekukan API contract, status HTTP, validation, query allowlist, dan authorization behavior.
4. Tambahkan test integration dengan Orchestra Testbench, static analysis, dan CI matrix.
5. Dokumentasikan breaking changes, konfigurasi, security model, dan prosedur upgrade.

Status perubahan yang sudah diterapkan di repository:

- [Selesai] Compatibility PHP 7.4+, Laravel 6–8, Carbon 2, dan JWT v5/v6.
- [Selesai] Config route konsisten menggunakan `route.prefix`.
- [Selesai] Route bawaan opt-in melalui `route.enabled=false`.
- [Selesai] Migration loading opt-in melalui `database.migrations.enabled=false`.
- [Selesai] Config auth/database/permission utama tersedia.
- [Selesai] Secret JWT tidak boleh kosong saat digunakan.
- [Diterapkan sebagian] Fondasi PHPUnit/Testbench, PHPStan, Composer scripts, dan GitHub Actions sudah ditambahkan. PHPUnit lulus (4 test, 9 assertion) dan PHPStan lulus menggunakan baseline untuk 178 issue legacy; issue legacy tersebut masih perlu dibereskan bertahap.
- [Diterapkan] Environment Docker PHP 7.4 + Composer + SQLite tersedia melalui `docker compose run --rm package-test`; pipeline berhasil dijalankan.
- [Diterapkan sebagian] Service/controller RBAC utama tidak lagi mengimpor `App\Models` atau `App\Http\Controllers\Controller`; model default package tersedia. Model override penuh melalui config masih perlu diselesaikan bersama constructor injection.
- [Diterapkan sebagian] Published models sekarang diarahkan ke `App\Models\LaravelInstant`; migration loading tetap opt-in, tetapi migration naming dan upgrade path masih perlu ditinjau.
- [Diterapkan sebagian] Validation failure sekarang memakai HTTP 422, response mengenali status 400/404/422, route middleware dapat dikonfigurasi, dan query field/order divalidasi; audit API/error/security menyeluruh masih perlu dilanjutkan.

## Temuan blocker

### 1. Constraint PHP bertentangan dengan kode dan dependency

`composer.json:17` menyatakan PHP `>=7.4`, tetapi source menggunakan union types seperti `int|array` dan `int|string` (`src/Traits/InstantServiceTrait.php:151`, beberapa service lain), yang membutuhkan PHP 8.0+. Selain itu, Carbon 3 tidak cocok dengan klaim dukungan PHP 7.4.

Saran:

- Jika target v1 adalah Laravel modern, naikkan minimum ke PHP `^8.2` dan dokumentasikan matrix Laravel yang didukung.
- Jika ingin mendukung PHP 7.4, hilangkan sintaks PHP 8 dan gunakan dependency Carbon yang kompatibel; ini sebaiknya tidak dipilih untuk package baru.
- Tambahkan `require` untuk `illuminate/*` yang memang digunakan, atau gunakan `laravel/framework` bila package memang hanya untuk aplikasi Laravel penuh. Gunakan constraint yang tepat, bukan mengandalkan dependency transitif aplikasi.

### 2. Tidak ada test suite dan automation rilis

Fondasi test dan automation sekarang tersedia melalui `tests/`, `phpunit.xml.dist`, PHPStan config/baseline, Docker, dan GitHub Actions. Pipeline yang sudah dijalankan lulus dengan 4 test dan 9 assertion.

Saran minimum:

- Gunakan Orchestra Testbench untuk menguji service provider dan aplikasi Laravel minimal.
- [Diterapkan sebagian] Test config/provider dan query security sudah ada; feature test CRUD, authorization, JWT, migration, publish, integer/UUID, dan transaksi masih perlu ditambahkan.
- [Diterapkan sebagian] Unit test QueryMaker sudah ada; Response, Token, Permission, dan generator command masih perlu ditambahkan.
- CI: PHPUnit, PHPStan, dan `composer validate` sudah ada; workflow juga memiliki compatibility job Laravel 6, 7, dan 8. PHP-CS-Fixer/Pint serta `composer audit` dengan Composer modern masih perlu ditambahkan.
- Smoke test instalasi package pada project Laravel baru masih perlu ditambahkan.

## Temuan penting berdasarkan kode

### 3. Route dan config tidak sinkron — diperbaiki

Masalah awal ada pada `publish/config/laravel-instant.php` yang mendefinisikan `route_prefix`, sementara `src/Routes/api.php` membaca `laravel-instant.route.prefix`. Selain itu, route menambahkan string `api/` secara hardcode.

Perbaikan yang sudah diterapkan:

- Config sekarang menggunakan satu key: `laravel-instant.route.prefix`.
- Default prefix ditetapkan eksplisit menjadi `api`.
- Route membaca prefix dari config dan tidak lagi menambahkan `api/` secara hardcode.
- Prefix dapat diubah menjadi `api/v1` atau nilai lain dari file config aplikasi.

Yang masih perlu ditambahkan pada tahap testing adalah assertion terhadap hasil `route:list` di Laravel 6, 7, dan 8. Route middleware sekarang diambil dari config sehingga consumer dapat menyesuaikannya.

### 4. Route bawaan dimuat selalu dan domain application tercampur dengan library

Masalah route selalu aktif sudah diperbaiki. `src/LaravelInstantServiceProvider.php` sekarang hanya memanggil `loadRoutesFrom()` jika `laravel-instant.route.enabled` bernilai `true`; default-nya `false`. Prefix route juga tetap dikendalikan oleh config.

Karena RBAC tetap bawaan, risiko domain application masih harus diselesaikan di dalam package, bukan dihilangkan dengan menghapus fitur RBAC.

Service bawaan juga mengimpor model aplikasi pengguna (`App\Models\Permission`, `App\Models\RolePermission`, `App\Models\Role`), contohnya `src/Services/PermissionService.php:4` dan `src/Services/RoleService.php:4`. Ini membuat package tidak self-contained dan gagal bila consumer tidak memiliki model dengan nama persis tersebut.

Saran desain:

- Jadikan route package opt-in melalui config `routes.enabled`, atau hilangkan route/controller domain dari core package.
- [Diterapkan] Route package opt-in melalui `route.enabled` dan default-nya `false`.
- [Tidak dipilih untuk v1] Pisahkan package menjadi core CRUD/query dan package opsional untuk RBAC/auth.
- Inject model melalui contract/config atau gunakan model package secara konsisten; jangan hardcode `App\Models` yang tidak dijamin tersedia.
- Jika asset model memang harus dipublish, gunakan model aplikasi sebagai extension point dan dokumentasikan penggantian class-nya.
- Beri middleware eksplisit pada route auth/admin; jangan menganggap permission check di controller cukup.

### 5. Publish model dan migration dapat menimpa atau mengganggu aplikasi

Provider masih menyediakan publish model/migration karena asset ini merupakan bagian dari API lama package, tetapi migration loading otomatis sekarang dimatikan secara default melalui `database.migrations.enabled=false`. Dokumentasi AI juga tidak lagi dipublish ke `CLAUDE.md`, melainkan `laravel-instant.md`.

Risiko overwrite saat consumer menjalankan `vendor:publish --force` tetap ada dan memerlukan breaking-change design tersendiri pada v1.

Saran:

- Jangan publish User model/migration default secara otomatis sebagai bagian dari package CRUD.
- [Sebagian diterapkan] Migration tidak lagi diload otomatis; consumer harus mengaktifkannya secara eksplisit.
- Beri nama migration unik dan migration terpisah untuk tabel package.
- Jangan memakai model application sebagai asset yang diasumsikan aman untuk overwrite.
- Sediakan migration stub yang dapat dikustomisasi, atau gunakan migration package dengan tabel yang benar-benar dimiliki package.
- Uji `vendor:publish --tag=... --force` dan perilakunya saat file tujuan sudah ada.

### 6. Config belum merepresentasikan semua konfigurasi yang dipakai

Config sekarang sudah mencakup `database.primary_key`, `database.migrations.enabled`, `route.enabled`, `route.prefix`, `route.middleware`, `models.*`, `class_permission`, `disable_permissions`, dan `auth.*` yang digunakan source. Secret JWT juga divalidasi saat token dibuat atau diverifikasi sehingga konfigurasi secret kosong gagal secara eksplisit.

Sisa pekerjaan: audit seluruh pemakaian `env()` langsung dan pindahkan ke config agar `config:cache` serta test environment lebih mudah diprediksi.

### 7. API contract dan error handling belum stabil

Beberapa indikasi yang perlu dibereskan sebelum public API dibekukan:

- Validation failure di `InstantServiceTrait.php:177-180` memakai status `500`; seharusnya `422`.
- `remove()` mendeklarasikan return `void` (`InstantServiceTrait.php:151`), tetapi controller meneruskan hasilnya ke response.
- `find()` dan `update()` tidak memiliki contract yang jelas untuk not-found, invalid ID, dan model UUID.
- Banyak method menangkap `Throwable` lalu membungkus exception dengan pesan mentah; ini dapat membocorkan detail database dan menghilangkan stack/context.
- [Diterapkan] QueryMaker sudah dipisah menjadi fungsi build/filter/relation/column/order/execute, memakai query builder baru, mereset state saat re-use, memperbaiki `authentication` fallback, dan menolak query field/order direction yang tidak valid. Allowlist relation per resource masih perlu ditambahkan.
- `table()` membangun URL memakai `env('APP_URL')` (`InstantServiceTrait.php:253`), yang tidak ideal untuk queued request, test, proxy, atau runtime config cache.
- `DELETE` memakai route optional ID sementara contoh API memakai body `id`; pilih satu contract yang konsisten.

Saran: tetapkan DTO/request/response contract, gunakan Laravel validation rules/Form Request, gunakan exception khusus dengan status code terkontrol, dan tambahkan OpenAPI atau dokumentasi endpoint yang dapat diuji.

### 8. Authentication dan security perlu threat model yang eksplisit

Package menyediakan JWT sendiri sekaligus membaca token/cookie dan permission. Ini memperbesar surface area security. Secret, algoritma, expiry, cookie policy, refresh token rotation, revocation, issuer/audience, dan CSRF behavior harus didefinisikan.

Catatan khusus:

- Default cookie `secure=true` dan `samesite=none` (`publish/config/laravel-instant.php:19-21`) perlu dijelaskan karena memerlukan HTTPS dan berdampak pada cross-site behavior.
- [Diterapkan] Tidak ada fallback secret kosong; JWT gagal secara eksplisit saat `LI_SECRET_KEY` belum diisi.
- Pertimbangkan memakai guard Laravel/Sanctum atau package JWT yang mapan sebagai adapter, bukan mempertahankan auth implementation di core CRUD.
- Test token expired, invalid signature, refresh reuse, cookie flags, permission bypass, dan user enumeration masih perlu ditambahkan.
- Pastikan response debug tidak pernah mengembalikan token atau data sensitif; audit semua field, bukan hanya request body.

### 9. Versi Laravel belum dinyatakan

`composer.json` tidak mendeklarasikan `illuminate/*` atau `laravel/framework`, sehingga Composer tidak memberi consumer informasi Laravel version compatibility. Provider dan source menggunakan banyak komponen Laravel.

Saran: pilih salah satu strategi:

- `illuminate/support`, `illuminate/console`, `illuminate/database`, `illuminate/http`, dan komponen lain sebagai dependency langsung dengan range versi yang jelas; atau
- dukung Laravel major tertentu dengan `laravel/framework` sebagai dependency langsung.

Cantumkan tabel compatibility di README, misalnya PHP 8.2 + Laravel 10/11/12, dan uji tiap kombinasi yang diklaim.

## Kualitas package dan developer experience

### Metadata Composer yang masih perlu dilengkapi

Tambahkan, sesuai kebutuhan:

- `homepage`, `support`, `funding`, `authors.email`, repository URL, dan deskripsi yang spesifik.
- `require-dev` untuk test dan quality tools.
- `autoload-dev` untuk tests.
- `minimum-stability`/`prefer-stable` hanya bila memang diperlukan.
- `config.sort-packages` dan script standar (`test`, `lint`, `analyse`, `format-check`).
- `extra.laravel.dont-discover` tidak wajib, tetapi dokumentasikan bila discovery dapat dimatikan.

Jangan release berdasarkan branch atau folder clone. Gunakan Git tag semver, changelog terstruktur, dan validasi package yang benar-benar di-install dari archive/Packagist.

### Dokumentasi

README sudah cukup kaya, tetapi masih ada risiko copy-paste failure dan asumsi yang tidak aman:

- Bagian instalasi mencampur instalasi Packagist, git clone, dan path repository lokal.
- Contoh composer berisi komentar di JSON sehingga bukan JSON valid.
- README mengklaim `php >=7.4` secara implisit lewat metadata, sementara contoh dan source modern.
- Compatibility, konfigurasi, middleware/auth prerequisites, upgrade guide, dan security policy sudah tersedia; limitations dan troubleshooting berbasis error nyata masih perlu diperdalam.
- `Changelog` tidak mengikuti format/extension umum dan isinya tidak berurutan secara konsisten.
- `CLAUDE.md` sebagai dokumentasi yang dipublish ke root aplikasi consumer berpotensi menimpa file milik consumer; gunakan nama file package-specific atau jadikan dokumentasi opsional.

Tambahkan minimal `CONTRIBUTING.md`, `SECURITY.md`, `CHANGELOG.md`, dan `UPGRADING.md`.

### Konsistensi source

Ada indikasi API dan naming belum dibekukan: method relation memakai `Role()`/`Permission()` dengan huruf kapital, konfigurasi memakai beberapa pola nama, dan ada service yang menimpa method trait dengan response handling berbeda. Gunakan PSR-12, nama method lowercase untuk relation, type declaration yang konsisten, return type yang akurat, serta PHPDoc generics bila diperlukan.

## Roadmap yang disarankan

### Milestone 0 — Release candidate internal

- Putuskan scope v1: CRUD core saja atau termasuk RBAC/JWT.
- Tetapkan PHP/Laravel matrix dan perbaiki `composer.json`.
- Perbaiki route key, config schema, namespace model, dan return/error contract.
- Nonaktifkan route bawaan secara default.

### Milestone 1 — Testable package

- Tambahkan Testbench test app.
- Tambahkan test migration, publish, command generator, query, authorization, auth, dan CRUD.
- Tambahkan PHPStan/Psalm, formatter, CI matrix, `composer validate`, dan `composer audit`.
- Jalankan test dari fresh Laravel app dan dari aplikasi dengan model User sendiri.

### Milestone 2 — Public API freeze

- Tetapkan contract request/response dan status HTTP.
- Dokumentasikan extension points dan deprecation policy.
- Tambahkan changelog semver dan upgrade guide.
- Audit keamanan independen atau minimal review threat model.

### Milestone 3 — Rilis v1.0

- Tag `v1.0.0` hanya setelah CI hijau pada seluruh matrix.
- Cek archive Composer agar semua file yang dibutuhkan masuk dan file internal yang tidak perlu tidak ikut.
- Test install via Packagist pada project baru.
- Publish release notes dengan known limitations.

## Checklist go/no-go

- [x] PHP minimum sesuai sintaks dan dependency.
- [ ] Laravel compatibility matrix tersedia dan diuji pada runtime CI.
- [x] Tidak ada dependency runtime tersembunyi pada `App\` consumer untuk service/controller bawaan.
- [x] Route package opt-in atau jelas middleware dan namespace-nya.
- [x] Publish model tidak menimpa model consumer; migration loading tetap opt-in.
- [x] Config key utama lengkap dan konsisten.
- [x] Test suite berjalan di CI.
- [x] Static analysis berjalan di CI; `composer audit` memerlukan job/toolchain Composer modern terpisah karena Composer 2.2 pada PHP 7.4 belum menyediakan command tersebut.
- [ ] HTTP status/error contract terdokumentasi secara lengkap.
- [ ] JWT/cookie/permission threat model dan test tersedia.
- [x] README, CHANGELOG, SECURITY, CONTRIBUTING, dan UPGRADING tersedia.
- [ ] Fresh-install smoke test dari Packagist/archive berhasil.

## Kesimpulan

Package ini sudah memiliki fondasi release candidate: pipeline Docker lulus, RBAC/controller tidak bergantung pada class `App\` consumer, route/migration opt-in, publish model lebih aman, dan query input divalidasi. Package belum boleh diberi label final `v1.0.0` sampai feature/security test, Laravel 6–8 matrix, migration upgrade path, dan HTTP contract lengkap selesai.
