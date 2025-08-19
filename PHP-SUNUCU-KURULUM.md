# PHP Sunucu Kurulum Rehberi - SmartYonetim

Bu rehber, SmartYonetim uygulamasını çeşitli PHP sunucu ortamlarına nasıl kuracağınızı detaylı olarak açıklamaktadır.

## 📋 Sistem Gereksinimleri

### Minimum Gereksinimler
- **PHP**: 8.2 veya üzeri (Önerilen: 8.3)
- **MySQL**: 5.7 veya üzeri / **PostgreSQL**: 12 veya üzeri
- **Web Sunucusu**: Apache 2.4+ veya Nginx 1.18+
- **Composer**: 2.0 veya üzeri
- **SSL Sertifikası** (Üretim için zorunlu)

### Gerekli PHP Uzantıları
```bash
# Gerekli uzantılar
php-pdo
php-mbstring
php-openssl
php-tokenizer
php-xml
php-ctype
php-json
php-bcmath
php-curl
php-fileinfo
php-gd
php-zip

# MySQL kullanıyorsanız
php-mysql

# PostgreSQL kullanıyorsanız
php-pgsql

# Opsiyonel (performans için önerilen)
php-redis
php-opcache
```

## 🚀 Kurulum Seçenekleri

### 1. Otomatik Kurulum (Önerilen)

```bash
# 1. Projeyi indirin
git clone https://github.com/onurgezer1/site-yonetimi-app.git
cd site-yonetimi-app

# 2. Otomatik kurulum scriptini çalıştırın
./deploy.sh
```

### 2. Manuel Kurulum

#### Adım 1: Proje Dosyalarını Yükleme
```bash
# Git ile klonlama
git clone https://github.com/onurgezer1/site-yonetimi-app.git

# Veya ZIP dosyasını indirip açma
# Dosyaları web sunucunuzun klasörüne kopyalayın
```

#### Adım 2: Bağımlılıkları Yükleme
```bash
# PHP bağımlılıklarını yükleyin
composer install --optimize-autoloader --no-dev

# Frontend assets (Node.js varsa)
npm install --production
npm run build
```

#### Adım 3: Environment Konfigürasyonu
```bash
# Production environment dosyasını kopyalayın
cp .env.production .env

# Uygulama anahtarını generate edin
php artisan key:generate
```

#### Adım 4: .env Dosyasını Düzenleme
```bash
nano .env
```

**Önemli Ayarlar:**
```env
APP_NAME="SmartYonetim"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Veritabanı ayarları
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_secure_password

# Mail ayarları
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password
```

#### Adım 5: Veritabanı Kurulumu
```bash
# Veritabanı tablolarını oluşturun
php artisan migrate --force

# (Opsiyonel) Demo verileri için
php artisan db:seed
```

#### Adım 6: İzinleri Ayarlama
```bash
# Storage ve cache klasörleri için yazma izni
chmod -R 775 storage bootstrap/cache

# Web sunucusu kullanıcısına sahiplik verme
chown -R www-data:www-data storage bootstrap/cache
```

#### Adım 7: Uygulama Optimizasyonu
```bash
# Cache'leri oluşturun (production için)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🌐 Web Sunucusu Konfigürasyonu

### Apache Konfigürasyonu

**Virtual Host Örneği:**
```apache
<VirtualHost *:80>
    ServerName smartyonetim.com
    ServerAlias www.smartyonetim.com
    DocumentRoot /var/www/smartyonetim/public
    
    <Directory /var/www/smartyonetim/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/smartyonetim_error.log
    CustomLog ${APACHE_LOG_DIR}/smartyonetim_access.log combined
</VirtualHost>

# SSL Virtual Host (443 portu için)
<VirtualHost *:443>
    ServerName smartyonetim.com
    ServerAlias www.smartyonetim.com
    DocumentRoot /var/www/smartyonetim/public
    
    SSLEngine on
    SSLCertificateFile /path/to/ssl/cert.pem
    SSLCertificateKeyFile /path/to/ssl/private.key
    
    <Directory /var/www/smartyonetim/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Gerekli Apache Modülleri:**
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
```

### Nginx Konfigürasyonu

Proje klasöründeki `nginx.conf` dosyasını kullanın:
```bash
# Konfigürasyonu kopyalayın
sudo cp nginx.conf /etc/nginx/sites-available/smartyonetim
sudo ln -s /etc/nginx/sites-available/smartyonetim /etc/nginx/sites-enabled/

# Nginx'i yeniden başlatın
sudo nginx -t
sudo systemctl reload nginx
```

## 💾 Veritabanı Konfigürasyonu

### MySQL Konfigürasyonu
```sql
-- Veritabanı ve kullanıcı oluşturma
CREATE DATABASE smartyonetim CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'smartyonetim_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON smartyonetim.* TO 'smartyonetim_user'@'localhost';
FLUSH PRIVILEGES;
```

### PostgreSQL Konfigürasyonu
```sql
-- Veritabanı ve kullanıcı oluşturma
CREATE DATABASE smartyonetim;
CREATE USER smartyonetim_user WITH ENCRYPTED PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE smartyonetim TO smartyonetim_user;
```

## 🔐 SSL Kurulumu

### Let's Encrypt (Certbot)
```bash
# Certbot kurulumu (Ubuntu/Debian)
sudo apt install certbot python3-certbot-apache

