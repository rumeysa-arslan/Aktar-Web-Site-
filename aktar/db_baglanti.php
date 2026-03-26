<?php
// Laragon varsayılan ayarları
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aktar_satis"; // HeidiSQL'de oluşturduğumuz veritabanı

// Hata raporlamayı aç (Geliştirme aşamasında faydalıdır)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session'ı (Oturum) başlat
// Sepet ve kullanıcı girişi için gereklidir.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısını oluştur (PDO kullanarak - daha güvenli)
try {
    // $conn = new mysqli($servername, $username, $password, $dbname);
    
    // PDO (PHP Data Objects) MySQLi'den daha esnek ve güvenlidir (SQL injection'a karşı)
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    
    // Hata modunu ayarla
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Karakter setini (mysqli'deki set_charset gibi) tekrar garantileyim
    $conn->exec("SET NAMES 'utf8'");

} catch(PDOException $e) {
    // Bağlantı hatası olursa programı durdur ve hatayı göster
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

// Sepet session'ı yoksa boş bir dizi olarak oluştur
if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = [];
}
?>