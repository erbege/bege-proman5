# TODOS - Progress Report & Weekly Report Refactoring

## Phase 1 — Fondasi ✅ COMPLETED (2026-05-01)

- [x] **Task 1**: Buat `ProgressReportService` — Single source of truth untuk create/delete/recalculate
- [x] **Task 2**: Refactor `ProgressReportController` — Delegasi ke service (153 → 95 LOC)
- [x] **Task 3**: Refactor `ProgressReportManager` Livewire — Delegasi ke service (232 → 188 LOC)
- [x] **Task 4**: Refactor `Api\ProgressReportController` — Delegasi ke service (101 → 72 LOC)
- [x] **Task 5**: Hapus 4 method duplikat di `WeeklyReportController` (849 → 669 LOC, -180 LOC)
- [x] **Task 6**: Fix bug `collectItemCumulatives` missing di `WeeklyReportService`
- [x] **Task 7**: Fix delete logic — gunakan latest cumulative (bukan SUM)
- [x] **Task 8**: Tambah `DB::transaction` + `lockForUpdate` di `ProgressReportService`
- [x] **Task 9**: Hapus model boot hooks (conflict dengan service)

## Phase 2 — Pengayaan Data ✅ COMPLETED (2026-05-01)

- [x] **Task 1**: Migration — tambah field PUPR (equipment_details, safety_details, material_usage_summary, weather_duration, next_day_plan)
- [x] **Task 2**: Update `ProgressReport` model — fillable, casts, computed accessors (has_equipment, has_material_usage, has_safety_data, equipment_count, safety_incident_count)
- [x] **Task 3**: Buat `DocumentationService` — unifikasi upload, addProgressPhotos, removeDocumentation, getProgressPhotosForPeriod (174 LOC)
- [x] **Task 4**: Refactor `WeeklyReportController` — delegasi 5 method documentation ke service (669 → 611 LOC, -58 LOC)
- [x] **Task 5**: Update `ProgressReportManager` Livewire — tambah form fields PUPR (188 → 214 LOC)
- [x] **Task 6**: Update Blade view — collapsible form sections (Peralatan, Material, K3/Safety, Rencana Esok Hari) + detail modal tables (657 → 926 LOC)
- [x] **Task 7**: Run migration berhasil

## Phase 3 — Approval Workflow ✅ COMPLETED (2026-05-01)

- [x] Migration: status + approval fields di progress_reports & weekly_reports
- [x] Approval logic di `ProgressReportService` dan `WeeklyReportService`
- [x] Weekly Report review workflow (draft → in_review → approved → published)
- [x] Notification untuk approval events (WeeklyReportStatusNotification)
- [x] Update views: approval buttons, status badges (show.blade.php & index.blade.php)

## Phase 4 — Advanced ✅ COMPLETED (2026-05-01)

- [x] Monthly Report model + service + views
- [x] "Copy from previous" UX feature
- [x] Auto weather API integration
- [x] Audit trail & revision history
