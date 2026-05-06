# Flowchart penerapan modul Material Request, Purchase Request, Purchase Order, dan Good Receive.


# 🔷 1. Gambaran Alur Besar (End-to-End Flow)

**Material Request (MR)** → **Purchase Request (PR)** → **Purchase Order (PO)** → **Goods Receipt (GR)**

* MR: kebutuhan dari lapangan
* PR: permintaan pembelian ke procurement
* PO: pemesanan resmi ke vendor
* GR: penerimaan barang

---

# 🔷 2. Modul & Workflow Detail

## 📦 1. Material Request (MR)

### Tujuan:

Permintaan material dari site/project.

### 👤 Role yang terlibat:

* **Requester**: Site Engineer / Supervisor
* **Checker**: Project Manager / Site Manager
* **Optional Approval**: Project Control / Cost Control

### 🔄 Workflow:

1. Site Engineer buat MR
2. Sistem cek stok gudang:

   * Jika **stok ada** → langsung ke issue material
   * Jika **stok tidak cukup** → lanjut ke PR
3. MR diajukan ke Project Manager
4. PM:

   * Approve → lanjut PR
   * Reject → kembali ke requester

### ⚙️ Catatan penting:

* MR bisa split:

  * sebagian dari stok
  * sebagian ke pembelian

---

## 📝 2. Purchase Request (PR)

### Tujuan:

Permintaan pembelian ke tim procurement.

### 👤 Role:

* **Creator**:

  * Bisa dari auto-convert MR
  * Bisa juga manual oleh Site Admin / Project Admin
* **Reviewer**: Project Manager / Cost Control
* **Approver**: Procurement Manager / Project Director (tergantung nilai)

### 🔄 Workflow:

1. PR dibuat (manual / dari MR)
2. Validasi budget oleh Cost Control
3. Approval berjenjang:

   * ≤ 50 juta → Procurement Manager
   * > 50 juta → Project Director
4. Approved → lanjut RFQ / PO

### ⚙️ Best practice:

* PR wajib ada:

  * BOQ reference
  * Budget code
  * Justifikasi

---

## 🛒 3. Purchase Order (PO)

### Tujuan:

Dokumen resmi pemesanan ke vendor.

### 👤 Role:

* **Creator**: Procurement Officer
* **Reviewer**: Procurement Manager
* **Approver**:

  * Finance Manager
  * Director (jika nominal besar)

### 🔄 Workflow:

1. Procurement buat PO dari PR
2. Pilih vendor (hasil RFQ / tender)
3. Review harga & terms
4. Approval berjenjang:

   * Procurement Manager
   * Finance (cek cashflow)
5. PO dikirim ke vendor

### ⚙️ Catatan:

* PO mengunci:

  * harga
  * qty
  * delivery date
* Setelah approved → tidak boleh sembarangan edit (harus revision)

---

## 📥 4. Goods Receipt (GR)

### Tujuan:

Pencatatan barang masuk (fisik vs PO)

### 👤 Role:

* **Receiver**: Warehouse Staff / Storekeeper
* **Checker**: QC / Site Engineer
* **Approver**: Warehouse Manager / Project Manager

### 🔄 Workflow:

1. Barang datang
2. Warehouse input GR berdasarkan PO
3. QC cek:

   * quantity
   * kualitas
4. Jika OK:

   * Approve → stok bertambah
5. Jika tidak:

   * Reject / Partial accept
   * Buat retur

---

# 🔷 3. Matrix Role vs Aksi

| Modul | Create              | Review              | Approve                        | Reject            |
| ----- | ------------------- | ------------------- | ------------------------------ | ----------------- |
| MR    | Site Engineer       | Project Manager     | Project Manager                | Project Manager   |
| PR    | Site Admin / System | Cost Control        | Procurement Manager / Director | Same as approve   |
| PO    | Procurement Officer | Procurement Manager | Finance / Director             | Same as approve   |
| GR    | Warehouse Staff     | QC / Engineer       | Warehouse Manager              | Warehouse Manager |

---

# 🔷 4. Userflow (Sederhana)

### 🔁 Flow Utama:

1. Site Engineer → buat MR
2. PM approve
3. Sistem → generate PR
4. Procurement → buat PO
5. Vendor kirim barang
6. Warehouse → input GR

---

# 🔷 5. Hal Penting Saat Implementasi

### ✅ 1. Approval by Value (WAJIB)

* Gunakan threshold:

  * kecil → cepat
  * besar → berlapis

### ✅ 2. Audit Trail

* Semua aksi:

  * siapa approve
  * kapan
  * komentar

### ✅ 3. Status Flow

Contoh status:

* Draft
* Submitted
* Approved
* Rejected
* Partially Approved
* Closed

### ✅ 4. Integrasi antar modul

* MR → PR (auto convert)
* PR → PO
* PO → GR

---

# 🔷 6. Kesalahan Umum (yang harus dihindari)

❌ MR langsung jadi PO (lompat proses)
❌ Tidak ada kontrol budget di PR
❌ PO bisa diedit setelah approve
❌ GR tidak refer ke PO (bahaya fraud)
❌ Tidak ada role separation (creator = approver)

---

# 🔷 7. Bonus: Role Minimum System

Kalau mau simpel tapi tetap proper:

* Site Engineer
* Project Manager
* Cost Control
* Procurement Officer
* Procurement Manager
* Finance
* Warehouse Staff
* QC

---

