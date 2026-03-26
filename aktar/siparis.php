<?php 
// 1. Veritabanı bağlantısı ve session
include 'db_baglanti.php';

// --- GÜVENLİK KONTROLLERİ ---

// 2. Kullanıcı giriş yapmış mı?
if (!isset($_SESSION['kullanici_id'])) {
    // Giriş yapmamışsa, login sayfasına yönlendir
    header("Location: login.php?hedef=siparis.php"); // hedef: giriş yapınca buraya dönsün
    exit;
}

// 3. Sepet boş mu?
if (empty($_SESSION['sepet'])) {
    // Sepet boşsa anasayfaya yönlendir
    header("Location: index.php");
    exit;
}

// 4. Sepet bilgilerini ve toplam fiyatı tekrar hesapla (GÜVENLİK!)
$sepet_urunleri = [];
$toplam_fiyat = 0;
$stok_hatasi = false;

try {
    $urun_idler = array_keys($_SESSION['sepet']);
    $yer_tutucular = implode(',', array_fill(0, count($urun_idler), '?'));
    
    $stmt = $conn->prepare("SELECT * FROM urunler WHERE id IN ($yer_tutucular)");
    $stmt->execute($urun_idler);
    $db_urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ürünleri ID'ye göre organize et (kolay erişim için)
    $db_urunler_dizisi = [];
    foreach ($db_urunler as $urun) {
        $db_urunler_dizisi[$urun['id']] = $urun;
    }

    foreach ($_SESSION['sepet'] as $urun_id => $adet) {
        if (!isset($db_urunler_dizisi[$urun_id])) {
            unset($_SESSION['sepet'][$urun_id]); // Ürün artık veritabanında yok
            $stok_hatasi = true;
            continue;
        }

        $urun = $db_urunler_dizisi[$urun_id];
        
        if ($adet > $urun['stok']) {
            $stok_hatasi = true; // Stok yetersiz!
            $adet = $urun['stok']; // Adedi stokla sınırla
            $_SESSION['sepet'][$urun_id] = $adet; // Session'ı düzelt
        }
        
        if ($adet <= 0) {
            unset($_SESSION['sepet'][$urun_id]);
            continue;
        }

        $urun['adet'] = $adet;
        $urun['ara_toplam'] = $urun['fiyat'] * $adet;
        
        $sepet_urunleri[] = $urun;
        $toplam_fiyat += $urun['ara_toplam'];
    }

} catch (PDOException $e) {
    die("Sipariş hazırlanırken hata oluştu: " . $e->getMessage());
}

// Eğer son kontrolde sepet boşaldıysa (örn. ürünler silinmiş)
if (empty($sepet_urunleri)) {
    header("Location: sepet.php");
    exit;
}

// Hata mesajı için
$hata_mesaji = "";

