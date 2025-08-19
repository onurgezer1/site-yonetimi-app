@extends('layouts.app')

@section('content')
<div class="min-h-screen">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                    SmartYonetim
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                    Site ve Apartman Yönetimi için Şeffaf, Güvenli ve Kullanıcı Dostu SaaS Platformu
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" 
                       class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300">
                        Hemen Başlayın
                    </a>
                    <a href="{{ route('login') }}" 
                       class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                        Giriş Yapın
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Neden SmartYonetim?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Apsiyon'un tüm eksiklerini giderip daha iyi bir çözüm sunuyoruz
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Şeffaf Faturalandırma</h3>
                    <p class="text-gray-600">
                        Sıfır ekstra ücret politikası ile şeffaf ve güvenilir ödeme sistemi
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition duration-300">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">24/7 Destek</h3>
                    <p class="text-gray-600">
                        Canlı chat ve hızlı yanıt garantili müşteri hizmetleri
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center p-6 rounded-lg border border-gray-200 hover:shadow-lg transition duration-300">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">KVKK Uyumlu</h3>
                    <p class="text-gray-600">
                        Tam KVKK compliance ile güvenli veri yönetimi
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">
                        Modern ve Kullanıcı Dostu Arayüz
                    </h2>
                    <ul class="space-y-4 text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Toast bildirimleri ile kaydet butonu sonrası uyarılar
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Otomatik scroll ve loading states
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Responsive tasarım ile mobil uyumluluk
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Kat Mülkiyeti Kanunu uyumlu aidat hesaplaması
                        </li>
                    </ul>
                </div>
                <div class="bg-white p-8 rounded-lg shadow-lg">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Özellikler</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
                        <div>✓ Aidat Yönetimi</div>
                        <div>✓ Ödeme Takibi</div>
                        <div>✓ Gider Yönetimi</div>
                        <div>✓ Raporlama</div>
                        <div>✓ Bildirimler</div>
                        <div>✓ Muhasebe Entegrasyonu</div>
                        <div>✓ AI Destekli Sistem</div>
                        <div>✓ IoT Entegrasyonu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 bg-blue-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white mb-4">
                Hemen Başlayın
            </h2>
            <p class="text-xl text-blue-100 mb-8">
                Site ve apartman yönetiminizi daha verimli hale getirin
            </p>
            <a href="{{ route('register') }}" 
               class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition duration-300 inline-block">
                Ücretsiz Deneme Başlatın
            </a>
        </div>
    </div>
</div>
@endsection