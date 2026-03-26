<?php
// Session'ı başlat (db_baglanti.php bunu zaten yapıyor)
include 'db_baglanti.php';

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session'ı sonlandır
session_unset();
session_destroy();

// Kullanıcıyı anasayfaya yönlendir
header("Location: index.php");
exit;
?>