// --- FORM GÖNDERİLDİ Mİ? (SİPARİŞİ OLUŞTURMA) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $teslimat_adresi = trim($_POST['teslimat_adresi'] ?? '');
    
    if (empty($teslimat_adresi)) {
        $hata_mesaji = "Lütfen teslimat adresi giriniz.";
    } 
    // Stokta bir değişiklik olduysa tekrar sepet sayfasına yönlendir
    elseif ($stok_hatasi) {
        header("Location: sepet.php"); // sepet.php uyar_ıyı gösterecek
        exit;
    }
    // Her şey yolundaysa, VERİTABANI TRANSACTION'ı başlat
    else {
        try {
            // 1. TRANSACTION BAŞLAT
            $conn->beginTransaction();

            // 2. ADIM: `siparisler` tablosuna ana kaydı gir
            $stmt_siparis = $conn->prepare("INSERT INTO siparisler 
                (kullanici_id, toplam_fiyat, teslimat_adresi, durum) 
                VALUES (?, ?, ?, 'Hazırlanıyor')");
            $stmt_siparis->execute([
                $_SESSION['kullanici_id'],
                $toplam_fiyat,
                $teslimat_adresi
            ]);
            
            // Son eklenen siparişin ID'sini al
            $yeni_siparis_id = $conn->lastInsertId();

            // 3. ADIM: `siparis_detaylari` ve `urunler` stok güncellemesi (DÖNGÜ)
            $stmt_detay = $conn->prepare("INSERT INTO siparis_detaylari 
                (siparis_id, urun_id, adet, birim_fiyat) 
                VALUES (?, ?, ?, ?)");
            
            $stmt_stok_guncelle = $conn->prepare("UPDATE urunler SET stok = stok - ? 
                                                 WHERE id = ? AND stok >= ?"); // Güvenli stok güncelleme

            foreach ($sepet_urunleri as $urun) {
                // Sipariş detayını ekle
                $stmt_detay->execute([
                    $yeni_siparis_id,
                    $urun['id'],
                    $urun['adet'],
                    $urun['fiyat']
                ]);

                // Stoğu güncelle
                $stok_guncelle_sonuc = $stmt_stok_guncelle->execute([
                    $urun['adet'],
                    $urun['id'],
                    $urun['adet'] // Stok miktarının yeterli olup olmadığını tekrar kontrol et
                ]);

                // Eğer stok güncellemesi başarısız olursa (stok aniden bittiyse)
                if ($stmt_stok_guncelle->rowCount() == 0) {
                    throw new Exception("Ürün stoğu yetersiz: " . htmlspecialchars($urun['ad']));
                }
            }

            // 4. ADIM: Hiç hata olmadıysa, işlemi ONAYLA (COMMIT)
            $conn->commit();

            // 5. ADIM: Sepeti temizle
            $_SESSION['sepet'] = [];

            // 6. ADIM: Başarı sayfasına yönlendir
            header("Location: siparis_basarili.php?siparis_id=" . $yeni_siparis_id);
            exit;

        } catch (Exception $e) {
            // --- HATA OLURSA ---
            // 1. ADIM: Tüm değişiklikleri GERİ AL (ROLLBACK)
            $conn->rollBack();
            
            // 2. ADIM: Kullanıcıya hatayı göster
            $hata_mesaji = "Sipariş oluşturulamadı: " . $e->getMessage();
        }
    }
}

// Sayfa başlığı
$page_title = "Siparişi Tamamla";
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
            <?php $sepet_urun_sayisi = count($_SESSION['sepet']); ?>
            <li><a href="sepet.php">Sepet (<?php echo $sepet_urun_sayisi; ?>)</a></li>
        </ul>
        <div class="nav-right">
            <a href="hesabim.php">Hesabım (<?php echo htmlspecialchars($_SESSION['kullanici_adi']); ?>)</a>
            <a href="logout.php" class="login-btn">Çıkış Yap</a>
        </div>
    </nav>
</header>

<main>
    <div class="container">
        <h1>Siparişi Tamamla</h1>
        
        <?php if (!empty($hata_mesaji)): ?>
            <div class="mesaj-hata">
                <p><?php echo $hata_mesaji; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($stok_hatasi): ?>
            <div class="mesaj-hata">
                <p>Sepetinizdeki bazı ürünlerin stok durumu değişti. Lütfen adetleri kontrol edip tekrar deneyin. 
                <a href="sepet.php" style="color: #a94442; font-weight: bold;">Sepete Dön</a></p>
            </div>
        <?php endif; ?>

        <div class="siparis-grid">
            
            <div class="siparis-formu">
                <h3>1. Teslimat Adresi</h3>
                <form action="siparis.php" method="POST">
                    <div class="form-grup-login">
                        <label for="teslimat_adresi">Açık Adres:</label>
                        <textarea id="teslimat_adresi" name="teslimat_adresi" rows="5" 
                                  placeholder="Mahalle, Sokak, No, Daire, İlçe / İl" 
                                  required><?php echo $_POST['teslimat_adresi'] ?? ''; ?></textarea>
                    </div>
                    
                    <h3 style="margin-top: 20px;">2. Ödeme Yöntemi</h3>
                    <div class="odeme-secenek">
                        <input type="radio" id="kapida" name="odeme" value="kapida" checked disabled>
                        <label for="kapida">Kapıda Ödeme (Sistem Aktif)</label>
                        <p style="font-size: 0.9rem; color: #777;">(Not: Proje kapsamında yalnızca "Kapıda Ödeme" varsayılmaktadır.)</p>
                    </div>
                    
                    <hr style="margin: 20px 0;">
                    
                    <button type="submit" class="buton-buyuk-yesil" style="width: 100%;" 
                            <?php if ($stok_hatasi) echo 'disabled'; ?>>
                        Siparişi Onayla (Toplam: <?php echo number_format($toplam_fiyat, 2); ?> TL)
                    </button>
                </form>
            </div>

            <div class="siparis-ozeti">
                <h3>Siparişinizdeki Ürünler (<?php echo count($sepet_urunleri); ?> çeşit)</h3>
                
                <ul class="ozet-liste">
                    <?php foreach ($sepet_urunleri as $urun): ?>
                    <li class="ozet-urun">
                        <img src="<?php echo htmlspecialchars($urun['resim_url']); ?>" alt="">
                        <div class="ozet-urun-bilgi">
                            <strong><?php echo htmlspecialchars($urun['ad']); ?></strong>
                            <span>Adet: <?php echo $urun['adet']; ?></span>
                        </div>
                        <div class="ozet-urun-fiyat">
                            <?php echo number_format($urun['ara_toplam'], 2); ?> TL
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <hr>
                
                <div class="ozet-toplam" style="margin-bottom: 0;">
                    <span>TOPLAM</span>
                    <span><?php echo number_format($toplam_fiyat, 2); ?> TL</span>
                </div>
            </div>

        </div> </div> </main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>
</body>
</html>