<?php 
include 'db_baglanti.php';
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}
$siparis_id = isset($_GET['siparis_id']) ? (int)$_GET['siparis_id'] : 0;

if ($siparis_id <= 0) {
    header("Location: index.php");
    exit;
}
try {
    $stmt = $conn->prepare("SELECT * FROM siparisler WHERE id = ? AND kullanici_id = ?");
    $stmt->execute([$siparis_id, $_SESSION['kullanici_id']]);
    $siparis = $stmt->fetch();

    if (!$siparis) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}

$page_title = "Sipariş Başarılı";
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
            <li><a href="sepet.php">Sepet (0)</a></li>
        </ul>
        <div class="nav-right">
            <a href="hesabim.php">Hesabım</a>
            <a href="logout.php" class="login-btn">Çıkış Yap</a>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <div class="form-container" style="text-align: center;">
            <h2 style="color: #4CAF50;">Teşekkürler!</h2>
            
            <div class="mesaj-basari">
                <p>Siparişiniz başarıyla alınmıştır.</p>
            </div>
            
            <p style="font-size: 1.1rem; margin: 20px 0;">
                Sipariş Numaranız: <strong>#<?php echo htmlspecialchars($siparis['id']); ?></strong>
            </p>
            
            <p>Siparişinizin durumunu "Hesabım" sayfasından takip edebilirsiniz.</p>
            
            <a href="urunler.php" class="buton-buyuk-yesil" style="margin-top: 25px; width: auto; padding: 15px 30px;">
                Alışverişe Devam Et
            </a>
        </div>
    </div>
</main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>
</body>
</html>