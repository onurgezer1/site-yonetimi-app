# cPanel Shared Hosting Kurulum Rehberi

Bu rehber, SmartYonetim uygulamasını cPanel ile yönetilen shared hosting hesaplarına kurmak için özel olarak hazırlanmıştır.

## 📋 Ön Gereksinimler

### Hosting Gereksinimleri
- **PHP Version**: 8.2 veya üzeri
- **MySQL**: 5.7 veya üzeri
- **Disk Space**: Minimum 500MB
- **SSL Certificate**: Ücretsiz Let's Encrypt veya ücretli SSL
- **Cron Jobs**: Desteklenmeli (aylık aidat oluşturma için)

### Gerekli PHP Extensions
cPanel'de "Select PHP Version" bölümünden şu uzantıları aktif edin:
- ✅ pdo_mysql
- ✅ mbstring
- ✅ openssl
- ✅ tokenizer
- ✅ xml
- ✅ ctype
- ✅ json
- ✅ bcmath
- ✅ curl
- ✅ fileinfo
- ✅ gd
- ✅ zip

## 🚀 Adım Adım Kurulum

### 1. Dosyaları Yükleme

#### a) File Manager ile Yükleme
1. cPanel'de "File Manager"'ı açın
2. `public_html` klasörüne gidin
3. SmartYonetim ZIP dosyasını yükleyin
4. ZIP dosyasını açın (Extract)
5. Dosyaları düzenleyin (aşağıdaki klasör yapısına göre)

#### b) FTP ile Yükleme
```bash
# FTP client (FileZilla vb.) ile bağlanın
# Dosyaları şu şekilde organize edin:

/public_html/
  ├── index.php (public klasöründen taşınmış)
  ├── .htaccess (public klasöründen taşınmış)
  ├── assets/ (public/assets klasörü)
  ├── css/ (public/css klasörü)
  ├── js/ (public/js klasörü)
  └── images/ (public/images klasörü)

/domains/yourdomain.com/ (public_html'in üst klasörü)
  ├── app/
  ├── bootstrap/
  ├── config/
  ├── database/
  ├── resources/
  ├── routes/
  ├── storage/
  ├── vendor/
  ├── .env
  ├── composer.json
  └── artisan
```

### 2. index.php Dosyasını Düzenleme

`public_html/index.php` dosyasını şu şekilde düzenleyin:

