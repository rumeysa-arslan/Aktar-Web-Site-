<?php
include 'db_baglanti.php';
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$islem = $_POST['islem'] ?? ''; 
$urun_id = isset($_POST['urun_id']) ? (int)$_POST['urun_id'] : 0;
$adet = isset($_POST['adet']) ? (int)$_POST['adet'] : 1; 
try {
    switch ($islem) {
        case 'ekle':
            if ($urun_id > 0 && $adet > 0) {
                $stmt = $conn->prepare("SELECT stok FROM urunler WHERE id = ?");
                $stmt->execute([$urun_id]);
                $urun = $stmt->fetch();

                if ($urun && $urun['stok'] > 0) {
                    if (isset($_SESSION['sepet'][$urun_id])) {
                        $yeni_adet = $_SESSION['sepet'][$urun_id] + $adet;
                    } else {
                        $yeni_adet = $adet;
                    }
                    if ($yeni_adet > $urun['stok']) {
                        $yeni_adet = $urun['stok']; 
                    }

                    $_SESSION['sepet'][$urun_id] = $yeni_adet;
                }
            }
            header("Location: sepet.php");
            exit;
        case 'guncelle':
            if ($urun_id > 0 && isset($_SESSION['sepet'][$urun_id])) {
                if ($adet > 0) {
                    $stmt = $conn->prepare("SELECT stok FROM urunler WHERE id = ?");
                    $stmt->execute([$urun_id]);
                    $urun = $stmt->fetch();
                    
                    if ($urun && $adet <= $urun['stok']) {
                        $_SESSION['sepet'][$urun_id] = $adet; 
                    } elseif ($urun) {
                        $_SESSION['sepet'][$urun_id] = $urun['stok']; 
                    }
                } else {
                    unset($_SESSION['sepet'][$urun_id]);
                }
            }
            header("Location: sepet.php");
            exit;
        case 'sil':
            if ($urun_id > 0 && isset($_SESSION['sepet'][$urun_id])) {
                unset($_SESSION['sepet'][$urun_id]);
            }
            header("Location: sepet.php");
            exit;

        default:
            header("Location: index.php");
            exit;
    }

} catch (PDOException $e) {
    die("İşlem sırasında veritabanı hatası: " . $e->getMessage());
}
?>