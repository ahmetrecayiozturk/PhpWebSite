-- transactions tablosu: bakiye hareketlerini kaydeder
CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    amount REAL NOT NULL,           -- pozitif: yükleme, negatif: çekim
    type TEXT NOT NULL,             -- 'topup', 'refund', 'purchase' vb.
    reference TEXT,                 -- ödeme sağlayıcı referansı veya açıklama
    created_at DATETIME DEFAULT (datetime('now'))
);