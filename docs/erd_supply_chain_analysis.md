Berikut desain **ERD (Entity Relationship Diagram)** untuk modul:

**MR → PR → PO → GR**
dengan pendekatan yang siap diimplementasikan (relasional + scalable).

Saya sertakan:

* struktur tabel inti
* relasi
* best practice (normalisasi + audit)

---

# 🔷 1. Gambaran Relasi Utama

```
Material Request → Purchase Request → Purchase Order → Goods Receipt
        |                    |                    |                |
     MR Items            PR Items            PO Items         GR Items
```

---

# 🔷 2. ERD (Mermaid Diagram)

```mermaid
erDiagram

    USERS ||--o{ MATERIAL_REQUEST : creates
    USERS ||--o{ PURCHASE_REQUEST : creates
    USERS ||--o{ PURCHASE_ORDER : creates
    USERS ||--o{ GOODS_RECEIPT : creates

    MATERIAL_REQUEST ||--o{ MR_ITEMS : has
    PURCHASE_REQUEST ||--o{ PR_ITEMS : has
    PURCHASE_ORDER ||--o{ PO_ITEMS : has
    GOODS_RECEIPT ||--o{ GR_ITEMS : has

    MR_ITEMS ||--o{ PR_ITEMS : converts_to
    PR_ITEMS ||--o{ PO_ITEMS : converts_to
    PO_ITEMS ||--o{ GR_ITEMS : received_as

    VENDORS ||--o{ PURCHASE_ORDER : supplies

    PRODUCTS ||--o{ MR_ITEMS : requested
    PRODUCTS ||--o{ PR_ITEMS : requested
    PRODUCTS ||--o{ PO_ITEMS : ordered
    PRODUCTS ||--o{ GR_ITEMS : received

    WAREHOUSES ||--o{ GOODS_RECEIPT : location

    MATERIAL_REQUEST {
        int id PK
        string mr_number
        date request_date
        int project_id
        int created_by
        string status
        text notes
    }

    MR_ITEMS {
        int id PK
        int mr_id FK
        int product_id FK
        decimal qty_requested
        decimal qty_approved
    }

    PURCHASE_REQUEST {
        int id PK
        string pr_number
        int mr_id FK
        date request_date
        int created_by
        string status
    }

    PR_ITEMS {
        int id PK
        int pr_id FK
        int mr_item_id FK
        int product_id FK
        decimal qty
        decimal estimated_price
    }

    PURCHASE_ORDER {
        int id PK
        string po_number
        int pr_id FK
        int vendor_id FK
        date order_date
        string status
        decimal total_amount
    }

    PO_ITEMS {
        int id PK
        int po_id FK
        int pr_item_id FK
        int product_id FK
        decimal qty
        decimal price
    }

    GOODS_RECEIPT {
        int id PK
        string gr_number
        int po_id FK
        int warehouse_id FK
        date received_date
        string status
    }

    GR_ITEMS {
        int id PK
        int gr_id FK
        int po_item_id FK
        int product_id FK
        decimal qty_received
        decimal qty_rejected
    }

    PRODUCTS {
        int id PK
        string name
        string uom
    }

    VENDORS {
        int id PK
        string name
        string contact
    }

    USERS {
        int id PK
        string name
        string role
    }

    WAREHOUSES {
        int id PK
        string name
    }
```

---

# 🔷 3. Penjelasan Desain (Penting)

## ✅ 1. Relasi Bertingkat (Traceability)

* MR → PR → PO → GR **tidak langsung**, tapi lewat item:

  * `mr_items → pr_items → po_items → gr_items`

👉 Ini penting untuk:

* tracking per item
* partial order
* partial delivery

---

## ✅ 2. Status per Dokumen

Semua tabel utama punya:

```
status:
- draft
- submitted
- approved
- rejected
- closed
```

---

## ✅ 3. Audit Trail (WAJIB TAMBAH)

Tambahkan kolom:

```
created_at
created_by
approved_at
approved_by
rejected_at
rejected_by
```

Atau buat tabel:

```
approval_logs
- document_type (MR, PR, PO, GR)
- document_id
- action
- user_id
- timestamp
- notes
```

---

## ✅ 4. Budget Control (Opsional tapi penting)

Tambahkan ke PR:

```
budget_code
project_id
cost_center
```

---

## ✅ 5. Inventory Integration

Tambahkan tabel:

```
stock_movements
- product_id
- warehouse_id
- qty_in
- qty_out
- reference_type (GR / Issue)
- reference_id
```

---

# 🔷 4. Relasi Kritis (Sering Salah)

### ❌ Jangan:

* GR langsung ke PR ❌
* PO tanpa PR ❌ (kecuali special case)

### ✅ Harus:

* GR → PO → PR → MR

---

# 🔷 5. Skalabilitas (Best Practice)

Kalau sistem akan besar:

### Pisahkan:

* `projects`
* `cost_centers`
* `uom`
* `currency`

### Gunakan:

* UUID untuk distributed system
* soft delete (`deleted_at`)

---

# 🔷 6. Bonus: Role Mapping ke Database

Tambahkan tabel:

```
roles
permissions
user_roles
```

Contoh:

* Site Engineer → create MR
* PM → approve MR
* Procurement → create PO
* Warehouse → create GR

---

# 🔷 7. Kalau Mau Lebih Advance

Saya bisa bantu lanjut ke:

* 🔹 **ERD versi production (dengan indexing & constraint detail)**
* 🔹 **DDL SQL (PostgreSQL / MySQL)**
* 🔹 **API design (REST / GraphQL)**
* 🔹 **State machine per modul**

Tinggal bilang mau lanjut ke mana 👍
