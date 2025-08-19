# SmartYonetim - Site ve Apartman Yönetim SaaS Platformu

SmartYonetim, apartman ve site yönetimini kolaylaştırmak için şeffaf, düşük maliyetli, kullanıcı dostu bir SaaS platformudur. Apsiyon.com'un eksiklerini gidererek daha iyi bir çözüm sunar.

## 🚀 Özellikler

### ✨ Temel Özellikler
- **Şeffaf Faturalandırma**: Sıfır ekstra ücret politikası
- **24/7 Destek**: Canlı chat ve hızlı yanıt garantisi  
- **KVKK Uyumlu**: Tam veri koruma compliance
- **Modern UI/UX**: Toast bildirimleri, otomatik scroll
- **Mobil Uyumlu**: Responsive tasarım

### 🏢 Yönetim Özellikleri
- Aidat yönetimi ve otomatik hesaplama
- Ödeme takibi ve entegrasyonları
- Gider yönetimi ve raporlama
- Sakin ve daire yönetimi
- Bildirim sistemi

## 🛠 Teknoloji Stack

### Backend
- **PHP 8.3** + **Laravel 11**
- **PostgreSQL** veritabanı
- **Redis** cache ve queue
- **Laravel Sanctum** authentication

### Frontend
- **Blade Templates** + **Vue.js 3**
- **Tailwind CSS** styling
- **Vite** build tool

### DevOps
- **Docker** containerization
- **GitHub Actions** CI/CD
- **Laravel Forge** deployment

## 🚀 Kurulum

### Gereksinimler
- PHP 8.3+
- Node.js 20+
- PostgreSQL 16+
- Redis 7+
- Composer

### Geliştirme Ortamı (Docker ile)

```bash
# Repository'i klonlayın
git clone https://github.com/onurgezer1/site-yonetimi-app.git
cd site-yonetimi-app

# Environment dosyasını kopyalayın
cp .env.example .env

# Docker container'ları başlatın
docker-compose up -d

# Bağımlılıkları yükleyin
docker-compose exec app composer install
docker-compose exec node npm install

# Uygulama anahtarını generate edin
docker-compose exec app php artisan key:generate

# Migrations çalıştırın
docker-compose exec app php artisan migrate

# Assets'leri build edin
docker-compose exec node npm run dev
```

### Manuel Kurulum

```bash
# Repository'i klonlayın
git clone https://github.com/onurgezer1/site-yonetimi-app.git
cd site-yonetimi-app

# PHP bağımlılıklarını yükleyin
composer install

# Node.js bağımlılıklarını yükleyin
npm install

# Environment dosyasını ayarlayın
cp .env.example .env
php artisan key:generate

# Veritabanını ayarlayın (.env dosyasında)
php artisan migrate

# Assets'leri build edin
npm run dev

# Sunucuyu başlatın
php artisan serve
```

## 📊 Kullanıcı Rolleri

### 🔐 Admin
- Sistem geneli yönetimi
- Tüm sitelere erişim
- Kullanıcı yönetimi

### 🏢 Site Yöneticisi  
- Site bilgilerini yönetme
- Daire ve sakin yönetimi
- Aidat ve gider takibi
- Raporlama

### 🏠 Sakin
- Kendi aidat bilgilerini görme
- Ödeme yapma
- Bildirimleri alma

### 👥 Firma Çalışanı
- Çoklu site yönetimi
- Profesyonel araçlar
- Gelişmiş raporlama

## 🧪 Test

```bash
# Tüm testleri çalıştır
php artisan test

# Specific test sınıfını çalıştır
php artisan test --filter=SiteTest

# Code coverage ile
php artisan test --coverage
```

## 🔧 Geliştirme

### Code Quality
```bash
# PHP CS Fixer
composer run cs-fix

# PHPStan analiz
composer run phpstan

# Security check
composer audit
```

### Asset Development
```bash
# Development watch mode
npm run dev

# Production build
npm run build
```

## 📝 API Dokümantasyonu

API endpoints Laravel Sanctum ile korunmaktadır.

### Authentication
```
POST /api/login
POST /api/register
POST /api/logout
```

### Sites
```
GET /api/sites
POST /api/sites
GET /api/sites/{id}
PUT /api/sites/{id}
DELETE /api/sites/{id}
```

### Aidats
```
GET /api/aidats
POST /api/aidats/generate-monthly
GET /api/aidats/statistics
```

## 🚀 Deployment

### Laravel Forge ile
1. GitHub repository'yi bağlayın
2. Environment variables'ı ayarlayın
3. Deployment script'i çalıştırın

### Manuel Deployment
```bash
# Production için optimize edin
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔒 Güvenlik

- Rate limiting uygulanmıştır
- CSRF koruması aktiftir
- Input validation katmanları mevcuttur
- KVKK uyumlu veri şifreleme
- Güvenlik başlıkları konfigüre edilmiştir

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'feat: add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request açın

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## 📞 Destek

- 📧 Email: destek@smartyonetim.com
- 💬 Canlı Destek: https://smartyonetim.com/destek
- 📖 Dokümantasyon: https://docs.smartyonetim.com

## 🗺 Roadmap

- [ ] Mobile uygulama (Flutter)
- [ ] AI destekli hata denetimi
- [ ] IoT cihaz entegrasyonları  
- [ ] Gelişmiş raporlama araçları
- [ ] Muhasebe yazılımı entegrasyonları
- [ ] Multi-tenant mimarisi