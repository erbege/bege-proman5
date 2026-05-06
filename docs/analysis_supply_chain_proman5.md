# Analisis Supply Chain - PROMAN5 (Construction ERP)

Dokumen ini berisi analisis mendalam mengenai alur *supply chain* yang saat ini diterapkan pada proyek PROMAN5, perbandingannya dengan praktik terbaik (*best practices*), serta rekomendasi peningkatan untuk skalabilitas dan integritas data.

---

## 1. Analisis Alur Saat Ini (Current State)

Berdasarkan dokumen referensi dan struktur kode (`app/Models`), alur *supply chain* PROMAN5 mengikuti rantai linear:
**Material Request (MR) → Purchase Request (PR) → Purchase Order (PO) → Goods Receipt (GR) → Inventory**

### ✅ Kelebihan Implementasi Saat Ini:
1.  **Traceability**: Adanya relasi antar item (`MR_ITEMS` ke `PR_ITEMS`, dst.) memungkinkan pelacakan sisa kuantitas yang belum diproses (*remaining to order/receive*).
2.  **Audit Trail**: Penggunaan `Spatie\Activitylog` dan `ApprovalLog` memberikan transparansi terhadap siapa yang mengubah data dan melakukan persetujuan.
3.  **Data Locking**: Implementasi pada level model (`boot` method) yang mencegah pengeditan field kritis setelah dokumen disetujui adalah langkah keamanan yang sangat baik.
4.  **Inventory Management**: Adanya konsep `average_cost` dan `reserved_qty` pada model `Inventory` menunjukkan kesiapan untuk manajemen stok yang presisi.

---

## 2. Gap Analysis & Rekomendasi Perubahan

Berdasarkan standar industri ERP Konstruksi, terdapat beberapa area yang perlu diperkuat:

### 🔷 A. Integrasi Anggaran (Budget Control)
*   **Masalah**: Saat ini, link ke RAB (`RabItem`) baru terlihat kuat di `MaterialUsage`. Pada level `PurchaseRequest`, kontrol anggaran terhadap pagu harga/kuantitas RAB belum terlihat eksplisit secara otomatis.
*   **Rekomendasi**: 
    *   Setiap baris di `PR_ITEMS` wajib merujuk ke `rab_item_id`.
    *   Tambahkan validasi saat PR dibuat: `Total PR (Qty * Price) + Total PO sebelumnya <= Anggaran RAB`.
    *   Cegah pembuatan PR jika item tidak ada dalam RAB atau melebihi sisa anggaran tanpa *Addendum/Justification*.

### 🔷 B. 3-Way Matching (Financial Closing)
*   **Masalah**: Alur saat ini berhenti di `Goods Receipt`. Dalam *supply chain* yang lengkap, kita perlu memastikan apa yang dipesan, apa yang diterima, dan apa yang ditagih oleh vendor adalah sama.
*   **Rekomendasi**: Tambahkan modul **Supplier Invoice** dan **Payment Tracking**:
    1.  **Supplier Invoice**: Dibuat berdasarkan GR (atau PO untuk jasa).
    2.  **3-Way Match**: Sistem mencocokkan `PO Qty/Price` vs `GR Qty` vs `Invoice Qty/Price`.
    3.  **Payment Request**: Proses approval untuk pembayaran ke vendor setelah *Invoice* divalidasi.

### 🔷 C. Manajemen Satuan (UOM Conversion)
*   **Masalah**: Seringkali barang dibeli dalam satuan besar (misal: "Palet" atau "Truk") tapi digunakan di lapangan dalam satuan kecil (misal: "Semen per Sak" atau "M3").
*   **Rekomendasi**: Tambahkan tabel `UnitConversion` atau field `conversion_factor` pada `Material`. Hal ini penting agar stok di gudang dan penggunaan di RAB tetap akurat secara matematis.

### 🔷 D. State Machine (Workflow Engine)
*   **Masalah**: Logika transisi status saat ini tersebar di `boot()` models dan mungkin di *Controller/Livewire*. Hal ini sulit dimaintain jika alur approval menjadi kompleks (misal: butuh 3 approver untuk nilai > 1M).
*   **Rekomendasi**: Implementasikan *Workflow Engine* pusat (seperti referensi `state_machine_supply_chain_analysis.md`). Gunakan tabel konfigurasi untuk mendefinisikan siapa boleh melakukan aksi apa pada status apa.

### 🔷 E. Retur Barang (Goods Return)
*   **Masalah**: Belum ada alur untuk barang yang ditolak saat QC di GR atau barang yang rusak setelah masuk gudang.
*   **Rekomendasi**: Tambahkan modul `PurchaseReturn` yang akan mengurangi stok dan memicu nota debet ke vendor.

---

## 3. Rekomendasi Teknis (Code & Database)

### 📊 Skema Database (Penyesuaian)
1.  **PR Items**: Tambahkan `rab_item_id` (FK) dan `budget_status`.
2.  **Inventory**: Pastikan `average_cost` diupdate setiap kali ada GR baru menggunakan formula *Weighted Average Cost*.
3.  **Suppliers**: Tambahkan field untuk *Payment Terms* (TOP) dan bank account (sudah diinisiasi di percakapan sebelumnya).

### ⚙️ Trigger & Logic
1.  **Auto-Closing PO**: Jika semua `PO_ITEMS` sudah di-receive secara penuh di GR, status PO otomatis menjadi `closed`.
2.  **Stock Reservation**: Saat MR disetujui, kuantitas tersebut harus masuk ke `reserved_qty` di `Inventory` agar tidak bisa digunakan oleh MR lain, sampai barang benar-benar di-*issue*.

---

## 4. Matriks Role & Tanggung Jawab (Best Practice)

| Role | MR | PR | PO | GR | Invoice |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Site Engineer** | Create | - | - | Check | - |
| **Project Manager** | Approve | Review | - | Approve | Review |
| **Cost Control** | - | Validate | - | - | - |
| **Procurement** | - | Create | Create | - | - |
| **Warehouse** | - | - | - | Create | - |
| **Finance** | - | - | Approve | - | Pay |

---

## 5. Kesimpulan & Langkah Selanjutnya

Sistem PROMAN5 sudah memiliki fondasi yang kuat. Fokus pengembangan selanjutnya sebaiknya adalah **Budget Integration** (mengunci PR ke RAB) dan **Financial Integration** (Invoice & Payment). 

Dengan mengunci alur dari RAB hingga Pembayaran, kebocoran biaya (*cost overrun*) dapat dideteksi sejak dini pada level *Purchase Request*, bukan setelah uang dibayarkan.

---
*Generated by Antigravity AI - Supply Chain Analysis Module*