# Apache için SSL sertifikası
sudo certbot --apache -d smartyonetim.com -d www.smartyonetim.com

# Nginx için SSL sertifikası
sudo certbot --nginx -d smartyonetim.com -d www.smartyonetim.com

# Otomatik yenileme
sudo crontab -e
# Şu satırı ekleyin:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

## 📊 cPanel Kurulumu

### 1. Dosya Yükleme
- SmartYonetim dosyalarını `public_html` klasörüne yükleyin
- `public` klasörünün içindeki tüm dosyaları `public_html`'e taşıyın
- Diğer klasörleri `public_html`'in bir üst klasörüne koyun

### 2. Index.php Dosyasını Düzenleme
```php
// public_html/index.php dosyasındaki yolları güncelleyin
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

### 3. cPanel Veritabanı Kurulumu
- cPanel'de "MySQL Databases" bölümünden veritabanı oluşturun
- Kullanıcı oluşturun ve veritabanına bağlayın
- `.env` dosyasında veritabanı bilgilerini güncelleyin

### 4. Cron Jobs Kurulumu
```bash
# cPanel Cron Jobs bölümünden ekleyin
* * * * * cd /home/username/domains/smartyonetim.com && php artisan schedule:run >> /dev/null 2>&1
```

## ⚡ Performans Optimizasyonu

### 1. OPcache Konfigürasyonu
```ini
; php.ini dosyasına ekleyin
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Redis Cache (Opsiyonel)
```bash
# Redis kurulumu
sudo apt install redis-server php-redis

# .env dosyasında
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 3. Queue Worker Kurulumu
```bash
# Systemd service oluşturun
sudo nano /etc/systemd/system/smartyonetim-worker.service
```

```ini
[Unit]
Description=SmartYonetim Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/smartyonetim/artisan queue:work --sleep=3 --tries=3 --max-time=3600
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

```bash
# Service'i aktifleştirin
sudo systemctl enable smartyonetim-worker
sudo systemctl start smartyonetim-worker
```

## 🛡️ Güvenlik Ayarları

### 1. Dosya İzinleri
```bash
# Güvenli izinler
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

### 2. Web Sunucusu Güvenlik Başlıkları
Nginx ve Apache konfigürasyonlarında güvenlik başlıkları zaten eklenmiştir.

### 3. Firewall Ayarları
```bash
# UFW ile port ayarları
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## 🧪 Test ve Doğrulama

### 1. Uygulama Testi
```bash
# Temel işlevsellik testi
php artisan route:list
php artisan config:show
php artisan queue:work --once
```

### 2. Web Tarayıcı Testi
- Ana sayfaya erişimi test edin
- Kayıt/giriş fonksiyonlarını test edin
- Admin paneline erişimi kontrol edin

## 🚨 Sorun Giderme

### Yaygın Hatalar ve Çözümleri

**1. 500 Internal Server Error**
```bash
# Log dosyalarını kontrol edin
tail -f storage/logs/laravel.log
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
```

**2. Database Connection Error**
- `.env` dosyasındaki veritabanı bilgilerini kontrol edin
- Veritabanı kullanıcı izinlerini doğrulayın
- `php artisan tinker` ile bağlantıyı test edin

**3. Permission Denied Hatları**
```bash
# İzinleri düzeltin
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**4. Composer/PHP Hatları**
```bash
# Composer cache'i temizleyin
composer clear-cache
composer install --optimize-autoloader --no-dev
```

## 📞 Destek

Kurulum sırasında sorun yaşarsanız:

- 📧 **Email**: destek@smartyonetim.com
- 📖 **Dokümantasyon**: [GitHub Issues](https://github.com/onurgezer1/site-yonetimi-app/issues)
- 🐛 **Hata Bildirimi**: GitHub Issues sayfasından bildirebilirsiniz

## ✅ Kurulum Sonrası Yapılacaklar

1. **SSL Sertifikası** kurulumu ve HTTP'den HTTPS'e yönlendirme
2. **Backup sistemi** kurulumu (veritabanı + dosyalar)
3. **Monitoring** araçları kurulumu
4. **Ödeme gateway** entegrasyonlarının test edilmesi
5. **SMS servisi** konfigürasyonu
6. **Mail servisi** test edilmesi
7. **KVKK compliance** ayarlarının kontrol edilmesi

---

Bu rehberi takip ederek SmartYonetim uygulamanızı başarılı bir şekilde PHP sunucunuza kurabilirsiniz. Herhangi bir sorunla karşılaştığınızda lütfen destek kanallarımızdan yardım isteyin.