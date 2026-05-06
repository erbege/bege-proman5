Mantap, ini bagian yang biasanya bikin sistem jadi **“hidup”**—karena tanpa trigger & function, ERD tadi cuma jadi data pasif.

Saya akan kasih versi **production-ready PostgreSQL** untuk:

1. ✅ Auto update stock saat GR approved
2. ✅ Validasi qty (tidak boleh over-receive)
3. ✅ Proteksi status (hanya dokumen approved yang boleh impact stock)

---

# 🔷 1. FUNCTION: VALIDASI QTY (ANTI OVER-RECEIVE)

👉 Mencegah total penerimaan melebihi qty di PO

```sql
CREATE OR REPLACE FUNCTION fn_validate_gr_qty()
RETURNS TRIGGER AS $$
DECLARE
    total_received NUMERIC;
    po_qty NUMERIC;
BEGIN
    -- Ambil total qty received sebelumnya (exclude current row jika update)
    SELECT COALESCE(SUM(qty_received),0)
    INTO total_received
    FROM gr_items
    WHERE po_item_id = NEW.po_item_id
    AND id <> COALESCE(NEW.id, 0);

    -- Ambil qty dari PO
    SELECT qty INTO po_qty
    FROM po_items
    WHERE id = NEW.po_item_id;

    IF (total_received + NEW.qty_received) > po_qty THEN
        RAISE EXCEPTION 'Over receiving detected! PO qty: %, Attempt: %',
            po_qty, (total_received + NEW.qty_received);
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### Trigger:

```sql
CREATE TRIGGER trg_validate_gr_qty
BEFORE INSERT OR UPDATE ON gr_items
FOR EACH ROW
EXECUTE FUNCTION fn_validate_gr_qty();
```

---

# 🔷 2. FUNCTION: AUTO STOCK IN (SAAT GR APPROVED)

👉 Hanya jalan kalau status GR = `approved`

```sql
CREATE OR REPLACE FUNCTION fn_post_gr_to_stock()
RETURNS TRIGGER AS $$
DECLARE
    rec RECORD;
BEGIN
    -- hanya eksekusi jika status berubah ke approved
    IF NEW.status = 'approved' AND OLD.status IS DISTINCT FROM 'approved' THEN

        FOR rec IN
            SELECT * FROM gr_items WHERE gr_id = NEW.id
        LOOP
            INSERT INTO stock_movements (
                product_id,
                warehouse_id,
                qty_in,
                qty_out,
                reference_type,
                reference_id,
                created_at
            ) VALUES (
                rec.product_id,
                NEW.warehouse_id,
                rec.qty_received,
                0,
                'GR',
                NEW.id,
                now()
            );
        END LOOP;

    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### Trigger:

```sql
CREATE TRIGGER trg_post_gr_stock
AFTER UPDATE ON goods_receipts
FOR EACH ROW
EXECUTE FUNCTION fn_post_gr_to_stock();
```

---

# 🔷 3. FUNCTION: PREVENT EDIT AFTER APPROVAL

👉 Mengunci data setelah approved

```sql
CREATE OR REPLACE FUNCTION fn_prevent_update_if_approved()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.status = 'approved' THEN
        RAISE EXCEPTION 'Document already approved and cannot be modified';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### Trigger contoh (PO):

```sql
CREATE TRIGGER trg_lock_po
BEFORE UPDATE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION fn_prevent_update_if_approved();
```

👉 Bisa dipasang juga ke:

* material_requests
* purchase_requests
* goods_receipts

---

# 🔷 4. FUNCTION: AUTO UPDATE TOTAL PO

👉 Menjaga `total_amount` selalu konsisten

```sql
CREATE OR REPLACE FUNCTION fn_update_po_total()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE purchase_orders
    SET total_amount = (
        SELECT SUM(qty * price)
        FROM po_items
        WHERE po_id = NEW.po_id
    )
    WHERE id = NEW.po_id;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
```

### Trigger:

```sql
CREATE TRIGGER trg_update_po_total
AFTER INSERT OR UPDATE OR DELETE ON po_items
FOR EACH ROW
EXECUTE FUNCTION fn_update_po_total();
```

---

# 🔷 5. FUNCTION: AUTO STOCK OUT (JIKA MATERIAL DIAMBIL)

👉 Optional (kalau ada issue material dari warehouse)

```sql
CREATE OR REPLACE FUNCTION fn_issue_material()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO stock_movements (
        product_id,
        warehouse_id,
        qty_in,
        qty_out,
        reference_type,
        reference_id,
        created_at
    ) VALUES (
        NEW.product_id,
        NEW.warehouse_id,
        0,
        NEW.qty_issued,
        'ISSUE',
        NEW.id,
        now()
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

---

# 🔷 6. BEST PRACTICE (KRITIS)

## ⚠️ 1. JANGAN DOUBLE POSTING

Tambahkan proteksi:

```sql
IF EXISTS (
  SELECT 1 FROM stock_movements 
  WHERE reference_type = 'GR' AND reference_id = NEW.id
)
THEN
  RAISE EXCEPTION 'Stock already posted for this GR';
END IF;
```

---

## ⚠️ 2. GUNAKAN TRANSACTION

Semua approval harus dalam:

```sql
BEGIN;
UPDATE goods_receipts SET status='approved' WHERE id=...;
COMMIT;
```

---

## ⚠️ 3. IDEMPOTENCY (SANGAT PENTING)

Trigger harus aman kalau dijalankan ulang.

---

# 🔷 7. FLOW YANG TERJADI (REAL SYSTEM)

1. User approve GR
2. Trigger jalan
3. Loop GR items
4. Insert stock movement
5. Stock bertambah

---

# 🔷 8. Kalau Mau Level Lebih Gila (Enterprise)

Saya bisa bantu:

* 🔥 FIFO / moving average costing
* 🔥 reserved stock (untuk MR)
* 🔥 multi warehouse transfer
* 🔥 approval engine dinamis (by amount & role)
* 🔥 event-driven (Kafka / queue instead of trigger)

---

Kalau Anda serius mau production:

👉 langkah berikut terbaik adalah:
**saya bantu bikin service layer (API + transaction flow)**
karena di sistem besar, sebagian logic biasanya dipindah dari trigger ke service.

Mau lanjut ke sana?
