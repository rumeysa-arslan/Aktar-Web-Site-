<?php 
include 'db_baglanti.php';
$page_title = "Anasayfa";
$site_adi = "Aktarhane";

try {
    $stmt = $conn->prepare("SELECT * FROM urunler WHERE stok > 0 ORDER BY RAND() LIMIT 4");
    $stmt->execute();
    $one_cikan_urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Ürünler getirilirken hata oluştu: " . $e->getMessage();
    $one_cikan_urunler = []; 
}
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
            <li><a href="index.php" class="active">Anasayfa</a></li>
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
        
        <section class="hero" style="text-align:center; padding: 40px; background: #fff; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h1>Aktarhane'ye Hoş Geldiniz!</h1>
            <p>Sağlıklı bir yaşam için ihtiyacınız olan doğal ürünler bir tık uzağınızda.</p>
        </section>

        <h2>Öne Çıkan Ürünler</h2>
        
        <div class="urun-grid">
            <?php 
            if (count($one_cikan_urunler) > 0):
                foreach ($one_cikan_urunler as $urun):
            ?>
                <div class="urun-kart">
                    <img src="<?php echo htmlspecialchars($urun['resim_url']); ?>" alt="<?php echo htmlspecialchars($urun['ad']); ?>">
                    
                    <div class="urun-kart-icerik">
                        <h3><?php echo htmlspecialchars($urun['ad']); ?></h3>
                        
                        <div class="urun-fiyat"><?php echo number_format($urun['fiyat'], 2); ?> TL</div>
                        
                        <a href="urun_detay.php?id=<?php echo $urun['id']; ?>" class="buton">Detayları Gör</a>
                    </div>
                </div>
                <?php 
                endforeach;
            else:
                echo "<p>Şu anda öne çıkan ürün bulunmamaktadır.</p>";
            endif;
            ?>
        </div> </div> </main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>

</body>
</html>