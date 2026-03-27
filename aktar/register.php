<?php 
include 'db_baglanti.php';
if (isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$page_title = "Kayıt Ol";
$site_adi = "Aktarhane";
$hatalar = []; 
$basari_mesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST['kullanici_adi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';

    if (empty($kullanici_adi) || empty($email) || empty($sifre) || empty($sifre_tekrar)) {
        $hatalar[] = "Lütfen tüm alanları doldurun.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = "Geçersiz e-posta formatı.";
    }

    if ($sifre !== $sifre_tekrar) {
        $hatalar[] = "Şifreler eşleşmiyor.";
    }

    if (strlen($sifre) < 6) {
        $hatalar[] = "Şifre en az 6 karakter olmalıdır.";
    }

    if (empty($hatalar)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = ? OR email = ?");
            $stmt->execute([$kullanici_adi, $email]);
            if ($stmt->fetch()) {
                $hatalar[] = "Bu kullanıcı adı veya e-posta zaten kayıtlı.";
            } else {
                $sifre_hash = password_hash($sifre, PASSWORD_BCRYPT);
                $insert_stmt = $conn->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (?, ?, ?)");
                $insert_stmt->execute([$kullanici_adi, $email, $sifre_hash]);
                $basari_mesaji = "Kayıt başarılı! Lütfen giriş yapın.";
                header("Refresh: 2; url=login.php"); 
            }

        } catch (PDOException $e) {
            $hatalar[] = "Veritabanı hatası: " . $e->getMessage();
        }
    }
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
            <li><a href="index.php">Anasayfa</a></li>
            <li><a href="urunler.php">Tüm Ürünler</a></li>
            <li><a href="sepet.php">Sepet (<?php echo count($_SESSION['sepet']); ?>)</a></li>
        </ul>
        <div class="nav-right">
            <a href="register.php" class="login-btn">Kayıt Ol</a>
            <a href="login.php" class="login-btn" style="background-color: #555;">Giriş Yap</a>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <h2>Yeni Hesap Oluştur</h2>

            <?php if (!empty($hatalar)): ?>
                <div class="mesaj-hata">
                    <?php foreach ($hatalar as $hata): ?>
                        <p><?php echo $hata; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($basari_mesaji)): ?>
                <div class="mesaj-basari">
                    <p><?php echo $basari_mesaji; ?></p>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-grup-login">
                    <label for="kullanici_adi">Kullanıcı Adı:</label>
                    <input type="text" id="kullanici_adi" name="kullanici_adi" required>
                </div>
                <div class="form-grup-login">
                    <label for="email">E-posta:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-grup-login">
                    <label for="sifre">Şifre (Min. 6 karakter):</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <div class="form-grup-login">
                    <label for="sifre_tekrar">Şifre Tekrar:</label>
                    <input type="password" id="sifre_tekrar" name="sifre_tekrar" required>
                </div>
                <button type="submit" class="buton-buyuk-yesil" style="width: 100%;">Kayıt Ol</button>
            </form>
            <p style="text-align: center; margin-top: 15px;">
                Zaten hesabınız var mı? <a href="login.php">Giriş Yapın</a>
            </p>
        </div>
    </div>
</main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>
</body>
</html>