@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600">Hoş geldiniz, {{ auth()->user()->name }}</p>
                </div>
                <div class="text-sm text-gray-500">
                    {{ now()->format('d.m.Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        @if(auth()->user()->role === 'admin')
            <!-- Admin Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_sites'] ?? 0 }}</p>
                            <p class="text-gray-600">Toplam Site</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['active_sites'] ?? 0 }}</p>
                            <p class="text-gray-600">Aktif Site</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_users'] ?? 0 }}</p>
                            <p class="text-gray-600">Toplam Kullanıcı</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7zm0 0V5a2 2 0 012-2h6l2 2h6a2 2 0 012 2v2M7 13h10l-4-8H7l4 8z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_apartments'] ?? 0 }}</p>
                            <p class="text-gray-600">Toplam Daire</p>
                        </div>
                    </div>
                </div>
            </div>

        @elseif(auth()->user()->role === 'site_yoneticisi')
            <!-- Site Manager Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Site Bilgisi</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $statistics['site_name'] ?? 'Site atanmamış' }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Daire Doluluk Oranı</h3>
                    <p class="text-2xl font-bold text-green-600">%{{ number_format($statistics['occupancy_rate'] ?? 0, 1) }}</p>
                    <p class="text-sm text-gray-600">{{ $statistics['occupied_apartments'] ?? 0 }}/{{ $statistics['total_apartments'] ?? 0 }} daire</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aylık Gelir</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($statistics['monthly_income'] ?? 0, 2, ',', '.') }} TL</p>
                </div>
            </div>

        @elseif(auth()->user()->role === 'sakin')
            <!-- Resident Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Daire Bilgisi</h3>
                    <p class="text-xl font-bold text-blue-600">{{ $statistics['apartment'] ?? 'Daire atanmamış' }}</p>
                    <p class="text-sm text-gray-600">{{ $statistics['site_name'] ?? '' }}</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Bekleyen Aidat</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $statistics['pending_aidats'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">{{ number_format($statistics['total_debt'] ?? 0, 2, ',', '.') }} TL borç</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aylık Aidat</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($statistics['monthly_aidat'] ?? 0, 2, ',', '.') }} TL</p>
                </div>
            </div>

        @endif

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Hızlı İşlemler</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'site_yoneticisi')
                        <a href="{{ route('sites.index') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-medium text-gray-900">Site Yönetimi</span>
                        </a>

                        <a href="{{ route('aidats.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16a2 2 0 002 2z"/>
                            </svg>
                            <span class="font-medium text-gray-900">Aidat Yönetimi</span>
                        </a>
                    @endif

                    <a href="{{ route('payments.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="font-medium text-gray-900">Ödemeler</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                        <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="font-medium text-gray-900">Profil</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Son Aktiviteler</h2>
            </div>
            <div class="p-6">
                <div class="text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p>Henüz aktivite bulunmamaktadır.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection