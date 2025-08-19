#!/usr/bin/env php
<?php

/**
 * SmartYonetim Server Requirements Checker
 * Bu script sunucunuzun SmartYonetim için uygun olup olmadığını kontrol eder
 */

echo "🔍 SmartYonetim Sunucu Uygunluk Kontrolü\n";
echo "==========================================\n\n";

$errors = [];
$warnings = [];
$passed = 0;
$total = 0;

function checkRequirement($name, $check, $required = true) {
    global $errors, $warnings, $passed, $total;
    $total++;
    
    if ($check) {
        echo "✅ {$name}\n";
        $passed++;
    } else {
        if ($required) {
            echo "❌ {$name} - ZORUNLU\n";
            $errors[] = $name;
        } else {
            echo "⚠️  {$name} - ÖNERİLEN\n";
            $warnings[] = $name;
        }
    }
}

// PHP Version Check
echo "📋 PHP Kontrolü:\n";
echo "================\n";

$phpVersion = PHP_VERSION;
$phpVersionCheck = version_compare($phpVersion, '8.2.0', '>=');

checkRequirement(
    "PHP Version (Mevcut: {$phpVersion}, Gerekli: 8.2+)",
    $phpVersionCheck
);

// PHP Extensions Check
echo "\n🔧 PHP Uzantıları:\n";
echo "==================\n";

$requiredExtensions = [
    'pdo' => true,
    'pdo_mysql' => false,
    'pdo_pgsql' => false,
    'mbstring' => true,
    'openssl' => true,
    'tokenizer' => true,
    'xml' => true,
    'ctype' => true,
    'json' => true,
    'bcmath' => true,
    'curl' => true,
    'fileinfo' => true,
    'gd' => false,
    'zip' => false,
    'redis' => false,
];

foreach ($requiredExtensions as $extension => $required) {
    checkRequirement(
        "PHP {$extension} extension",
        extension_loaded($extension),
        $required
    );
}

// Database Check
echo "\n💾 Veritabanı Kontrolü:\n";
echo "======================\n";

$hasMysql = extension_loaded('pdo_mysql');
$hasPgsql = extension_loaded('pdo_pgsql');

checkRequirement(
    "MySQL/MariaDB desteği (pdo_mysql)",
    $hasMysql,
    false
);

checkRequirement(
    "PostgreSQL desteği (pdo_pgsql)",
    $hasPgsql,
    false
);

if (!$hasMysql && !$hasPgsql) {
    $errors[] = "En az bir veritabanı sürücüsü (MySQL veya PostgreSQL) gerekli";
}

// Function Checks
echo "\n⚙️  PHP Fonksiyon Kontrolü:\n";
echo "============================\n";

$requiredFunctions = [
    'exec',
    'shell_exec',
    'proc_open',
    'file_get_contents',
    'curl_init',
];

foreach ($requiredFunctions as $function) {
    checkRequirement(
        "PHP {$function} fonksiyonu",
        function_exists($function),
        $function === 'file_get_contents' || $function === 'curl_init'
    );
}

// Memory and Limits
echo "\n🎯 PHP Ayarları:\n";
echo "================\n";

$memoryLimit = ini_get('memory_limit');
$memoryLimitBytes = return_bytes($memoryLimit);
$recommendedMemory = 128 * 1024 * 1024; // 128MB

checkRequirement(
    "Memory Limit (Mevcut: {$memoryLimit}, Önerilen: 128M+)",
    $memoryLimitBytes >= $recommendedMemory,
    false
);

$maxExecutionTime = ini_get('max_execution_time');
checkRequirement(
    "Max Execution Time (Mevcut: {$maxExecutionTime}s, Önerilen: 60s+)",
    $maxExecutionTime == 0 || $maxExecutionTime >= 60,
    false
);

$uploadMaxFilesize = ini_get('upload_max_filesize');
$uploadMaxBytes = return_bytes($uploadMaxFilesize);
$recommendedUpload = 32 * 1024 * 1024; // 32MB

checkRequirement(
    "Upload Max Filesize (Mevcut: {$uploadMaxFilesize}, Önerilen: 32M+)",
    $uploadMaxBytes >= $recommendedUpload,
    false
);

// Write Permissions
echo "\n📁 Dosya İzinleri:\n";
echo "==================\n";

$writableDirectories = [
    __DIR__ . '/storage',
    __DIR__ . '/bootstrap/cache',
];

foreach ($writableDirectories as $dir) {
    $writable = is_dir($dir) && is_writable($dir);
    $dirName = basename($dir);
    
    checkRequirement(
        "{$dirName}/ klasörü yazılabilir",
        $writable,
        true
    );
}

// Web Server Detection
echo "\n🌐 Web Sunucusu:\n";
echo "================\n";

$webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
echo "Tespit edilen: {$webServer}\n";

// Composer Check
echo "\n📦 Araç Kontrolü:\n";
echo "=================\n";

$composerExists = shell_exec('which composer') !== null;
checkRequirement(
    "Composer kurulu",
    $composerExists,
    true
);

// Summary
echo "\n📊 ÖZET:\n";
echo "========\n";

echo "Başarılı kontroller: {$passed}/{$total}\n";

if (!empty($errors)) {
    echo "\n❌ KRİTİK SORUNLAR:\n";
    foreach ($errors as $error) {
        echo "   • {$error}\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  UYARILAR:\n";
    foreach ($warnings as $warning) {
        echo "   • {$warning}\n";
    }
}

echo "\n";

if (empty($errors)) {
    echo "🎉 Sunucunuz SmartYonetim için uygundur!\n";
    echo "Kuruluma devam edebilirsiniz: ./deploy.sh\n";
} else {
    echo "🚫 Lütfen kritik sorunları çözdükten sonra tekrar deneyin.\n";
}

echo "\n📚 Detaylı kurulum rehberi için:\n";
echo "   • PHP-SUNUCU-KURULUM.md (VPS/Dedicated)\n";
echo "   • CPANEL-KURULUM.md (Shared Hosting)\n\n";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}