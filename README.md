# 🍔 KC QR Menu & Order Management System

Modern, profesyonel ve tam kapsamlı bir restoran yönetim çözümü. Müşterileriniz için şık bir **QR Menü**, garsonlarınız için anlık bir **Yönetim Paneli** ve işletme sahipleri için detaylı bir **Admin Dashboard** sunar.

---

## ✨ Öne Çıkan Özellikler

- **📱 Gelişmiş QR Menü:** Müşterilerin telefonlarından ürünleri inceleyebileceği, kategori filtrelemeli modern bir arayüz.
- **👨‍🍳 Garson (Mutfak) Paneli:** Siparişlerin anlık düştüğü, durum güncelleme (Hazırlanıyor, Hazır, Teslim Edildi) özellikli operasyonel panel.
- **🔔 Real-Time Bildirimler:** Yeni sipariş geldiğinde, sayfayı yenilemeye gerek kalmadan çalan **Yemeksepeti tarzı sesli uyarı**.
- **🔢 Günlük Akıllı Sipariş Sayacı:** Her gün başında otomatik sıfırlanan, mutfak takibini kolaylaştıran sipariş numaralandırma sistemi.
- **📊 Admin Dashboard:** Ürün yönetimi, fiyat güncellemeleri, kategori düzenleme ve masa yönetimi için merkezi bir panel.
- **👥 Personel Yönetimi:** Admin ve Garson rolleri ile yetkilendirilmiş güvenli giriş sistemi.
- **📷 Görsel Yönetimi:** Ürün görsellerini sürükle-bırak yöntemiyle kolayca yükleme ve yönetme.

## 🛠 Kullanılan Teknolojiler

- **Backend:** PHP 8.x (Hızlı ve Güvenilir API Yapısı)
- **Veritabanı:** MySQL / PDO (Güvenli veri haberleşmesi)
- **Frontend:** Vanilla JavaScript (ES6+), Modern CSS3 (Grid & Flexbox)
- **Güvenlik:** Role-based Authentication, Password Hashing, CSRF Koruma altyapısı.

## � Kurulum ve Kullanım

1. **Dosyaları Aktarın:** Repoyu sunucunuza (MAMP/cPanel) indirin.
2. **Veritabanı:** MySQL üzerinden bir veritabanı oluşturun.
3. **Konfigürasyon:** `api/config.example.php` dosyasını `api/config.php` olarak kopyalayıp DB bilgilerinizi doldurun.
4. **Kurulum:** Tarayıcınızdan `install_auth.php` dosyasını çalıştırarak tabloları oluşturun.
5. **Güvenlik:** Kurulumdan sonra `install_auth.php` dosyasını sunucunuzdan silebilirsiniz.

**Sistem Giriş Bilgileri:**
- **Yönetici:** `admin` | Şifre: `admin`
- **Garson:** `garson` | Şifre: `garson`

---
## 📄 Lisans ve İletişim

Bu proje **Doğukan Koçan / Koçan Creative** tarafından geliştirilmiştir. Ticari kullanım ve özelleştirilmiş kurulum talepleriniz için lütfen [web sitemizi](https://kocancreative.com/) ziyaret edin.

> **Dipnot:** Bu repo, portfolyo amaçlı temel özellikleri içermektedir. Profesyonel kurulumlarda ek güvenlik ve performans optimizasyonları uygulanmaktadır.

