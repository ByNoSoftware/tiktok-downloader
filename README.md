# TikTok Filigranlı Video İndirme Aracı

![TikTok Downloader](/screenshots/preview.png)

Bu proje, TikTok videolarını filigranlı olarak indirmek için modern bir web aracıdır. Kullanıcı dostu arayüzü ve gelişmiş özellikleriyle TikTok videolarını hızlı ve kolay bir şekilde indirmenizi sağlar.

## ✨ Özellikler

- 📹 **TikTok Video İndirme**: Kullanıcılar TikTok video URL'si girerek videoları indirebilir
- 🎵 **Ses İndirme**: Video ile beraber veya sadece ses dosyasını indirme seçeneği
- 🎨 **Karanlık/Aydınlık Tema**: Kullanıcı tercihine göre değişen tema seçeneği
- 📱 **Tam Responsive**: Mobil, tablet ve masaüstü uyumlu tasarım
- 🔄 **Alternatif API**: Ana API çalışmadığında otomatik olarak alternatif API'ye geçiş
- 👁️ **Filigranlı İndirme**: Filigranlı ve filigramsız indirme seçenekleri
- 📊 **Video İstatistikleri**: Beğeni, yorum ve paylaşım sayıları gösterimi

## 🛠️ Teknolojiler

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **API**: [RapidAPI TIKTOK](https://rapidapi.com/Lundehund/api/tiktok-api23)

## 📋 Gereksinimler

- PHP 7.2 veya üzeri
- cURL desteği
- HTTPS protokolü (API gereksinimleri için)

## ⚙️ Kurulum

1. Repo'yu klonlayın veya ZIP olarak indirin:
   ```bash
   git clone https://github.com/ByNoSoftware/tiktok-downloader.git
   ```

2. Dosyaları web sunucunuza yükleyin

3. `process.php` dosyasında API anahtarınızı güncelleyin (gerekirse):
   ```php
   $apiKey = "sizin-api-anahtariniz";
   ```

4. Hemen kullanmaya başlayın!

## 💡 Kullanım

1. TikTok'tan video URL'sini kopyalayın
2. URL'yi uygulama içindeki metin kutusuna yapıştırın
3. Filigranlı/filigransız ve HD kalite seçeneklerini belirleyin
4. "İndir" butonuna tıklayın
5. Video bilgileri yüklendikten sonra "Video İndir" veya "Ses İndir" seçeneklerini kullanın

## 🎨 Özelleştirme

### Renk Şeması Değiştirme

`style.css` dosyasında `:root` değişkenlerini düzenleyerek renk şemasını özelleştirebilirsiniz:

```css
:root {
    --primary-color: #fe2c55;  /* Ana renk - TikTok kırmızısı */
    --secondary-color: #25f4ee;  /* İkincil renk - TikTok turkuaz */
    --dark-color: #121212;  /* Koyu tema arka plan */
    --light-color: #ffffff;  /* Açık tema arka plan */
    --grey-color: #f7f7f7;  /* Gri tonları */
    --text-color: #333333;  /* Metin rengi */
}
```

### Logo Değiştirme

Logo, Font Awesome ikonu kullanılarak oluşturulmuştur. `index.html` dosyasında logo bölümünü değiştirebilirsiniz:

```html
<div class="logo">
    <i class="fab fa-tiktok"></i>
    <h1>TikTok İndirici</h1>
</div>
```

## 🔧 Sorun Giderme

### API Hataları

API yanıt hatası alırsanız:

1. API anahtarınızın geçerli olduğundan emin olun
2. Günlük veya aylık API sınırlarınızı kontrol edin
3. PHP hata günlüklerini kontrol edin
4. TikTok URL'sinin doğru formatını kullandığınızdan emin olun

### Video İndirilemiyor

Bazı videolar indirilemiyorsa:

1. Videonun gizli olup olmadığını kontrol edin
2. Bölgesel kısıtlamaları kontrol edin
3. Farklı bir video ile deneyin

## 📄 Lisans

Bu proje Creative Commons Attribution-NonCommercial 4.0 International License (CC BY-NC 4.0) Lisansı ile lisanslanmıştır - detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 🙏 Teşekkürler

- [Font Awesome](https://fontawesome.com/) - İkonlar için
- [RapidAPI](https://rapidapi.com/Lundehund/api/tiktok-api23) - TikTok API erişimi için

## 📧 İletişim

Sorularınız veya önerileriniz için:

- GitHub Issues: [Yeni bir issue oluşturun](https://github.com/ByNoSoftware/tiktok-downloader/issues/new)
- R10.net: [bnsware](https://www.r10.net/profil/154778-bnsware.html)
