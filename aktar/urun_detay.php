<?php 

include 'db_baglanti.php';
$urun_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($urun_id <= 0) {
    header("Location: index.php");
    exit;
}
try {
    $stmt = $conn->prepare("SELECT * FROM urunler WHERE id = ? AND stok > 0");
    $stmt->execute([$urun_id]);
    $urun = $stmt->fetch(PDO::FETCH_ASSOC); 

    if (!$urun) {
        die("HATA: Ürün bulunamadı veya stokta yok.");
    }

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
$page_title = $urun['ad'];
$site_adi = "Aktarhane";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_adi; ?> | <?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <nav class="main-nav">
        <a href="index.php" class="logo"><?php echo $site_adi; ?></a> 
        <ul>
            <li><a href="index.php">Anasayfa</a></li>
            <li><a href="urunler.php">Tüm Ürünler</a></li>
            <?php 
            $sepet_urun_sayisi = count($_SESSION['sepet']);
            ?>
            <li><a href="sepet.php">Sepet (<?php echo $sepet_urun_sayisi; ?>)</a></li>
        </ul>
        <div class="nav-right">
            <?php if (isset($_SESSION['kullanici_id'])): ?>
                <a href="hesabim.php">Hesabım</a>
                <a href="logout.php" class="login-btn">Çıkış Yap</a>
            <?php else: ?>
                <a href="register.php">Kayıt Ol</a>
                <a href="login.php" class="login-btn">Giriş Yap</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        
        <div class="urun-detay-grid">
            
            <div class="urun-detay-resim">
                <img src="<?php echo htmlspecialchars($urun['resim_url']); ?>" alt="<?php echo htmlspecialchars($urun['ad']); ?>">
            </div>

            <div class="urun-detay-bilgi">
                <h1><?php echo htmlspecialchars($urun['ad']); ?></h1>
                
                <div class="detay-fiyat">
                    <?php echo number_format($urun['fiyat'], 2); ?> TL
                </div>
                
                <div class="detay-aciklama">
                    <p><?php echo nl2br(htmlspecialchars($urun['aciklama'])); // nl2br: veritabanındaki satır atlamalarını <br>'ye çevirir ?></p>
                </div>
                
                <hr style="margin: 20px 0;">

                <form action="sepet_islemler.php" method="POST">
                    
                    <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                    
                    <input type="hidden" name="islem" value="ekle">
                    
                    <div class="form-grup">
                        <label for="adet">Adet:</label>
                        <input type="number" id="adet" name="adet" value="1" min="1" max="<?php echo $urun['stok']; ?>" required>
                        <span style="margin-left: 10px;">(Stok: <?php echo $urun['stok']; ?>)</span>
                    </div>
                    
                    <button type="submit" class="buton-buyuk-yesil">
                        Sepete Ekle
                    </button>
                    
                </form>
                
            </div>
            
        </div> </div> </main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>

</body>
</html>