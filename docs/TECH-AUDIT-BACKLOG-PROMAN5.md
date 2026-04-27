# TECH AUDIT BACKLOG PROMAN5

Tanggal audit: 27 April 2026  
Scope: `bege-proman5` (Laravel 12, Livewire, Sanctum, Spatie Permission)

## Ringkasan Eksekutif
Secara fungsional, fondasi aplikasi sudah kuat untuk domain kontraktor: project, RAB/AHSP, procurement, inventory, progress report, dan API mobile sudah terhubung.  
Gap utama ada di 5 area: `authorization consistency`, `API contract consistency`, `workflow governance`, `concurrency safety`, dan `test hardening`.

## Prioritas Backlog

## P0 - Critical (Minggu 1)
1. Perbaiki kebocoran data lintas proyek di API MR/PR.
Target file: [MaterialRequestController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/MaterialRequestController.php:17), [PurchaseRequestController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/PurchaseRequestController.php:16)
Masalah: Filter supervisor memakai pola `whereNotIn(...)->orWhere(...)` yang membuka data proyek di luar scope user.
Rekomendasi: Ganti ke allow-list berbasis keanggotaan project + policy.
Estimasi: 1 hari

2. Tutup endpoint debug AI di web route.
Target file: [web.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/routes/web.php:16)
Masalah: Endpoint `/ai` publik berisiko abuse biaya dan disclosure.
Rekomendasi: Hapus endpoint, atau minimum lindungi dengan `auth` + `app()->isLocal()`.
Estimasi: 0.5 hari

3. Enforce scoped nested route binding untuk resource child (project -> report/usage).
Target file: [WeeklyReportController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/WeeklyReportController.php:123)
Masalah: Belum ada guard eksplisit bahwa `WeeklyReport` milik `Project` yang sama.
Rekomendasi: Pakai scoped bindings + fallback 404 seragam.
Estimasi: 1 hari

4. Perbaiki setup 2FA Jetstream/Fortify yang broken.
Target file: [User.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Models/User.php:17), [fortify.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/config/fortify.php:129)
Masalah: Test 2FA gagal karena method `twoFactorQrCodeSvg()` tidak tersedia.
Rekomendasi: Tambah trait 2FA yang sesuai versi Jetstream/Fortify + retest.
Estimasi: 1 hari

## P1 - High (Minggu 2-3)
1. Terapkan `FormRequest` untuk semua endpoint create/update/approve/reject.
Target: seluruh `app/Http/Controllers/Api/*Controller.php`
Masalah: Validasi tersebar dan tidak reusable.
Rekomendasi: Pisah request class per use case (`StoreMaterialRequestRequest`, `ApprovePurchaseRequestRequest`, dll).
Estimasi: 3-4 hari

2. Standardisasi response API dengan `JsonResource` + envelope tunggal.
Masalah: Format response campur (`data`, raw model, raw pagination, `success` flag).
Rekomendasi: Contract standar:
- success
- message
- data
- meta (opsional pagination)
Estimasi: 3 hari

3. Refactor controller gemuk (khusus WeeklyReport) ke service/action layer.
Target: [WeeklyReportController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/WeeklyReportController.php:27)
Masalah: HTTP orchestration bercampur logic domain.
Rekomendasi: Pecah menjadi `WeeklyReportService` + action classes (`UpdateCoverAction`, `CascadeCumulativeAction`, dll).
Estimasi: 4-5 hari

4. Hardening error handling.
Masalah: Beberapa endpoint expose pesan exception mentah (`$e->getMessage()`).
Target file: [MaterialUsageController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/MaterialUsageController.php:39), [WeeklyReportController.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Http/Controllers/Api/WeeklyReportController.php:48)
Rekomendasi: Standard error mapper + logging internal, response publik tetap generic.
Estimasi: 1-2 hari

## P2 - Medium (Minggu 4+)
1. Stabilkan generator nomor dokumen MR/PR (race-safe).
Target file: [MaterialRequest.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Models/MaterialRequest.php:52), [PurchaseRequest.php](/d:/01%20BEGE/99%20DEVS/LARAVEL/PROMAN5/bege-proman5/app/Models/PurchaseRequest.php:84)
Masalah: Query `last + 1` rentan duplikasi saat concurrent request.
Rekomendasi: Sequence table + row lock dalam transaction.
Estimasi: 2 hari

2. Approval matrix bertingkat untuk PR/MR/PO.
Masalah: Approval masih 1-step, belum mempertimbangkan nominal, role, jenis proyek.
Rekomendasi: Rule engine ringan:
- threshold nilai
- role approver per project
- SLA/timeout escalation
Estimasi: 4-6 hari

3. Tambah audit trail status transition.
Masalah: Activity log sudah ada package, tetapi belum distandardisasi untuk event approval lifecycle.
Rekomendasi: Event + listener untuk `requested`, `approved`, `rejected`, `closed`.
Estimasi: 2 hari

4. Perluas test coverage domain kritikal.
Masalah: Feature test kebanyakan smoke test status code.
Rekomendasi:
- test otorisasi lintas proyek
- test approval edge cases
- test atomic inventory mutation
- test idempotency endpoint approval
Estimasi: 4 hari

## Rekomendasi Arsitektur Modul
1. Gunakan `Policy` per aggregate utama: `Project`, `MaterialRequest`, `PurchaseRequest`, `WeeklyReport`, `Inventory`.
2. Standarisasi state machine status dokumen (`draft -> pending -> approved/rejected -> processed/closed`).
3. Pisahkan read model API (resource DTO) dari write logic (service/action) supaya web dan API tidak divergen.
4. Bentuk folder `app/Domain` (Procurement, Inventory, Reporting) untuk menurunkan coupling antar controller.

## Benchmark Praktik Aplikasi Sejenis Indonesia
Polanya mengikuti yang dipakai produk umum di Indonesia:
1. Mekari Expense: purchase request + approval workflow bertingkat.
2. Accurate Online: fitur persetujuan transaksi dan kontrol otorisasi.
3. HashMicro Construction: integrasi budgeting, progress, procurement, inventory.

## Urutan Implementasi Disarankan
1. Selesaikan semua item P0 dulu.
2. Lanjutkan P1 dalam 2 sprint (API contract + FormRequest + refactor WeeklyReport).
3. Jalankan P2 bertahap sambil menambah regression test.

## Definisi Selesai (DoD) per Item
1. Ada unit/feature test yang mengunci behavior baru.
2. Endpoint terdampak terdokumentasi di Scribe/Scramble.
3. Tidak ada breaking change tanpa versioning/compat note.
4. Security review minimal pada authz, data exposure, dan input validation.
