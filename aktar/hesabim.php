<?php 
include 'db_baglanti.php';

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php?hedef=hesabim.php");
    exit;
}

$kullanici_id = $_SESSION['kullanici_id'];
$kullanici_adi = $_SESSION['kullanici_adi'];
$kullanici_email = $_SESSION['kullanici_email'];
try {
    $stmt = $conn->prepare("SELECT * FROM siparisler 
                           WHERE kullanici_id = ? 
                           ORDER BY siparis_tarihi DESC"); 
    $stmt->execute([$kullanici_id]);
    $gecmis_siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Siparişler getirilirken hata oluştu: " . $e->getMessage());
    $gecmis_siparisler = [];
}

$page_title = "Hesabım";
$site_adi = "Aktarhane";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_adi; ?> | <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <nav class="main-nav">
        <a href="index.php" class="logo"><?php echo $site_adi; ?></a> 
        <ul>
            <li><a href="index.php">Anasayfa</a></li>
            <li><a href="urunler.php">Tüm Ürünler</a></li>
            <li><a href="sepet.php">Sepet (<?php echo count($_SESSION['sepet']); ?>)</a></li>
        </ul>
        <div class="nav-right">
            <a href="hesabim.php" style="color: white; font-weight: bold;">Hesabım</a>
            <a href="logout.php" class="login-btn">Çıkış Yap</a>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <h1>Hesabım</h1>
        
        <div class="hesap-grid">
            
            <div class="hesap-bilgi-kutusu">
                <h3>Üyelik Bilgilerim</h3>
                <p><strong>Kullanıcı Adı:</strong> <?php echo htmlspecialchars($kullanici_adi); ?></p>
                <p><strong>E-posta:</strong> <?php echo htmlspecialchars($kullanici_email); ?></p>
                </div>

            <div class="hesap-siparisler">
                <h3>Geçmiş Siparişlerim</h3>
                
                <?php if (empty($gecmis_siparisler)): ?>
                    <p>Daha önce hiç sipariş vermemişsiniz.</p>
                <?php else: ?>
                    <table class="hesap-tablosu">
                        <thead>
                            <tr>
                                <th>Sipariş No</th>
                                <th>Tarih</th>
                                <th>Toplam Tutar</th>
                                <th>Durum</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gecmis_siparisler as $siparis): ?>
                            <tr>
                                <td>#<?php echo $siparis['id']; ?></td>
                                <td><?php echo date("d/m/Y H:i", strtotime($siparis['siparis_tarihi'])); ?></td>
                                <td><?php echo number_format($siparis['toplam_fiyat'], 2); ?> TL</td>
                                <td>
                                    <span class="durum-etiket <?php echo strtolower($siparis['durum']); ?>">
                                        <?php echo htmlspecialchars($siparis['durum']); ?>
                                    </span>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
        </div> </div> </main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>
</body>
</html>