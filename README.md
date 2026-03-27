# 🌿 Aktarhane - Online Satış Web Sitesi

Aktarhane, geleneksel aktar ürünlerinin (bitkisel çaylar, baharatlar, şifalı bitkiler) dijital ortamda sergilenmesi ve satılması amacıyla geliştirilmiş bir **E-ticaret Web Uygulaması** projesidir. Bu proje, bilgisayar programcılığı bölümü vize sınavı kapsamında geliştirilmiştir.

## 🚀 Kullanılan Teknolojiler

Bu proje geliştirilirken aşağıdaki teknolojiler ve araçlar kullanılmıştır:

* **Frontend:** HTML5, CSS3 (Responsive Design)
* **Backend:** PHP (Dinamik veri işleme)
* **Veritabanı:** MySQL (İlişkisel veritabanı yapısı)
* **Geliştirme Ortamı:** Laragon & VS Code

## 🛠️ Kurulum Talimatları

Projeyi kendi yerel sunucunuzda çalıştırmak için şu adımları izleyin:

### 1. Dosyaların Hazırlanması
Laragon kullanıyorsanız, projeyi `C:\laragon\www\aktar` dizinine kopyalayın.

### 2. Veritabanı Kurulumu
1. Laragon üzerinden **MySQL** servisini başlatın.
2. Tarayıcıdan `localhost/phpmyadmin` (veya HeidiSQL) adresine gidin.
3. `aktar_satis` adında yeni bir veritabanı oluşturun.
4. Aşağıdaki SQL komutlarını kullanarak tabloları oluşturun:

```sql
CREATE TABLE kategoriler (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(100) NOT NULL
);

CREATE TABLE urunler (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(255) NOT NULL,
    fiyat DECIMAL(10, 2) NOT NULL,
    stok INT(11) NOT NULL,
    aciklama TEXT,
    resim_yolu VARCHAR(255),
    kategori_id INT(11) NOT NULL
);
```


## 🚀 3. Uygulamayı Çalıştırma ve Test Etme

Proje dosyalarını ve veritabanını hazırladıktan sonra web sitesini yerel sunucunuzda ayağa kaldırmak için aşağıdaki adımları takip edin:

### **A. Servis Kontrolü**

Laragon kontrol panelini açın. **Apache** ve **MySQL** servislerinin yanında yeşil ışık yandığından (çalıştığından) emin olun.

### **B. Tarayıcı Üzerinden Erişim**

Web tarayıcınızı (Chrome, Edge vb.) açın ve adres çubuğuna şu yollardan birini yazın:

* **Yerel Sunucu Yolu:** `http://localhost/aktarhane`
* **Sanal Host Yolu:** `http://aktarhane.test` (Laragon otomatik tanımladıysa)

### **C. Sayfa ve Fonksiyon Kontrolü**

* **Anasayfa Yapısı:** `index.php` dosyasının sorunsuz yüklendiğini ve tasarımın (CSS) aktif olduğunu doğrulayın.
* **Dinamik Veri Akışı:** `urunler.php` sayfasına giderek, veritabanına eklediğiniz ürünlerin (Nane, Adaçayı vb.) listelendiğini kontrol edin.