```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Yolları shared hosting için güncelleyin
if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

### 3. MySQL Veritabanı Kurulumu

#### a) Veritabanı Oluşturma
1. cPanel'de "MySQL Databases" bölümünü açın
2. Yeni veritabanı oluşturun: `smartyonetim`
3. Veritabanı kullanıcısı oluşturun
4. Kullanıcıyı veritabanına bağlayın (All Privileges)

#### b) Veritabanı Bilgilerini Kaydetme
```
Veritabanı Adı: cpanel_user_smartyonetim
Kullanıcı Adı: cpanel_user_smart
Şifre: [güçlü şifre oluşturun]
Host: localhost
```

### 4. Environment (.env) Dosyası Kurulumu

`.env.production` dosyasını `.env` olarak yeniden adlandırın ve düzenleyin:

```env
APP_NAME="SmartYonetim"
APP_ENV=production
APP_KEY=base64:[BURAYA_KEY_GENERATE_EDİN]
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Veritabanı ayarları (cPanel'den aldığınız bilgiler)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=cpanel_user_smartyonetim
DB_USERNAME=cpanel_user_smart
DB_PASSWORD=your_database_password

# Shared hosting için cache ayarları
CACHE_STORE=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file

# Mail ayarları (hosting sağlayıcınızın SMTP bilgileri)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SmartYonetim"
```

### 5. Composer Dependencies Yükleme

#### a) Terminal Erişimi Varsa:
```bash
cd /home/username/domains/yourdomain.com
composer install --optimize-autoloader --no-dev
```

#### b) Terminal Erişimi Yoksa:
1. Yerel bilgisayarınızda `composer install --optimize-autoloader --no-dev` çalıştırın
2. `vendor` klasörünü FTP ile sunucuya yükleyin

### 6. Application Key Generate

cPanel Terminal'de veya SSH ile:
```bash
php artisan key:generate
```

### 7. Storage Klasörü İzinleri

File Manager'dan şu klasörlere 755 izni verin:
- `storage/` ve alt klasörleri
- `bootstrap/cache/`

### 8. Veritabanı Migration

```bash
# Terminal veya SSH ile
cd /home/username/domains/yourdomain.com
php artisan migrate --force
```

### 9. Application Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. Cron Jobs Kurulumu

cPanel'de "Cron Jobs" bölümünden şu job'u ekleyin:

**Dakika**: `*`  
**Saat**: `*`  
**Gün**: `*`  
**Ay**: `*`  
**Haftanın Günü**: `*`  

**Komut**:
```bash
cd /home/username/domains/yourdomain.com && php artisan schedule:run >> /dev/null 2>&1
```

### 11. SSL Certificate Kurulumu

#### Let's Encrypt (Ücretsiz):
1. cPanel'de "SSL/TLS" bölümünü açın
2. "Let's Encrypt" seçin
3. Domain'inizi seçip sertifika oluşturun

#### Force HTTPS Redirect:
`public_html/.htaccess` dosyasının başına ekleyin:
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🔧 Özel Ayarlar

### PHP.ini Ayarları

cPanel'de "MultiPHP INI Editor" ile şu ayarları yapın:

```ini
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
post_max_size = 64M
upload_max_filesize = 64M
max_file_uploads = 20
```

### Error Handling

Shared hosting'de debug modunu kapatın:
```env
APP_DEBUG=false
LOG_LEVEL=error
```

### File Permissions Kontrolü

File Manager'dan kontrol edin:
- Klasörler: 755
- Dosyalar: 644
- `.env` dosyası: 600 (güvenlik için)

## 📧 Email Kurulumu

### cPanel Email Accounts

1. cPanel'de "Email Accounts" oluşturun
2. `noreply@yourdomain.com` hesabını oluşturun
3. SMTP ayarlarını `.env` dosyasına ekleyin

### SMTP Test

```bash
# Terminal'de test edin
php artisan tinker
Mail::raw('Test mesajı', function ($message) {
    $message->to('test@example.com')->subject('SMTP Test');
});
```

## 🚨 Yaygın Sorunlar ve Çözümleri

### 1. "Internal Server Error" (500)

**Çözüm:**
1. `storage` klasörü izinlerini kontrol edin (755)
2. `.env` dosyası varlığını kontrol edin
3. Error log'ları kontrol edin:
   ```bash
   tail -f /home/username/public_html/storage/logs/laravel.log
   ```

### 2. "Database Connection Error"

**Çözüm:**
1. cPanel'de veritabanı bilgilerini doğrulayın
2. `.env` dosyasındaki DB ayarlarını kontrol edin
3. Veritabanı kullanıcı izinlerini kontrol edin

### 3. "Class Not Found" Hataları

**Çözüm:**
```bash
composer dump-autoload --optimize
php artisan config:clear
php artisan cache:clear
```

### 4. "Storage Link" Problemi

**Çözüm:**
```bash
php artisan storage:link
```

### 5. CSS/JS Dosyaları Yüklenmiyor

**Çözüm:**
1. `public` klasöründeki asset dosyalarının doğru yere taşındığını kontrol edin
2. `.htaccess` dosyasının `public_html`'de olduğunu kontrol edin

## ✅ Kurulum Doğrulama

### 1. Temel Test
- Ana sayfayı ziyaret edin: `https://yourdomain.com`
- Kayıt sayfasını test edin: `https://yourdomain.com/register`

### 2. Database Test
```bash
php artisan tinker
DB::connection()->getPdo();
```

### 3. Cache Test
```bash
php artisan config:show
```

### 4. Queue Test (Cron Job)
```bash
php artisan queue:work --once
```

## 🔄 Güncelleme Prosedürü

1. **Backup Alın**:
   - Veritabanını export edin
   - Dosyaları backup alın

2. **Yeni Dosyaları Yükleyin**:
   - Yeni sürüm dosyalarını upload edin
   - `.env` dosyasını koruyun

3. **Migration Çalıştırın**:
   ```bash
   php artisan migrate --force
   ```

4. **Cache Temizleyin**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

## 📊 Performans Optimizasyonu

### 1. OPcache (Hosting sağlayıcı desteği gerekir)
cPanel'de "MultiPHP INI Editor" ile:
```ini
opcache.enable=1
opcache.memory_consumption=128
```

### 2. File Cache
```env
CACHE_STORE=file
SESSION_DRIVER=file
```

### 3. Database Optimization
- Düzenli olarak `OPTIMIZE TABLE` çalıştırın
- İndeksleri kontrol edin

## 📞 Destek

cPanel kurulumu sırasında sorun yaşarsanız:

1. **Hosting Sağlayıcı Desteği**: PHP versiyonu, uzantılar ve izinler için
2. **SmartYonetim Desteği**: destek@smartyonetim.com
3. **Dokümantasyon**: GitHub repository README.md

---

Bu rehberi takip ederek SmartYonetim'i cPanel shared hosting hesabınıza başarılı bir şekilde kurabilirsiniz.