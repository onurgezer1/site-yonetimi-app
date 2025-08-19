<?php

/**
 * SmartYonetim - Shared Hosting Entry Point
 * 
 * Bu dosya shared hosting ortamları için özel olarak hazırlanmıştır.
 * Normal Laravel index.php dosyasından farklı olarak, shared hosting
 * klasör yapısına uygun şekilde düzenlenmiştir.
 * 
 * Kullanım:
 * 1. Bu dosyayı public_html/ klasörüne kopyalayın
 * 2. Laravel dosyalarını public_html'in bir üst klasörüne koyun
 * 3. Aşağıdaki yolları hosting yapınıza göre güncelleyin
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Shared hosting için özel hata yakalama
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

/*
|--------------------------------------------------------------------------
| Laravel Application Path
|--------------------------------------------------------------------------
|
| Shared hosting ortamında Laravel dosyalarınızın bulunduğu klasörü
| buraya yazın. Örneğin: /home/username/laravel-app/
| 
| Güvenlik için Laravel dosyalarını public_html dışında tutun!
|
*/

$laravelPath = __DIR__ . '/../'; // public_html'in bir üst klasörü
// Alternatif yollar:
// $laravelPath = '/home/username/domains/yourdomain.com/';
// $laravelPath = $_SERVER['DOCUMENT_ROOT'] . '/../laravel/';

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists($laravelPath . 'storage/framework/maintenance.php')) {
    require $laravelPath . 'storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

if (!file_exists($laravelPath . 'vendor/autoload.php')) {
    die('
    <div style="font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa;">
        <h2 style="color: #dc3545;">❌ SmartYonetim Kurulum Hatası</h2>
        <p>Laravel dosyaları bulunamadı veya Composer bağımlılıkları yüklenmemiş.</p>
        <hr>
        <h3>Çözüm Adımları:</h3>
        <ol>
            <li><strong>Laravel dosyalarının yolunu kontrol edin:</strong><br>
                Mevcut yol: <code>' . htmlspecialchars($laravelPath) . '</code><br>
                Bu yolda <code>vendor/autoload.php</code> dosyası olmalı.
            </li>
            <li><strong>Composer bağımlılıklarını yükleyin:</strong><br>
                <code>composer install --optimize-autoloader --no-dev</code>
            </li>
            <li><strong>Dosya izinlerini kontrol edin:</strong><br>
                <code>chmod -R 755 vendor/</code>
            </li>
        </ol>
        <hr>
        <p><a href="mailto:destek@smartyonetim.com">Destek için iletişime geçin</a></p>
    </div>
    ');
}

require $laravelPath . 'vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once $laravelPath . 'bootstrap/app.php';

// Shared hosting için özel environment ayarları
if (!$app->environment('production')) {
    // Development ortamında hata gösterimi
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

/*
|--------------------------------------------------------------------------
| Shared Hosting Debugging (Sadece development için)
|--------------------------------------------------------------------------
|
| Sorun yaşıyorsanız, aşağıdaki kodu geçici olarak açabilirsiniz:
|
| echo '<pre>';
| echo 'PHP Version: ' . PHP_VERSION . "\n";
| echo 'Laravel Path: ' . $laravelPath . "\n";
| echo 'Document Root: ' . $_SERVER['DOCUMENT_ROOT'] . "\n";
| echo 'Current Directory: ' . __DIR__ . "\n";
| echo 'Vendor Autoload Exists: ' . (file_exists($laravelPath . 'vendor/autoload.php') ? 'YES' : 'NO') . "\n";
| echo '.env File Exists: ' . (file_exists($laravelPath . '.env') ? 'YES' : 'NO') . "\n";
| echo 'Bootstrap App Exists: ' . (file_exists($laravelPath . 'bootstrap/app.php') ? 'YES' : 'NO') . "\n";
| echo '</pre>';
|
*/