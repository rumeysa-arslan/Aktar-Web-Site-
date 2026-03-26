<?php 
// 1. Veritabanı bağlantısı
include 'db_baglanti.php';

$page_title = "Alışveriş Sepeti";
$site_adi = "Aktarhane";

// 2. Sepetteki ürünleri ve detayları veritabanından al
$sepet_urunleri = [];
$toplam_fiyat = 0;

// Sepet boş değilse
if (!empty($_SESSION['sepet'])) {
    try {
        // Sepetteki tüm urun_id'leri al (örn: [3, 5, 1])
        $urun_idler = array_keys($_SESSION['sepet']);
        
        // SQL IN() sorgusu için yer tutucuları hazırla (örn: ?,?,?)
        $yer_tutucular = implode(',', array_fill(0, count($urun_idler), '?'));
        
        // Sepetteki ID'lere uyan tüm ürünleri ÇEK
        $stmt = $conn->prepare("SELECT * FROM urunler WHERE id IN ($yer_tutucular)");
        $stmt->execute($urun_idler);
        $db_urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sepetteki adet bilgisiyle veritabanı bilgisini birleştir
        foreach ($db_urunler as $urun) {
            $urun_id = $urun['id'];
            $adet = $_SESSION['sepet'][$urun_id]; // Session'dan adedi al
            
            // Stok kontrolü (Sepete eklendikten sonra stok azalmışsa)
            if ($adet > $urun['stok']) {
                $adet = $urun['stok']; // Stokla sınırla
                $_SESSION['sepet'][$urun_id] = $adet; // Session'ı da düzelt
            }
            
            // Adet 0 ise sepetten çıkar
            if ($adet <= 0) {
                unset($_SESSION['sepet'][$urun_id]);
                continue; // Bu ürünü listeleme
            }

            $urun['adet'] = $adet;
            $urun['ara_toplam'] = $urun['fiyat'] * $adet;
            
            $sepet_urunleri[] = $urun; // Listeleme dizisine ekle
            $toplam_fiyat += $urun['ara_toplam'];
        }

    } catch (PDOException $e) {
        echo "Sepet getirilirken hata oluştu: " . $e->getMessage();
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
            <?php $sepet_urun_sayisi = count($_SESSION['sepet']); ?>
            <li><a href="sepet.php" class="active">Sepet (<?php echo $sepet_urun_sayisi; ?>)</a></li>
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
        <h1>Alışveriş Sepeti</h1>
        
        <?php if (empty($sepet_urunleri)): ?>
            <div class="sepet-bos">
                <p>Sepetinizde henüz ürün bulunmamaktadır.</p>
                <a href="urunler.php" class="buton-buyuk-yesil">Alışverişe Başla</a>
            </div>

        <?php else: ?>
            <div class="sepet-icerik-grid">
                
                <div class="sepet-listesi">
                    <table class="sepet-tablosu">
                        <thead>
                            <tr>
                                <th colspan="2">Ürün</th>
                                <th>Fiyat</th>
                                <th>Adet</th>
                                <th>Ara Toplam</th>
                                <th>Kaldır</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sepet_urunleri as $urun): ?>
                            <tr>
                                <td style="width: 100px;">
                                    <img src="<?php echo htmlspecialchars($urun['resim_url']); ?>" alt="">
                                </td>
                                <td>
                                    <a href="urun_detay.php?id=<?php echo $urun['id']; ?>">
                                        <?php echo htmlspecialchars($urun['ad']); ?>
                                    </a>
                                </td>
                                <td><?php echo number_format($urun['fiyat'], 2); ?> TL</td>
                                
                                <td>
                                    <form action="sepet_islemler.php" method="POST" class="adet-form">
                                        <input type="hidden" name="islem" value="guncelle">
                                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                        <input type="number" name="adet" value="<?php echo $urun['adet']; ?>" 
                                               min="0" max="<?php echo $urun['stok']; ?>" class="adet-input">
                                        <button type="submit">Güncelle</button>
                                    </form>
                                </td>
                                
                                <td><?php echo number_format($urun['ara_toplam'], 2); ?> TL</td>
                                
                                <td>
                                    <form action="sepet_islemler.php" method="POST">
                                        <input type="hidden" name="islem" value="sil">
                                        <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                        <button type="submit" class="sil-btn">X</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="sepet-ozeti">
                    <h3>Sipariş Özeti</h3>
                    <div class="ozet-satir">
                        <span>Ara Toplam</span>
                        <span><?php echo number_format($toplam_fiyat, 2); ?> TL</span>
                    </div>
                    <div class="ozet-satir">
                        <span>Kargo</span>
                        <span>ÜCRETSİZ</span>
                    </div>
                    <hr>
                    <div class="ozet-toplam">
                        <span>TOPLAM</span>
                        <span><?php echo number_format($toplam_fiyat, 2); ?> TL</span>
                    </div>

                    <?php if (isset($_SESSION['kullanici_id'])): ?>
                        <a href="siparis.php" class="buton-buyuk-yesil">Siparişi Tamamla</a>
                    <?php else: ?>
                        <p style="margin-top: 15px; text-align: center; color: #777;">
                            Siparişi tamamlamak için lütfen
                            <a href="login.php" style="font-weight: bold;">giriş yapın</a>
                            veya <a href="register.php" style="font-weight: bold;">kayıt olun</a>.
                        </p>
                    <?php endif; ?>
                    
                </div>
            </div> <?php endif; ?>

    </div> </main>

<footer class="main-footer">
    <p>&copy; <?php echo date("Y"); ?> <?php echo $site_adi; ?>. Tüm hakları saklıdır.</p>
</footer>

</body>
</html>