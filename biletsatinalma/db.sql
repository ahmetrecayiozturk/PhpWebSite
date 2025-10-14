-- Kullanıcılar
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT,
    email TEXT,
    role TEXT CHECK(role IN ('user','firma_admin','admin')),
    firma_id INTEGER,
    credit REAL DEFAULT 0
);

-- Firmalar
CREATE TABLE firmas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT
);

-- Seferler
CREATE TABLE sefers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firma_id INTEGER,
    kalkis TEXT,
    varis TEXT,
    tarih DATE,
    saat TIME,
    fiyat REAL,
    koltuk_sayisi INTEGER
);

-- Biletler
CREATE TABLE biletler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    sefer_id INTEGER,
    koltuk_no INTEGER,
    fiyat REAL,
    durum TEXT CHECK(durum IN ('aktif','iptal')),
    pdf_path TEXT,
    created_at DATETIME
);

-- Kuponlar
CREATE TABLE kuponlar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kod TEXT UNIQUE,
    oran INTEGER,
    firma_id INTEGER,
    kullanim_limiti INTEGER,
    son_kullanma DATE
);