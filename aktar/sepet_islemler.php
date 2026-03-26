<?php
// 1. Veritabanı bağlantısını ve session'ı başlat
include 'db_baglanti.php';

// 2. Gelen 'islem' türünü belirle (POST ile gelmeli)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // POST değilse anasayfaya at
    header("Location: index.php");
    exit;
}

$islem = $_POST['islem'] ?? ''; // 'ekle', 'guncelle', 'sil'
$urun_id = isset($_POST['urun_id']) ? (int)$_POST['urun_id'] : 0;
$adet = isset($_POST['adet']) ? (int)$_POST['adet'] : 1; // Ekleme için varsayılan 1

// 3. İşlem türüne göre (switch-case) yönlendirme
try {
    switch ($islem) {
        // --- SEPETE EKLEME ---
        case 'ekle':
            if ($urun_id > 0 && $adet > 0) {
                // Ürünün stok durumunu kontrol et (Veritabanından)
                $stmt = $conn->prepare("SELECT stok FROM urunler WHERE id = ?");
                $stmt->execute([$urun_id]);
                $urun = $stmt->fetch();

                if ($urun && $urun['stok'] > 0) {
                    // Sepette bu ürün zaten var mı?
                    if (isset($_SESSION['sepet'][$urun_id])) {
                        // Varsa, adedini gelen adet kadar ARTIR
                        $yeni_adet = $_SESSION['sepet'][$urun_id] + $adet;
                    } else {
                        // Yoksa, yeni ekle
                        $yeni_adet = $adet;
                    }
                    
                    // Yeni adet, stok miktarını geçmesin
                    if ($yeni_adet > $urun['stok']) {
                        $yeni_adet = $urun['stok']; // Stokla sınırla
                        // (Burada kullanıcıya "Maksimum stok kadar eklendi" uyarısı verilebilir)
                    }

                    $_SESSION['sepet'][$urun_id] = $yeni_adet;
                }
            }
            // Ekleme işleminden sonra kullanıcıyı sepet sayfasına yönlendir
            header("Location: sepet.php");
            exit;

        // --- SEPET GÜNCELLEME (Adet Değiştirme) ---
        case 'guncelle':
            if ($urun_id > 0 && isset($_SESSION['sepet'][$urun_id])) {
                if ($adet > 0) {
                    // Stoğu tekrar kontrol et
                    $stmt = $conn->prepare("SELECT stok FROM urunler WHERE id = ?");
                    $stmt->execute([$urun_id]);
                    $urun = $stmt->fetch();
                    
                    if ($urun && $adet <= $urun['stok']) {
                        $_SESSION['sepet'][$urun_id] = $adet; // Adedi GÜNCELLE
                    } elseif ($urun) {
                        $_SESSION['sepet'][$urun_id] = $urun['stok']; // Stokla sınırla
                    }
                } else {
                    // Adet 0 veya daha azsa, ürünü sil (bkz: 'sil' case)
                    unset($_SESSION['sepet'][$urun_id]);
                }
            }
            // Güncelleme işlemi sepet sayfasından yapıldığı için sepet sayfasına dön
            header("Location: sepet.php");
            exit;

        // --- SEPETTEN SİLME ---
        case 'sil':
            if ($urun_id > 0 && isset($_SESSION['sepet'][$urun_id])) {
                // Session dizisinden o ürünü kaldır
                unset($_SESSION['sepet'][$urun_id]);
            }
            // Silme işlemi sepet sayfasından yapıldığı için sepet sayfasına dön
            header("Location: sepet.php");
            exit;

        default:
            // Bilinmeyen bir işlemse anasayfaya yönlendir
            header("Location: index.php");
            exit;
    }

} catch (PDOException $e) {
    die("İşlem sırasında veritabanı hatası: " . $e->getMessage());
}
?>