Kalau mau sistem benar-benar **ketat (hard rule)**, kita tidak cukup pakai `status` biasa—kita butuh **state machine di level database** supaya:

* tidak ada loncatan status ilegal
* approval harus sesuai urutan & role
* semua transisi tervalidasi otomatis

Di bawah ini saya buat versi **production-grade state machine di PostgreSQL**.

---

# 🔷 1. TABEL STATE MACHINE CONFIG

## 📌 Master State

```sql
CREATE TABLE sm_states (
  id SERIAL PRIMARY KEY,
  code TEXT UNIQUE NOT NULL, -- draft, submitted, approved, rejected
  description TEXT
);
```

## 📌 Transisi yang diizinkan

```sql
CREATE TABLE sm_transitions (
  id SERIAL PRIMARY KEY,
  document_type TEXT NOT NULL, -- MR, PR, PO, GR

  from_state TEXT REFERENCES sm_states(code),
  to_state TEXT REFERENCES sm_states(code),

  action TEXT NOT NULL, -- submit, approve, reject
  role_required TEXT NOT NULL,

  is_active BOOLEAN DEFAULT TRUE
);
```

---

# 🔷 2. CONTOH DATA TRANSISI

```sql
INSERT INTO sm_states (code) VALUES
('draft'), ('submitted'), ('approved'), ('rejected');

-- MR flow
INSERT INTO sm_transitions (document_type, from_state, to_state, action, role_required) VALUES
('MR', 'draft', 'submitted', 'submit', 'site_engineer'),
('MR', 'submitted', 'approved', 'approve', 'project_manager'),
('MR', 'submitted', 'rejected', 'reject', 'project_manager');

-- PR flow
INSERT INTO sm_transitions VALUES
(DEFAULT, 'PR', 'draft', 'submitted', 'submit', 'site_admin', TRUE),
(DEFAULT, 'PR', 'submitted', 'approved', 'approve', 'proc_manager', TRUE),
(DEFAULT, 'PR', 'submitted', 'rejected', 'reject', 'proc_manager', TRUE);
```

---

# 🔷 3. FUNCTION: VALIDASI TRANSISI (CORE ENGINE)

```sql
CREATE OR REPLACE FUNCTION fn_validate_transition(
    p_document_type TEXT,
    p_current_state TEXT,
    p_action TEXT,
    p_user_id BIGINT
)
RETURNS TEXT AS $$
DECLARE
    v_role TEXT;
    v_next_state TEXT;
BEGIN
    -- ambil role user (simplified: satu role utama)
    SELECT r.name INTO v_role
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = p_user_id
    LIMIT 1;

    -- cek transisi valid
    SELECT to_state INTO v_next_state
    FROM sm_transitions
    WHERE document_type = p_document_type
      AND from_state = p_current_state
      AND action = p_action
      AND role_required = v_role
      AND is_active = TRUE;

    IF v_next_state IS NULL THEN
        RAISE EXCEPTION 
        'Invalid transition: % -> % by role %',
        p_current_state, p_action, v_role;
    END IF;

    RETURN v_next_state;
END;
$$ LANGUAGE plpgsql;
```

---

# 🔷 4. FUNCTION: APPLY TRANSITION

👉 Ini yang dipanggil oleh aplikasi / API

```sql
CREATE OR REPLACE FUNCTION fn_apply_transition(
    p_table TEXT,
    p_id BIGINT,
    p_action TEXT,
    p_user_id BIGINT
)
RETURNS VOID AS $$
DECLARE
    v_current_state TEXT;
    v_new_state TEXT;
    v_document_type TEXT;
BEGIN
    -- mapping table → document type
    v_document_type := CASE p_table
        WHEN 'material_requests' THEN 'MR'
        WHEN 'purchase_requests' THEN 'PR'
        WHEN 'purchase_orders' THEN 'PO'
        WHEN 'goods_receipts' THEN 'GR'
    END;

    -- ambil current state
    EXECUTE format(
        'SELECT status FROM %I WHERE id = $1',
        p_table
    )
    INTO v_current_state
    USING p_id;

    -- validasi & ambil next state
    v_new_state := fn_validate_transition(
        v_document_type,
        v_current_state,
        p_action,
        p_user_id
    );

    -- update state
    EXECUTE format(
        'UPDATE %I SET status = $1 WHERE id = $2',
        p_table
    )
    USING v_new_state, p_id;

    -- log
    INSERT INTO approval_logs (
        document_type,
        document_id,
        action,
        user_id,
        created_at
    ) VALUES (
        v_document_type,
        p_id,
        p_action,
        p_user_id,
        now()
    );

END;
$$ LANGUAGE plpgsql;
```

---

# 🔷 5. HARD LOCK: BLOCK DIRECT UPDATE

👉 Supaya user tidak bisa bypass state machine

```sql
CREATE OR REPLACE FUNCTION fn_block_direct_status_update()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status <> OLD.status THEN
        RAISE EXCEPTION 'Direct status update not allowed. Use state machine.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

### Trigger:

```sql
CREATE TRIGGER trg_block_status_update
BEFORE UPDATE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION fn_block_direct_status_update();
```

👉 Pasang ke semua tabel:

* MR
* PR
* PO
* GR

---

# 🔷 6. OPTIONAL: MULTI-LEVEL APPROVAL

Kalau butuh approval bertingkat:

```sql
ALTER TABLE sm_transitions ADD COLUMN min_amount NUMERIC;
ALTER TABLE sm_transitions ADD COLUMN max_amount NUMERIC;
```

Dan validasi:

```sql
AND (min_amount IS NULL OR amount >= min_amount)
```

---

# 🔷 7. CONTOH PEMAKAIAN

```sql
SELECT fn_apply_transition(
  'purchase_orders',
  1001,
  'approve',
  5
);
```

---

# 🔷 8. KELEBIHAN PENDEKATAN INI

✅ Tidak bisa bypass
✅ Role-based enforced di DB
✅ Flexible (config-driven)
✅ Audit otomatis
✅ Bisa dipakai semua modul

---

# 🔷 9. KEKURANGAN (JUJUR)

Saya harus jujur:

👉 Ini **powerful tapi kompleks**

Di sistem besar biasanya:

* validasi utama tetap di **service layer**
* DB = last guard (bukan main logic)

---

# 🔷 10. Kalau Mau Lebih Hardcore Lagi

Saya bisa bantu:

* 🔥 Parallel approval (multi approver wajib)
* 🔥 Delegation (user A approve atas nama B)
* 🔥 SLA timeout auto-reject
* 🔥 Event-driven workflow (tanpa trigger)

---

Kalau Anda mau lanjut, langkah paling “real production” berikutnya:

👉 **API + transaction flow + concurrency handling (race condition)**

Karena di sini biasanya sistem mulai “pecah” kalau tidak didesain dari awal.
