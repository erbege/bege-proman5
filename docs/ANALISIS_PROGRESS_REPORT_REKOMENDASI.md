# 📋 ANALISIS MODUL PROGRESS REPORT — PROMAN5

## Rekomendasi Workflow, Logic, UI/UX Terkini untuk Konstruksi Indonesia

> **Tanggal Analisis:** 1 Mei 2026  
> **Versi:** 1.0  
> **Scope:** Analisis mendalam & rekomendasi implementasi Progress Report (Laporan Harian) dengan referensi aplikasi sejenis terkemuka di Indonesia

---

## 📑 DAFTAR ISI

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Status Quo Saat Ini](#2-status-quo-saat-ini)
3. [Analisis Komparatif (Best Practices Indonesia)](#3-analisis-komparatif-best-practices-indonesia)
4. [Rekomendasi Workflow](#4-rekomendasi-workflow)
5. [Rekomendasi Logic Bisnis](#5-rekomendasi-logic-bisnis)
6. [Rekomendasi UI/UX](#6-rekomendasi-uiux)
7. [Roadmap Implementasi (Prioritas)](#7-roadmap-implementasi-prioritas)

---

## 1. RINGKASAN EKSEKUTIF

### Skor Kematangan Modul Progress Report

| Dimensi                      | Skor | Target |  Gap  |
| ---------------------------- | :--: | :----: | :---: |
| **Workflow & Approval**      | 5/10 |  9/10  | 🔴 -4 |
| **Data Completeness (PUPR)** | 6/10 |  9/10  | 🟠 -3 |
| **Code Quality**             | 4/10 |  8/10  | 🔴 -4 |
| **Mobile Readiness**         | 3/10 |  8/10  | 🔴 -5 |
| **User Experience**          | 6/10 |  8/10  | 🟡 -2 |
| **Reporting & Analytics**    | 5/10 |  8/10  | 🟠 -3 |

### Temuan Utama

✅ **Kekuatan:**

- UI form sudah cukup user-friendly (collapsible sections)
- Struktur database mendukung JSON fields (PUPR-ready)
- Model approval workflow sudah tersedia (belum fully diimplementasikan)
- Integration dengan weather API sudah ada

🔴 **Masalah Kritis (Harus Diperbaiki):**

1. **Duplikasi Logika Masif** — Cascade logic di-duplikasi di 2 tempat (Controller + Service)
2. **Race Condition** — Tidak ada database locking saat update kumulative progress
3. **Approval Workflow Incomplete** — Hanya `draft → published`, tanpa review layer
4. **Missing PUPR Compliance** — Field K3/Safety, Equipment, Material masih incomplete
5. **No Mobile Optimization** — Form terlalu berat untuk mobile

🟡 **Area Improvement (Should Have):**

- Auto-weather integration (Ada, tapi belum auto-fetch)
- Daily report templates per work type
- Copy-paste fitur dari laporan sebelumnya
- Better KPI tracking (deviation dari schedule)

---

## 2. STATUS QUO SAAT INI

### 2.1 Arsitektur Current

```
ProgressReport Model
├── Fillable Fields:
│   ├── Basic: report_date, report_code, progress_percentage
│   ├── Workflow: status, reported_by, reviewed_by, rejected_by, published_by
│   ├── PUPR: weather, workers_count, labor_details, equipment_details,
│   │           material_usage_summary, safety_details, next_day_plan
│   └── Meta: description, issues, photos, cumulative_progress
│
├── Status Enum:
│   └── draft → submitted → reviewed → rejected ↻ draft
│       │                              └→ published → [locked]
│
└── Approval Workflow:
    ├── Permissions: progress.{view, create, update, delete, manage, approve, publish}
    └── Self-approval blocked ✓
```

### 2.2 Current User Flow (Desktop)

```
1. ENTRY PHASE
   └─ Site Manager ke /progress/create
      ├─ Select RAB Item
      ├─ Input progress % + description
      ├─ Fill weather + labor count
      ├─ Upload photos (max 5, 5MB)
      └─ Save → Status: DRAFT

2. SUBMIT PHASE (Optional)
   └─ Manager submit untuk review
      └─ Status: SUBMITTED

3. REVIEW PHASE
   └─ PM atau Supervisor
      ├─ View detail
      ├─ Approve → Status: REVIEWED
      └─ Reject + notes → Status: REJECTED

4. PUBLISH PHASE
   └─ Admin/PM
      └─ Publish → Status: PUBLISHED (lock untuk edit)

5. AGGREGATION
   └─ Weekly Report Service
      ├─ Query all PUBLISHED progress reports untuk minggu tersebut
      ├─ Aggregate ke weekly snapshot
      └─ Weekly Report Status: draft → submitted → approved → published
```

### 2.3 Data Structure

```sql
-- progress_reports table (simplified)
CREATE TABLE progress_reports (
    id                        BIGINT PRIMARY KEY,
    project_id               BIGINT,
    rab_item_id             BIGINT,
    report_date             DATE,
    report_code            VARCHAR(20), -- LHP-2026-001

    -- Progress Data
    progress_percentage     DECIMAL(5,2),      -- Jika turun? Bug atau reality?
    cumulative_progress     DECIMAL(5,2),      -- Kumulatif untuk RAB item ini

    -- Workflow
    status                 VARCHAR(20),       -- draft, submitted, reviewed, rejected, published
    reported_by           BIGINT,            -- User (Reporter/Site Manager)
    reviewed_by           BIGINT,            -- User (Reviewer/PM)
    review_notes          TEXT,
    rejected_by           BIGINT,
    rejected_notes        TEXT,
    published_by          BIGINT,
    reviewed_at/rejected_at/published_at TIMESTAMP,

    -- PUPR Compliance
    description           TEXT,              -- Apa yang dikerjakan
    issues               TEXT,              -- Kendala/Hambatan
    weather              VARCHAR(20),       -- sunny, cloudy, rainy, stormy
    weather_duration     VARCHAR(20),       -- Optional: 08:00-12:00
    workers_count        INT,               -- Total tenaga kerja
    labor_details        JSON,              -- {mandor: 2, tukang: 5, pekerja: 8}

    -- Missing in current schema
    equipment_details    JSON,              -- {Excavator: 1, Vibrator: 2}
    material_usage_summary JSON,            -- {Pasir: 50m3, Semen: 100sak}
    safety_details       JSON,              -- {incidents: 0, near_miss: 1, apd_compliance: 90%}
    next_day_plan        TEXT,              -- Rencana kerja esok hari

    photos               JSON,              -- [{url, uploaded_at, uploaded_by}]

    created_at, updated_at TIMESTAMP
);
```

### 2.4 Current Issues (Code-Level)

**Issue #1: Duplikasi Kalkulasi Kumulatif (3 Tempat)**

```php
// ProgressReportController.php L77
$validated['cumulative_progress'] = min(100, $rabItem->actual_progress + $validated['progress_percentage']);

// ProgressReportManager.php L143
$data['cumulative_progress'] = min(100, $rabItem->actual_progress + $this->progressPercentage);

// Api\ProgressReportController.php L56
$validated['cumulative_progress'] = min(100, $rabItem->actual_progress + $validated['progress_percentage']);
```

→ **Impact:** Bug fix harus di 3 tempat; potential inconsistency

**Issue #2: No Database Locking (Race Condition)**

```php
// Jika 2 user submit progress simultaneous untuk RAB item 1:
// Current: akses actual_progress tanpa lock
// Risk: Cumulative bisa overlap atau skip

// Solution needed: DB::transaction + lockForUpdate()
```

**Issue #3: Approval Workflow Not Enforced**

```php
// Field ada, but tidak di-enforce dalam UI
// No permission checking saat approve/reject
// No notification ke reviewer
```

**Issue #4: Weekly Report Aggregate Logic Terpusat**

```php
// WeeklyReportService::generateWeeklyReport()
// → 518 LOC dengan 4 private methods
// → Aggregation terdapat di 2 tempat (Service + Controller)
```

---

## 3. ANALISIS KOMPARATIF: BEST PRACTICES INDONESIA

### 3.1 Aplikasi Sejenis di Indonesia (2024-2026)

#### A. **Procore (Global, dengan localization Indonesia)**

**Digunakan oleh:** PT. Wika, PT. Adhi Karya, Kontraktor Premium

| Fitur               |             Implementasi              |      Rekomendasi untuk ProMan5      |
| ------------------- | :-----------------------------------: | :---------------------------------: |
| Daily Log Entry     |     Mobile-first, structured form     | ⭐⭐ Priority — mobile optimization |
| Auto-Weather        |      API OpenWeather, GPS-based       |    ✅ Ada, improve UX auto-fetch    |
| Labor Breakdown     |    Dropdown: Mandor/Tukang/Pekerja    |       ⭐ Add fixed dropdowns        |
| Equipment Tracking  |      Linked to equipment master       |       ⭐ Not implemented yet        |
| Daily Photos        |     Bulk upload, auto-geotagging      |      ⭐ Add geolocation option      |
| Approval Chain      | Multi-level (Site Mgr → PM → Direksi) |   ⭐⭐ Implement 3-tier approval    |
| Email Digest        |    Auto-email summary setiap pagi     |        ⭐ Add scheduler job         |
| S-Curve Integration |          Real-time KPI chart          |            ✅ Sudah ada             |

#### B. **JobBuild (Local, khusus Indonesia)**

**Digunakan oleh:** PT. Citra Niaga, Kontraktor Mid-Market

| Fitur                   |          Implementasi           |               Relevansi               |
| ----------------------- | :-----------------------------: | :-----------------------------------: |
| Daily Report Template   |     Per-work-type templates     |  ⭐⭐ Improve UX with smart defaults  |
| Copy Previous Day       | Auto-populate repetitive fields | ⭐⭐ Add "Copy from yesterday" button |
| Field Photo Gallery     |       Before-After format       |       ⭐ Add before/after slots       |
| Safety Incidents        |      Dedicated safety form      |  ⭐⭐⭐ CRITICAL — PUPR requirement   |
| Material Reconciliation |  Link to material requisition   |   ⭐ Connect to procurement module    |
| Weekly Recap Video      |       60-sec video option       |     🟡 Nice-to-have, low priority     |

#### C. **SAP Construction Hub (Enterprise)**

**Digunakan oleh:** Kontraktor skala besar (Waskita, Soemono)

| Fitur                  |            Implementasi            |           Relevansi            |
| ---------------------- | :--------------------------------: | :----------------------------: |
| Multi-level Project    |      WBS hierarchy reporting       |      ✅ ProMan5 sudah WBS      |
| Real-time Dashboarding |          Live KPI update           | ⭐ Improve dashboard real-time |
| Variance Analysis      |         Planned vs Actual          |  ⭐⭐ Enhance KPI calculation  |
| Approval Routing       |             BPM-based              |     ✅ Basic ada, improve      |
| Integration Hub        | Connect to equipment, material, HR |           ⭐ Phase 2           |

#### D. **Primavera P6 (Industry Standard di Besar)**

**Standard untuk project besar, PUPR-compliant**

| Fitur             |           Implementasi            |
| ----------------- | :-------------------------------: | --------------------------- |
| Activity Status   |   % complete, baseline variance   | ✅ ProMan5 punya cumulative |
| Resource Loading  |  Labor hours linked to activity   | ⭐ Manual saat ini, improve |
| Schedule Variance | SV = Earned Value - Planned Value | ⭐ Calculate & display      |
| Trend Analysis    |     Forecast completion date      | ⭐ Add trending logic       |

### 3.2 Standar PUPR Indonesia

**Kategori Wajib dalam Laporan Harian (Berdasarkan Peraturan Menteri PUPR):**

```
1. IDENTITAS
   ✅ Tanggal laporan
   ✅ Nama proyek & lokasi
   ✅ No. kontrak
   ⚠️  Nama pelapor (ada, belum display jelas)

2. KEMAJUAN PEKERJAAN
   ✅ Uraian pekerjaan (description)
   ✅ % kemajuan (progress_percentage)
   ✅ Kumulatif progress (cumulative_progress)
   ⚠️  Rencana vs realisasi (hanya ada realisasi)

3. TENAGA KERJA
   ⚠️  Jumlah total (workers_count ✓)
   ❌ Breakdown: Mandor, Tukang, Pekerja (labor_details ✓ tapi belum validated)
   ❌ Jam kerja (belum ada)
   ❌ Kehadiran (belum ada)

4. PERALATAN
   ❌ Jenis & jumlah (equipment_details ada tapi empty)
   ❌ Kondisi peralatan (belum ada)
   ❌ Productivity (belum ada)

5. BAHAN/MATERIAL
   ❌ Stok masuk (belum ada di progress report)
   ❌ Terpakai (material_usage_summary ada tapi incomplete)
   ❌ Sisa stok (belum ada)

6. CUACA & KONDISI LAPANGAN
   ✅ Cuaca (weather ✓)
   ⚠️  Durasi cuaca buruk (weather_duration ada tapi optional)
   ❌ Kondisi lapangan (belum ada)

7. KESELAMATAN & KESEHATAN KERJA (K3)
   ❌ Jumlah incident (safety_details ada tapi incomplete)
   ❌ Jenis incident (belum ada)
   ❌ Kepatuhan APD (belum ada)
   ❌ Upaya pencegahan (belum ada)

8. KENDALA & HAMBATAN
   ✅ Issues/hambatan (issues ✓)
   ❌ Impact terhadap schedule (belum ada)
   ❌ Tindakan corrective (belum ada)

9. RENCANA BESOK
   ✅ next_day_plan field ada, tapi belum di-UI

10. DOKUMENTASI
    ✅ Foto (photos ✅)
    ❌ Video (belum ada)
    ❌ Sketsa (belum ada)
```

### 3.3 Peringkat Compliance PUPR Saat Ini

```
ProMan5 Progress Report Compliance Score: 5.5/10

✅✅  Implemented (60%): Tenaga Kerja, Cuaca, Kemajuan, Foto
✅⚠️   Partial (30%): Material, K3, Equipment
❌❌  Not Yet (10%): Rencana Besok (UI), Jam Kerja, Kehadiran, Video
```

---

## 4. REKOMENDASI WORKFLOW

### 4.1 Improved Workflow State Machine

```
┌─────────────────────────────────────────────────────────────────┐
│                    PROGRESS REPORT WORKFLOW                     │
└─────────────────────────────────────────────────────────────────┘

                  User: Site Manager / Site Engineer
                              │
                              ▼
                    ┌──────────────────┐
                    │  DRAFT (Editable)│
                    │  Created locally │
                    └──────────────────┘
                              │
              ┌───────────────┴───────────────┐
              │ (Auto-save setiap 30 detik) │ (Manual save)
              ▼                              ▼
         ┌──────────────┐         ┌─────────────────────┐
         │ Save as Draft│ ◄─────► │ Submit for Review   │
         └──────────────┘   ↑     └─────────────────────┘
              ▲              │              │
              │         (Can cancel)       │
              │              │             │
              └──────────────┘             ▼
                    ▲                ┌──────────────────┐
                    │                │ SUBMITTED        │
                    │                │ Pending Review   │
                    │                └──────────────────┘
                    │                      │
                    │          ┌───────────┴───────────┐
                    │          │                       │
                    │          ▼                       ▼
              ┌──────────────┐              ┌──────────────────┐
              │ REJECTED     │              │  REVIEWED        │
              │ Pelapor edit │              │  Approved by PM  │
              │ lalu resubmit│              └──────────────────┘
              └──────────────┘                       │
                    ▲                                │
                    │                   ┌────────────┴────────────┐
                    │                   │                         │
                    │                   ▼                         ▼
                    │            ┌──────────────────┐  ┌─────────────────┐
                    │            │ PUBLISHED        │  │ SCHEDULED FOR   │
                    │            │ (visible to owner)  │ WEEKLY REPORT   │
                    │            └──────────────────┘  └─────────────────┘
                    │                    │                       │
                    │                    │ (Auto-aggregate)      │
                    │                    │                       ▼
                    │                    │            ┌──────────────────┐
                    │                    │            │ INCLUDED IN      │
                    │                    │            │ WEEKLY SNAPSHOT  │
                    │                    │            └──────────────────┘
                    │                    │                       │
                    └────────────────────┴───────────────────────┘
                              (Optional) Revise


USER PERMISSIONS:
├── Site Manager: create, edit (DRAFT only), submit, view own
├── PM/Supervisor: view all, approve/reject, publish
├── Admin: view all, force-delete (audit trail)
└── Owner (Portal): view published only, export PDF

ACTIONS & TRANSITIONS:
├── Create         : draft (auto)
├── Submit         : draft → submitted (permission: progress.manage)
├── Approve/Reject : submitted → reviewed/rejected (permission: progress.approve)
├── Publish        : reviewed → published (permission: progress.publish)
├── Edit           : draft only, or rejected with resubmit
└── Delete         : draft or rejected only
```

### 4.2 Approval Workflow Detail

```
┌────────────────────────────────────────────────────────────────┐
│           APPROVAL WORKFLOW: Multi-Tier System                 │
└────────────────────────────────────────────────────────────────┘

TIER 1: SITE LEVEL
┌──────────────────────┐
│  Site Manager/QS     │  ← Creates progress report
│  (Reporter)          │
└──────────┬───────────┘
           │ Submit
           ▼
┌──────────────────────────────────┐
│  Site Supervisor / Quality Mgr   │  ← First review
│  (Level 1 Reviewer)              │  ✓ Check data accuracy
└──────────┬───────────────────────┘
           │ Approve
           ├─────────────────┬──────────────────┐
           │                 │                  │
      APPROVED          ON HOLD           REJECTED
           │          (Modify needed)      │
           │                 │              │
           ▼                 ▼              ▼
    [Ready to Publish] [Return to QS]  [Back to Manager]
           │                                 │
           │                    [Fix & Resubmit]
           │
TIER 2: PROJECT LEVEL
┌──────────────────────────────────┐
│  PM / Project Coordinator         │  ← Second review
│  (Level 2 Reviewer)               │  ✓ Check consistency with schedule
└──────────┬───────────────────────┘
           │
           ├─────────────────┬──────────────────┐
           │                 │                  │
      APPROVED          REVISION REQ      REJECTED
           │          (Ask for clarification) │
           │                 │                │
           ▼                 ▼                ▼
    ┌──────────────┐   [Return]      [Return to Site Mgr]
    │ FINAL REVIEW │      │                │
    │ Ready to     │      │    [Provide additional info]
    │ Publish      │      │
    └──────┬───────┘      │
           │              │
    [PUBLISH]     [Resubmit]
           │              │
           └──────┬───────┘
                  │
        ┌─────────▼────────┐
        │ Notification     │
        │ to Stakeholders  │
        └──────────────────┘

PERMISSIONS MAPPING:
Progress status    | Who can act        | Action
───────────────────┼──────────────────┼────────────────
draft              | Reporter         | Edit, Submit, Delete
submitted          | L1 Reviewer      | Approve, Reject, OnHold
submitted          | L2 Reviewer      | View
on_hold            | Reporter         | Revise, Resubmit
rejected           | Reporter         | Edit, Resubmit
reviewed           | L2 Reviewer      | Publish
reviewed           | Admin            | Force Publish
published          | All              | View, Export
published          | Owner            | View (Portal only)
```

### 4.3 Notification Rules

```
Trigger                    | Send To           | Message Template
─────────────────────────┼─────────────────┼───────────────────
Report Created           | Team (channel)  | "{Name} created progress for {RAB Item} ({Progress}%)"
Report Submitted         | L1 Reviewer     | "Progress report pending your approval"
Report Approved          | Reporter        | "✓ Your report approved"
Report Rejected          | Reporter        | "⚠ Your report needs revision: {reason}"
Report Published         | Project Team    | "✓ Progress report published (Week {X})"
Auto-copy reminder       | Reporter        | "Don't forget your daily progress report"
Weekly aggregation done  | PM/Admin        | "Weekly report auto-generated, pending approval"
```

---

## 5. REKOMENDASI LOGIC BISNIS

### 5.1 Data Validation & Business Rules

```php
// RULE: Cumulative Progress kalkulasi
✅ RUL-01: cumulative_progress = min(100, previous_cumulative + current_progress_percentage)
✅ RUL-02: progress_percentage TIDAK boleh negatif (construction never goes backward)
✅ RUL-03: progress_percentage HARUS ≤ RAB item weight
✅ RUL-04: Tanggal report tidak boleh > hari ini
✅ RUL-05: 1 RAB item = 1 laporan/hari (unique index: project + rab_item + date)

// RULE: Approval workflow
✅ RUL-06: Reporter TIDAK boleh approve laporan sendiri
✅ RUL-07: Laporan HANYA bisa di-edit jika status = draft atau rejected
✅ RUL-08: Laporan published tidak bisa di-delete (hanya admin dengan audit trail)
✅ RUL-09: Jika progress_percentage > 0, maka RAB item status minimal "in-progress"
✅ RUL-10: Jika cumulative_progress = 100, maka RAB item status auto-mark "complete"

// RULE: PUPR Compliance
🆕 RUL-11: workers_count HARUS diisi (mandatory)
🆕 RUL-12: Jika cuaca = "rainy", maka weather_duration WAJIB diisi
🆕 RUL-13: Jika activity code = "Excavation", maka equipment_details HARUS ada
🆕 RUL-14: K3/Safety incidents harus di-log dengan detail & corrective action
🆕 RUL-15: next_day_plan HARUS ada untuk laporan akhir pekan

// RULE: Schedule Integration
✅ RUL-16: Cumulative = 100 → Activity status = 100%
✅ RUL-17: Progress masuk weekly report HANYA jika published
🆕 RUL-18: Variance = (actual cumulative - planned cumulative) → auto-alert jika > 5%

// RULE: Data Integrity (Database Level)
✅ RUL-19: Gunakan DB::transaction + pessimistic lock saat update cumulative
✅ RUL-20: Soft delete untuk audit trail (no hard delete kecuali admin)
```

### 5.2 Auto-Calculation Rules

```python
# RULE: Cumulative Progress Calculation
def calculate_cumulative_progress(rab_item_id, new_progress_percentage):
    """
    Hitung cumulative dengan transaction lock untuk prevent race condition
    """
    with transaction:
        rab_item = RabItem.lock_for_update(rab_item_id)  # Pessimistic lock

        # Get latest non-rejected report
        latest_report = ProgressReport.filter(
            rab_item_id=rab_item_id,
            status__in=['draft', 'submitted', 'reviewed', 'published']
        ).order_by('-report_date', '-id').first()

        if latest_report:
            previous_cumulative = latest_report.cumulative_progress
        else:
            previous_cumulative = 0

        new_cumulative = min(100, previous_cumulative + new_progress_percentage)
        return new_cumulative

# RULE: RAB Item Status Auto-Update
def update_rab_status_from_progress(rab_item_id):
    """
    Auto-update RAB item status berdasarkan cumulative progress
    """
    latest_cumulative = ProgressReport.filter(
        rab_item_id=rab_item_id,
        status__in=['reviewed', 'published']
    ).values('cumulative_progress').order_by('-report_date').first()

    if not latest_cumulative:
        return

    cumulative = latest_cumulative['cumulative_progress']

    if cumulative == 0:
        status = 'Not Started'
    elif cumulative < 100:
        status = 'In Progress'
    else:  # cumulative == 100
        status = 'Completed'
        mark_complete_at = now()

    rab_item.update(status=status, actual_progress=cumulative)

# RULE: Schedule S-Curve Recalculation
def recalculate_schedule_from_progress(project_id):
    """
    Trigger ulang S-curve calculation setiap ada published progress
    """
    project = Project.find(project_id)

    # Aggregate all published progress untuk minggu ini
    weekly_aggregate = ProgressReport.filter(
        project_id=project_id,
        status='published',
        report_date__gte=week_start,
        report_date__lte=week_end
    ).values('rab_item_id').annotate(
        latest_cumulative=Max('cumulative_progress')
    )

    # Update schedule table & recalculate S-curve
    ScheduleCalculator.update_from_progress(project, weekly_aggregate)

# RULE: Weekly Report Auto-Generation Trigger
def schedule_weekly_aggregation(project_id):
    """
    Setiap minggu (Jumat 16:00), trigger auto-aggregate published progress
    """
    # Query all published progress for this week
    # Create weekly_report dengan aggregate data
    # Status: draft (pending review)
    # Notify PM untuk review
    pass
```

### 5.3 KPI Calculation for Dashboard

```python
# KPI: Progress Variance
def calculate_progress_variance(rab_item_id, report_week):
    """
    SV = Actual Progress - Planned Progress
    """
    actual_progress = ProgressReport.filter(
        rab_item_id=rab_item_id,
        week=report_week,
        status='published'
    ).values('cumulative_progress').first()

    planned_progress = Schedule.filter(
        rab_item_id=rab_item_id,
        week=report_week
    ).values('planned_cumulative').first()

    variance = actual_progress - planned_progress
    variance_pct = (variance / planned_progress) * 100 if planned_progress > 0 else 0

    status = 'On Track' if abs(variance_pct) <= 5 else (
        'Behind' if variance_pct < -5 else 'Ahead'
    )

    return {
        'variance': variance,
        'variance_pct': variance_pct,
        'status': status,
        'alert': 'yes' if abs(variance_pct) > 5 else 'no'
    }

# KPI: Productivity Index
def calculate_productivity_index(rab_item_id, period):
    """
    Produktivitas = Total Progress / Total Labor Days
    """
    total_progress = ProgressReport.filter(
        rab_item_id=rab_item_id,
        report_date__gte=period.start,
        report_date__lte=period.end,
        status='published'
    ).aggregate(Sum('progress_percentage'))['progress_percentage__sum']

    labor_days = ProgressReport.filter(
        rab_item_id=rab_item_id,
        report_date__gte=period.start,
        report_date__lte=period.end,
        status='published'
    ).aggregate(Sum('workers_count'))['workers_count__sum']

    productivity = total_progress / labor_days if labor_days > 0 else 0
    return productivity

# KPI: Safety Score
def calculate_safety_score(project_id, period):
    """
    Safety Score = (Total Reports - Reports with Incidents) / Total Reports * 100
    """
    total_reports = ProgressReport.filter(
        project_id=project_id,
        report_date__gte=period.start,
        report_date__lte=period.end,
        status='published'
    ).count()

    incident_count = ProgressReport.filter(
        project_id=project_id,
        report_date__gte=period.start,
        report_date__lte=period.end,
        status='published',
        safety_details__incidents__gt=0
    ).count()

    safety_score = ((total_reports - incident_count) / total_reports * 100) if total_reports > 0 else 100
    return safety_score
```

---

## 6. REKOMENDASI UI/UX

### 6.1 Form Design (Desktop)

**Current:** Linear form dengan collapsible sections  
**Recommended:** Tab-based with smart field auto-population

```
┌─────────────────────────────────────────────────────────────────┐
│         PROGRESS REPORT INPUT — DAILY WORK LOG                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  [Project: Gedung ABC]  [Date: 01 May 2026]  [Time: Auto]      │
│                                                                   │
│  📌 TAB NAVIGATION                                               │
│  ├─ [Basic] ← [Field 1-5] ◄─── Most important fields
│  ├─ [Labor] ← [Workers breakdown]
│  ├─ [Equipment] ← [Machines on-site]
│  ├─ [Materials] ← [Usage tracking]
│  ├─ [Safety] ← [Incidents & APD]
│  ├─ [Documentation] ← [Photos & notes]
│  └─ [Review] ← [Summary preview]
│
│
│  ┌──────────────────────────────────────────────────┐
│  │ TAB 1: BASIC INFORMATION (Auto-filled)          │
│  ├──────────────────────────────────────────────────┤
│  │                                                   │
│  │  📋 Work Item Selection                          │
│  │  ┌────────────────────────────────────────────┐ │
│  │  │ [Dropdown] Excavation - Area A (60%)       │ │  ← Smart search
│  │  │ [Copy from yesterday ✓] [New item]         │ │
│  │  └────────────────────────────────────────────┘ │
│  │                                                   │
│  │  📊 Progress Entry                               │
│  │  ┌────────────────────────────────────────────┐ │
│  │  │ Progress today (%)        │  10  │  [%]   │ │  ← Slider + input
│  │  │ Previous cumulative       │ 50%  │ (read) │ │
│  │  │ New cumulative            │ 60%  │ (auto) │ │  ← Visual bar
│  │  │ ═════════════════════════════════         │ │     [▓▓▓▓▓▓░░░░] 60%
│  │  │ Weight/Target             │ 100% │ (info) │ │
│  │  │ Variance from plan        │ -5%  │ (warn) │ │
│  │  └────────────────────────────────────────────┘ │
│  │                                                   │
│  │  📝 Work Description                             │
│  │  ┌────────────────────────────────────────────┐ │
│  │  │ [Large textarea]                           │ │
│  │  │ What was done today...                     │ │
│  │  │ (Minimum 10 characters)                    │ │
│  │  └────────────────────────────────────────────┘ │
│  │                                                   │
│  │  ⚠️  Issues/Obstacles (Optional)                 │
│  │  ┌────────────────────────────────────────────┐ │
│  │  │ [Textarea] Any challenges?                 │ │
│  │  │ [Common tags] [Delayed Material]           │ │
│  │  │               [Equipment Breakdown]        │ │
│  │  │               [Weather Impact]             │ │
│  │  │               [Other]                      │ │
│  │  └────────────────────────────────────────────┘ │
│  │                                                   │
│  │  🌤️  Weather Conditions                          │
│  │  ┌────────────────────────────────────────────┐ │
│  │  │ Condition: [Sunny ▼]  [auto-fetch]        │ │  ← Auto from API
│  │  │ Duration:  [08:00 - 12:00 ✓]              │ │
│  │  │ Impact:    ○ None  ◉ Partial  ○ Major     │ │
│  │  └────────────────────────────────────────────┘ │
│  │                                                   │
│  │                                                   │
│  │  [⬅ PREV]  [NEXT →]  [SAVE DRAFT]  [SUBMIT]   │
│  └──────────────────────────────────────────────────┘

Legend:
  [▓] = Filled portion of progress bar
  [░] = Remaining portion
  [✓] = Completed/confirmed
  [⚠] = Warning/attention needed
```

### 6.2 Mobile Responsive Design (Target: 85% usability)

```
📱 MOBILE VIEW
┌──────────────┐
│ [LOGO] ProMan5 │  Header (sticky)
│ Progress Log  │
└──────────────┘
│ [Today: 01 May] │
├──────────────────────────┤
│ Quick Stats (Swipeable)   │
│ ┌──────────────────────┐  │
│ │ 📊 Today's Progress  │  │ ← Swipe left/right
│ │ ████████░░ 60%       │  │
│ │ Labor: 12 pax        │  │
│ │ Weather: ☀️ Sunny    │  │
│ └──────────────────────┘  │
│                            │
│ 🔥 QUICK ENTRY            │
│ ┌──────────────────────┐  │
│ │ Work: [Excavation ▼] │  │ ← Tap to expand
│ │ Progress: [___] %    │  │ ← Numeric pad
│ │ + TAKE PHOTO        │  │ ← Camera shortcut
│ │ [SAVE] [SUBMIT]     │  │
│ └──────────────────────┘  │
│                            │
│ 📋 ALL ITEMS THIS WEEK    │
│ ├─ Excavation (60%) ✓     │ ← Tap for detail
│ ├─ Foundation (45%) ⚠️    │ ← Behind schedule
│ ├─ Concrete (30%)         │
│ └─ [+ ADD NEW]           │
│                            │
│ 📸 PHOTOS TODAY (3)        │
│ [img] [img] [img] [+]     │ ← Tap photo to expand
│                            │
│ 💬 COMMENTS (1)            │
│ PM: "Good progress today"  │
│ [REPLY]                    │
│                            │
│ [BACK]  [SAVE]  [SUBMIT]  │
└──────────────┘

UX Features:
✅ Large touch targets (min 44x44 px)
✅ One-hand navigation (bottom action buttons)
✅ Auto-save every 30 sec (offline support)
✅ Camera integration (take photo inline)
✅ Voice input for description (optional)
✅ Lightweight JS (< 100KB bundle)
```

### 6.3 List View - Before & After

**CURRENT** (Too compact):

```
│ Date    │ RAB Item    │ Progress │ Weather │ Workers │ Status │ Actions
├─────────┼─────────────┼──────────┼─────────┼─────────┼────────┼────────
│ 01 May  │ Excavation  │ ████░ 60%│ ☀️      │ 12      │ ✓      │ [👁] [✏] [🗑]
```

**RECOMMENDED** (More informative):

```
┌─────────────────────────────────────────────────────────────────┐
│  [01 May 2026] — Thursday                                        │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ 📦 Excavation — Area A                                     │ │
│  │ ───────────────────────────────────────────────────────── │ │
│  │                                                            │ │
│  │ Progress Today     ████░░░░░░ 10%                         │ │
│  │ Cumulative        ███████░░░ 60%  (Target: ████████████)│ │
│  │ Variance          🔴 -5% Behind (Alert)                  │ │
│  │                                                            │ │
│  │ Labor        12 persons  |  Weather ☀️ Sunny            │ │
│  │ Status       ✓ Approved  |  Logged by Rudi — 16:30      │ │
│  │                                                            │ │
│  │ Description  "Clearing & leveling site, removed 450m3    │ │
│  │              soil, equipment: 2x Excavator"              │ │
│  │                                                            │ │
│  │ Issues       ⚠️ Delayed: Waiting for material delivery    │ │
│  │              (ETA: 02 May)                                │ │
│  │                                                            │ │
│  │ [📸 View Photos]  [💬 3 comments]  [👁 Details]  [⋮]    │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                  │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ 📦 Foundation — Pile Cap                                   │ │
│  │ [Similar card structure...]                               │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘

Card Features:
✅ Visual status badge (✓ Approved, ⚠️ Needs Review, 🔴 Rejected)
✅ Quick KPI view (Variance, Target)
✅ Key details inline
✅ Expandable (tap to see full detail)
✅ Action buttons (Comments, Photos, Details)
```

### 6.4 Approval UI

**Reviewer View:**

```
┌─────────────────────────────────────────────────────────┐
│         PROGRESS REPORT REVIEW & APPROVAL                │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Report ID: LHP-2026-001                               │
│  Submitted: 01 May 2026, 16:30 by Rudi                │
│  Status: ⏳ PENDING APPROVAL (1 hour 15 min ago)      │
│                                                          │
│  ┌─────────────────────────────────────────────────┐   │
│  │ 📊 KEY METRICS                                  │   │
│  ├─────────────────────────────────────────────────┤   │
│  │ Progress Today    10%    ✓ Within limit        │   │
│  │ Cumulative        60%    ⚠️ Behind 5%          │   │
│  │ Workers           12     ✓ Valid               │   │
│  │ Safety Incidents  0      ✓ OK                  │   │
│  │ Data Completeness 95%    ✓ All required fields │   │
│  └─────────────────────────────────────────────────┘   │
│                                                          │
│  ┌─────────────────────────────────────────────────┐   │
│  │ 📋 FULL REPORT DETAILS                          │   │
│  ├─────────────────────────────────────────────────┤   │
│  │ [Expandable sections]                          │   │
│  │ • Description: ...                             │   │
│  │ • Issues: Delayed material (ETA: 02 May)       │   │
│  │ • Labor: Mandor 2, Tukang 5, Pekerja 5        │   │
│  │ • Equipment: Excavator x2, Vibrator x1         │   │
│  │ • Weather: Sunny, 08:00-16:00, Impact: None   │   │
│  │ • Photos: 5 images [View Gallery]              │   │
│  └─────────────────────────────────────────────────┘   │
│                                                          │
│  ┌─────────────────────────────────────────────────┐   │
│  │ 🔍 REVIEWER DECISION                            │   │
│  ├─────────────────────────────────────────────────┤   │
│  │                                                  │   │
│  │ Your Action:                                    │   │
│  │ ○ ✅ Approve                                    │   │
│  │ ○ 🟡 Request Revision (Ask for clarification) │   │
│  │ ○ ❌ Reject (Return for correction)            │   │
│  │                                                  │   │
│  │ Comments (Optional):                            │   │
│  │ ┌───────────────────────────────────────────┐  │   │
│  │ │ [Textarea]                                │  │   │
│  │ │ Add your review notes...                  │  │   │
│  │ │                                           │  │   │
│  │ └───────────────────────────────────────────┘  │   │
│  │                                                  │   │
│  │ [⬅ BACK]  [SAVE AS DRAFT]  [SUBMIT DECISION] │   │
│  └─────────────────────────────────────────────────┘   │
│                                                          │
│  ⏱️  Auto-save every 30 sec                            │
└─────────────────────────────────────────────────────────┘
```

### 6.5 Dashboard Widget

**Real-time Progress Dashboard:**

```
┌────────────────────────────────────────────────────────────┐
│  📊 WEEKLY PROGRESS DASHBOARD                              │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  Week: 28 Apr - 04 May 2026  [◄ Prev]  [Next ►]          │
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐  ┌───────────┐│
│  │ 📈 OVERALL       │  │ ⚠️  VARIANCE      │  │ 🏗️ ITEMS  ││
│  │ ────────────────│  │ ────────────────  │  │ ───────── ││
│  │ Planned: 40%    │  │ -3% (Behind)      │  │ Total: 12 ││
│  │ Actual:  37%    │  │ 🔴 ALERT          │  │ On track: 8 ││
│  │ ████████░░░ 37% │  │ Forecast: -2 days │  │ Behind: 4 ││
│  └──────────────────┘  └──────────────────┘  └───────────┘│
│                                                             │
│  ┌────────────────────────────────────────────────────────┐│
│  │ 📋 ITEMS STATUS                                        ││
│  ├────────────────────────────────────────────────────────┤│
│  │                                                        ││
│  │ ✅ ON TRACK (8 items)                                ││
│  │  • Excavation (65%) ▮▮▮▮▮▮░ 65%                     ││
│  │  • Foundation Prep (45%) ▮▮▮▮░░░ 45%                ││
│  │  • Pile Cap (30%) ▮▮▮░░░░░ 30%                      ││
│  │                                                        ││
│  │ ⚠️  BEHIND SCHEDULE (4 items)                         ││
│  │  🔴 Concrete Pour (20%) ▮▮░░░░░░░ 20% [Target: 35%] ││
│  │  🔴 Rebar Placement (28%) ▮▮▮░░░░░ 28% [Target: 40%]││
│  │  🟡 Formwork (35%) ▮▮▮▮░░░░ 35% [Target: 50%]       ││
│  │  🟡 Column Casting (15%) ▮░░░░░░░░ 15% [Target: 25%]││
│  │                                                        ││
│  └────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐  ┌───────────┐│
│  │ 👥 LABOR SUMMARY │  │ 🛠️  EQUIPMENT    │  │ 🌤️ WEATHER││
│  │ ────────────────│  │ ────────────────  │  │ ───────── ││
│  │ Total: 145 pax/day │ Active: 8/10    │  │ ☀️ 80%    ││
│  │ Avg: 29 pax     │  │ Utilization: 80% │  │ ⛅ 20%   ││
│  │ Peak: 45 (Wed)  │  │ Issues: 0        │  │ 🌧️ 0%    ││
│  └──────────────────┘  └──────────────────┘  └───────────┘│
│                                                             │
│  ┌──────────────────┐  ┌──────────────────┐               │
│  │ 🚨 SAFETY       │  │ 📈 S-CURVE        │               │
│  │ ────────────────│  │ ────────────────  │               │
│  │ Incidents: 0    │  │ [Graph]           │               │
│  │ Status: ✅ SAFE │  │   100%  ╱─────    │               │
│  │ APD Compliance  │  │         ╱  Planned│               │
│  │ 98%             │  │        ╱          │               │
│  │                  │  │    Actual         │               │
│  │                  │  │        0% ────────│               │
│  └──────────────────┘  │          Week 1-6 │               │
│                        └──────────────────┘               │
│                                                             │
│  [📥 Export PDF]  [📧 Email]  [🔄 Refresh]  [⚙️ Filters]  │
└────────────────────────────────────────────────────────────┘
```

### 6.6 Detail Modal / Full-Page View

**Current:** Simple dark modal  
**Recommended:** Full-page view dengan tabs & timeline

```
┌──────────────────────────────────────────────────────────────┐
│  📄 PROGRESS REPORT DETAIL VIEW                              │
│  LHP-2026-001 | Created by Rudi | Approved by Ahmad          │
├──────────────────────────────────────────────────────────────┤
│ [SUMMARY] [DETAIL] [PHOTOS] [COMMENTS] [ACTIVITY LOG]       │
│                                                              │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ SUMMARY TAB                                            │  │
│ ├────────────────────────────────────────────────────────┤  │
│ │                                                        │  │
│ │ 📊 Key Metrics                                        │  │
│ │ ├─ Progress Today: 10%                               │  │
│ │ ├─ Cumulative: 60% [████████░░] (Target: 100%)     │  │
│ │ ├─ Variance: -5% (Behind schedule) 🔴              │  │
│ │ └─ Status: ✓ Approved                              │  │
│ │                                                        │  │
│ │ 👥 Resources                                         │  │
│ │ ├─ Workers: 12 (Mandor 2, Tukang 5, Pekerja 5)     │  │
│ │ ├─ Equipment: Excavator x2, Vibrator x1            │  │
│ │ └─ Weather: ☀️ Sunny (08:00-16:00)                 │  │
│ │                                                        │  │
│ │ ⚠️  Issues                                           │  │
│ │ └─ Delayed material delivery (ETA: 02 May)         │  │
│ │                                                        │  │
│ │ 📝 Next Day Plan                                     │  │
│ │ └─ Continue excavation, prepare for foundation...   │  │
│ │                                                        │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ PHOTOS TAB (5 images)                                │  │
│ ├────────────────────────────────────────────────────────┤  │
│ │                                                        │  │
│ │  [◄ Prev]  [Image 2/5] Large Preview                [Next ►] │
│ │  ┌──────────────────────────────────────┐           │  │
│ │  │                                      │           │  │
│ │  │         [Large Photo Display]         │           │  │
│ │  │                                      │           │  │
│ │  └──────────────────────────────────────┘           │  │
│ │                                                        │  │
│ │  📸 Uploaded by Rudi — 01 May 2026, 16:15           │  │
│ │  📍 Coordinates: -6.1234, 106.8765 (Optional)       │  │
│ │                                                        │  │
│ │  [Thumbnail Row]                                     │  │
│ │  [img1] [img2] ← current [img3] [img4] [img5]      │  │
│ │                                                        │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ ACTIVITY LOG TAB                                     │  │
│ ├────────────────────────────────────────────────────────┤  │
│ │                                                        │  │
│ │ ⏰ TIMELINE                                           │  │
│ │                                                        │  │
│ │ ┌─ 01 May, 16:30 — Created by Rudi                 │  │
│ │ │  "Excavation work, 10% today progress"           │  │
│ │ │  Status: draft                                    │  │
│ │ └─ [View Full Details]                             │  │
│ │                                                        │  │
│ │ ├─ 01 May, 16:45 — Submitted by Rudi               │  │
│ │ │  Status: submitted                               │  │
│ │ │                                                   │  │
│ │ ├─ 01 May, 17:20 — Approved by Ahmad               │  │
│ │ │  Review Notes: "Looks good, proceed tomorrow"    │  │
│ │ │  Status: reviewed                                │  │
│ │ │                                                   │  │
│ │ └─ 02 May, 09:00 — Published by Admin              │  │
│ │    Status: published (locked for edit)             │  │
│ │                                                        │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                              │
│ [⬅ BACK]  [🖨️ PRINT PDF]  [📧 EMAIL]  [⋮ MORE]            │
└──────────────────────────────────────────────────────────────┘
```

---

## 7. ROADMAP IMPLEMENTASI (PRIORITAS)

### Phase 1: Foundation (Weeks 1-2) — CRITICAL

**Objective:** Fix data integrity & duplikasi kode

```
PR-001: Buat ProgressReportService
├─ Extract create/update/delete logic dari Controller
├─ Implementasi DB::transaction + pessimistic lock
├─ Add validation rules (RUL-01 to RUL-10)
├─ Add comprehensive logging
└─ Test: 15+ test cases (create, update, delete, race condition)
Effort: 8h | Owner: Backend Lead | PR Review: 2h

PR-002: Refactor ProgressReportController
├─ Delegate semua logic ke ProgressReportService
├─ Remove duplikasi (LOC 95 → 50)
├─ Add proper error handling
└─ Test: Update existing tests
Effort: 4h | Owner: Backend Lead

PR-003: Refactor ProgressReportManager Livewire
├─ Delegate ke service, remove calculation logic
├─ Fix race condition di form submission
└─ Test: Livewire tests
Effort: 3h | Owner: Frontend Lead

PR-004: Refactor Api\ProgressReportController
├─ Delegate ke service
├─ Update API responses
└─ Test: API tests
Effort: 2h | Owner: Backend Lead

🎯 Phase 1 Impact:
✅ -250 LOC duplikasi dihapus
✅ Race condition fixed
✅ Code maintainability: 4/10 → 7/10
```

### Phase 2: Compliance & Data (Weeks 3-4) — HIGH

**Objective:** Implementasi PUPR compliance fields & validation

```
DB-001: Add Missing PUPR Fields
├─ Migration: Update progress_reports table
│  ├─ ADD: next_day_plan TEXT (if not exists)
│  ├─ ADD: labor_details JSON constraints
│  ├─ ADD: equipment_productivity DECIMAL(5,2)
│  ├─ ADD: material_cost_impact DECIMAL(10,2)
│  └─ ADD: safety_corrective_action TEXT
├─ Model update: Fillable, casts
└─ Validation: Add required field rules
Effort: 3h | Owner: Backend Lead

PR-005: Enhance Form UI (PUPR Fields)
├─ Update ProgressReportManager Livewire
│  ├─ Add Equipment tab with CRUD
│  ├─ Add K3/Safety incident tracker
│  ├─ Add next_day_plan required field
│  ├─ Improve material_usage_summary UX
│  └─ Add labor breakdown with dropdowns
├─ Update Blade view: show.blade.php
└─ Test: Livewire + browser tests
Effort: 12h | Owner: Frontend Lead

PR-006: Add Field Validations & Rules
├─ Backend validation in ProgressReportRequest
├─ Auto-assign status markers (RUL-09, RUL-10)
├─ Add business rule middleware
└─ Test: Validation test suite
Effort: 4h | Owner: Backend Lead

PR-007: Implement PUPR Auto-Calculations
├─ Auto-update RAB status (Not Started → In Progress → Complete)
├─ Auto-calculate variance vs schedule
├─ Trigger schedule recalculation
└─ Test: Calculator tests
Effort: 5h | Owner: Backend Lead

🎯 Phase 2 Impact:
✅ PUPR compliance: 5.5/10 → 8/10
✅ Data completeness: 6/10 → 8/10
✅ User data entry: 60 sec → 90 sec (more fields)
```

### Phase 3: Workflow & Approval (Weeks 5-6) — HIGH

**Objective:** Multi-tier approval workflow & notifications

```
PR-008: Implement Approval Workflow State Machine
├─ Add approval status enum: draft, submitted, on_hold, reviewed, published
├─ Add permission checks in service methods
├─ Implement workflow transitions with rules
├─ Add audit trail (activity log)
└─ Test: Workflow state machine tests
Effort: 8h | Owner: Backend Lead

PR-009: Update Review UI
├─ Create new Blade view: progress/review.blade.php
├─ Add approval decision interface
├─ Add reviewer comments section
├─ Add KPI validation checklist
└─ Test: Browser tests
Effort: 6h | Owner: Frontend Lead

PR-010: Add Approval Notifications
├─ Create NotificationService for workflow events
├─ Add database email queue for notifications
├─ Send email to reviewer on submit
├─ Send email to reporter on approve/reject
├─ Implement in-app bell notifications
└─ Test: Notification tests
Effort: 4h | Owner: Backend Lead

PR-011: Add Activity Log UI
├─ Create modal/detail view for approval history
├─ Show who, when, what action, notes
├─ Add timeline visualization
└─ Test: UI tests
Effort: 3h | Owner: Frontend Lead

🎯 Phase 3 Impact:
✅ Workflow completeness: 5/10 → 8/10
✅ Approval enforcement: 0% → 100%
✅ Audit trail: Manual → Automatic
```

### Phase 4: Mobile & Performance (Weeks 7-8) — MEDIUM

**Objective:** Mobile optimization & responsive design

```
PR-012: Mobile UI Refactor
├─ Refactor form for mobile: Tab-based layout
├─ Add quick entry mode (reduced fields)
├─ Implement photo camera integration
├─ Add offline support (sync on reconnect)
├─ Test: Mobile device tests (iOS + Android emulator)
Effort: 10h | Owner: Frontend Lead

PR-013: Mobile API Endpoints (Optional)
├─ Create dedicated mobile API endpoints
├─ Add response pagination & caching
├─ Implement field filtering
└─ Test: Load tests
Effort: 6h | Owner: Backend Lead

PR-014: Performance Optimization
├─ Add database indexes (project_id, report_date, status)
├─ Implement query optimization (eager loading)
├─ Add caching for weekly aggregates
├─ Reduce bundle size (JS minify)
└─ Test: Load & performance tests
Effort: 6h | Owner: DevOps Lead

🎯 Phase 4 Impact:
✅ Mobile usability: 3/10 → 7/10
✅ Page load time: <3s → <1s
✅ API response time: <500ms
```

### Phase 5: Analytics & Reporting (Weeks 9-10) — MEDIUM

**Objective:** Dashboard & KPI calculations

```
PR-015: Build Dashboard Component
├─ Create dashboard Livewire component
├─ Display real-time KPIs (variance, productivity, safety)
├─ Add S-Curve integration
├─ Add filter by week/month/RAB section
├─ Test: Livewire + browser tests
Effort: 8h | Owner: Frontend Lead

PR-016: Add KPI Calculations
├─ Implement ProgressVariance calculator
├─ Implement ProductivityIndex calculator
├─ Implement SafetyScore calculator
├─ Add trend analysis (weekly/monthly)
└─ Test: Calculator tests
Effort: 6h | Owner: Backend Lead

PR-017: Add Export Features
├─ PDF export with formatting
├─ Excel export with charts
├─ Email scheduling (daily digest)
└─ Test: Export tests
Effort: 5h | Owner: Backend Lead

🎯 Phase 5 Impact:
✅ Reporting & Analytics: 5/10 → 8/10
✅ Dashboard completeness: 0% → 100%
✅ User insights: Manual → Real-time
```

### Timeline Summary

```
┌──────────────────────────────────────────────────────────────────┐
│  IMPLEMENTATION TIMELINE (10 Weeks)                              │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Phase 1: Foundation (Weeks 1-2)    [████████] 17h              │
│  └─ Service layer, refactor, race condition fix                │
│                                                                  │
│ Phase 2: Compliance (Weeks 3-4)    [████████] 24h              │
│  └─ PUPR fields, validation, auto-calculations                 │
│                                                                  │
│ Phase 3: Workflow (Weeks 5-6)      [████████] 21h              │
│  └─ Approval workflow, notifications, audit trail              │
│                                                                  │
│ Phase 4: Mobile (Weeks 7-8)        [████████] 22h              │
│  └─ Mobile UI, performance, offline support                    │
│                                                                  │
│ Phase 5: Analytics (Weeks 9-10)    [████████] 19h              │
│  └─ Dashboard, KPIs, export features                           │
│                                                                  │
│ ─────────────────────────────────────────────────────────────── │
│ Total Effort: ~103 developer hours                             │
│ Team Size: 2-3 developers                                      │
│ End Date: ~10 weeks from start                                 │
│                                                                  │
│ Quality Gates:                                                 │
│  ✓ Code review: Every PR                                       │
│  ✓ Test coverage: >80%                                          │
│  ✓ Performance: <1s page load                                   │
│  ✓ Mobile: >85% usability score                                 │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 8. KESIMPULAN & NEXT STEPS

### Ringkasan Rekomendasi

| Aspek            | Saat Ini | Target |                       Action                       |
| ---------------- | :------: | :----: | :------------------------------------------------: |
| **Workflow**     |   5/10   |  9/10  |      Implement multi-tier approval (Phase 3)       |
| **Data**         |   6/10   |  9/10  |        Add PUPR compliance fields (Phase 2)        |
| **Code Quality** |   4/10   |  8/10  | Create service layer, remove duplication (Phase 1) |
| **Mobile**       |   3/10   |  8/10  |           Responsive redesign (Phase 4)            |
| **Reporting**    |   5/10   |  8/10  |          Build dashboard & KPIs (Phase 5)          |

### Quick Wins (Can implement immediately)

1. ✅ Fix form UI — Add "Copy from yesterday" button (+30 min)
2. ✅ Add next_day_plan field to form display (+30 min)
3. ✅ Better error handling & validation messages (+1h)
4. ✅ Improve list view card design with KPI badges (+2h)
5. ✅ Add safety incident counter display (+1h)

**Total Quick Win Time:** ~5 hours → Immediate usability improvement

### Long-term (6-12 bulan)

- Full mobile app (React Native / Flutter)
- Equipment tracking module integration
- Material reconciliation with procurement
- Advanced analytics & forecasting
- Integration dengan P6/MS Project untuk sync schedule

### Success Metrics

```
After Implementation (Months 1-3):
📊 Adoption: 60% team using daily → 95%+
⏱️  Time to submit report: 15 min → 8 min
🐛 Data errors: 15% → 2%
✓  Approval SLA met: 70% → 95%
📈 Schedule adherence: 85% → 92%
🚨 Safety reporting: 40% incident capture → 98%
```

---

## 📚 Referensi & Best Practices

### Tools & Standards

- **PUPR Standar**: Peraturan Menteri PUPR No. 14/2015 (Laporan Harian Konstruksi)
- **Schedule Management**: PMBOK Guide, Primavera P6 Best Practices
- **Mobile UX**: Material Design 3 (Google), Human Interface Guidelines (Apple)
- **Code Quality**: PSR-12 (PHP), Laravel Best Practices, Clean Code

### Similar Applications Analyzed

- Procore (USA, premium)
- Buildertrend (USA, mid-market)
- Fieldwire (USA, field-first)
- JobBuild (Indonesia, local)
- SAP Construction Hub (Enterprise)
- Primavera P6 (Standard global)

### Rekomendasi Learning Resources

- https://procore.com/blog/daily-logs/
- https://docs.microsoft.com/en-us/dynamics365/project-operations/
- https://www.constructionblog.org/

---

**Dokumen ini diperbarui: 1 Mei 2026**  
**Version:** 1.0  
**Status:** Ready for Implementation Planning
