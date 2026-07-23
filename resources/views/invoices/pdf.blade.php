<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Fatura {{ $invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Fatura</h1>
    <p class="muted">{{ $invoiceNumber }} — {{ $issuedAt->format('d.m.Y H:i') }}</p>

    <p>
        <strong>Müşteri:</strong> {{ $customerName ?? $order?->user?->name }}<br>
        <strong>E-posta:</strong> {{ $customerEmail ?? $order?->user?->email }}
    </p>

    @if ($billingProfile)
        <p>
            <strong>Fatura profili:</strong> {{ $billingProfile->displayName() }}<br>
            <strong>Vergi / TCKN:</strong> {{ $billingProfile->tax_id }}<br>
            @if ($billingProfile->tax_office)
                <strong>Vergi dairesi:</strong> {{ $billingProfile->tax_office }}<br>
            @endif
            <strong>Adres:</strong> {{ $billingProfile->address }}
        </p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Açıklama</th>
                <th>Tutar</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($orders ?? collect([$order])) as $lineOrder)
                <tr>
                    <td>
                        Sipariş #{{ $lineOrder->id }}
                        @if ($lineOrder->site)
                            — {{ $lineOrder->site->domain }}
                        @endif
                    </td>
                    <td>{{ number_format((float) $lineOrder->price, 2) }} {{ $lineOrder->currency?->value ?? $payment->currency?->value }}</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Ödeme toplamı</strong></td>
                <td><strong>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency?->value }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
