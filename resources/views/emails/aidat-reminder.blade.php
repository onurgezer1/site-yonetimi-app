<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aidat Hatırlatması</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th,
        .details-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .details-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">SmartYonetim</div>
            <p>Site ve Apartman Yönetim Sistemi</p>
        </div>

        @if($isOverdueReminder)
            <div class="alert alert-warning">
                <strong>⚠️ DİKKAT:</strong> Aidat borcunuz gecikmiştir. Lütfen en kısa sürede ödemenizi yapınız.
            </div>
        @else
            <div class="alert alert-info">
                <strong>📅 HATIRLATMA:</strong> Aidat ödeme tarihiniz yaklaşmaktadır.
            </div>
        @endif

        <h2>Sayın {{ $user->name }},</h2>
        
        <p>{{ $site->name }} sitesi için {{ $aidat->period }} dönemi aidat bilgileriniz aşağıdadır:</p>

        <table class="details-table">
            <tr>
                <th>Site</th>
                <td>{{ $site->name }}</td>
            </tr>
            <tr>
                <th>Daire</th>
                <td>{{ $apartment->full_identifier }}</td>
            </tr>
            <tr>
                <th>Dönem</th>
                <td>{{ $aidat->period }}</td>
            </tr>
            <tr>
                <th>Aidat Tutarı</th>
                <td>{{ number_format($aidat->amount, 2, ',', '.') }} TL</td>
            </tr>
            @if($aidat->late_fee > 0)
                <tr>
                    <th>Gecikme Faizi</th>
                    <td style="color: #dc3545;">{{ number_format($aidat->late_fee, 2, ',', '.') }} TL</td>
                </tr>
            @endif
            <tr>
                <th>Toplam Tutar</th>
                <td class="amount">{{ number_format($aidat->total_amount, 2, ',', '.') }} TL</td>
            </tr>
            <tr>
                <th>Son Ödeme Tarihi</th>
                <td style="color: {{ $isOverdueReminder ? '#dc3545' : '#28a745' }}; font-weight: bold;">
                    {{ $aidat->due_date->format('d.m.Y') }}
                    @if($isOverdueReminder)
                        ({{ $aidat->due_date->diffForHumans() }})
                    @endif
                </td>
            </tr>
            <tr>
                <th>Durum</th>
                <td>
                    @if($aidat->status === 'pending')
                        <span style="color: #ffc107; font-weight: bold;">Beklemede</span>
                    @elseif($aidat->status === 'late')
                        <span style="color: #dc3545; font-weight: bold;">Gecikmiş</span>
                    @endif
                </td>
            </tr>
        </table>

        <div style="text-align: center;">
            <a href="{{ route('aidats.show', $aidat) }}" class="btn">
                💳 Ödeme Yap
            </a>
        </div>

        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h4 style="color: #495057;">Ödeme Yöntemleri:</h4>
            <ul style="color: #6c757d;">
                <li>🏦 Banka Havalesi</li>
                <li>💳 Kredi Kartı</li>
                <li>💰 Nakit (Site yöneticisine)</li>
            </ul>
        </div>

        @if($isOverdueReminder)
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                <p><strong>Önemli:</strong> Geciken ödemeler için yasal faiz uygulanmaktadır. 
                Ödemenizi geciktirmemek için lütfen en kısa sürede ödemenizi yapınız.</p>
            </div>
        @endif

        <div class="footer">
            <p>Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayın.</p>
            <p>
                Sorularınız için: 
                <a href="mailto:destek@smartyonetim.com">destek@smartyonetim.com</a> |
                <a href="tel:+905001234567">0500 123 45 67</a>
            </p>
            <p style="margin-top: 10px;">
                <small>
                    © {{ date('Y') }} SmartYonetim. Tüm hakları saklıdır.<br>
                    Bu mesajı almak istemiyorsanız, site yöneticinize başvurunuz.
                </small>
            </p>
        </div>
    </div>
</body>
</html>