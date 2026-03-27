<?php 
include 'db_baglanti.php';
if (isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$page_title = "Giriş Yap";
$site_adi = "Aktarhane";
$hata_mesaji = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_veya_kadi = trim($_POST['email_veya_kadi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';

    if (empty($email_veya_kadi) || empty($sifre)) {
        $hata_mesaji = "Lütfen tüm alanları doldurun.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE email = ? OR kullanici_adi = ?");
            $stmt->execute([$email_veya_kadi, $email_veya_kadi]);
            $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($kullanici && password_verify($sifre, $kullanici['sifre'])) {

                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];
                $_SESSION['kullanici_email'] = $kullanici['email'];
                header("Location: index.php");
                exit;
                
            } else {
                $hata_mesaji = "Kullanıcı adı veya şifre hatalı.";
            }

        } catch (PDOException $e) {
            $hata_mesaji = "Veritabanı hatası: " . $e->getMessage();
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
            <h2>Giriş Yap</h2>

            <?php if (!empty($hata_mesaji)): ?>
                <div class="mesaj-hata">
                    <p><?php echo $hata_mesaji; ?></p>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-grup-login">
                    <label for="email_veya_kadi">Kullanıcı Adı veya E-posta:</label>
                    <input type="text" id="email_veya_kadi" name="email_veya_kadi" required>
                </div>
                <div class="form-grup-login">
                    <label for="sifre">Şifre:</label>
                    <input type="password" id="sifre" name="sifre" required>
                </div>
                <button type="submit" class="buton-buyuk-yesil" style="width: 100%;">Giriş Yap</button>
            </form>
            <p style="text-align: center; margin-top: 15px;">
                Hesabınız yok mu? <a href="register.php">Hemen Kayıt Olun</a>
            </p>
        </div>
    </div>
</main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>
</body>
</html>