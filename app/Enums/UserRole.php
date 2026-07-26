<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Customer = 'customer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editör',
            self::Customer => 'Müşteri',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Editor => 'warning',
            self::Customer => 'gray',
        };
    }

    public function isStaff(): bool
    {
        return $this === self::Admin || $this === self::Editor;
    }

    public function panelAccessDescription(): string
    {
        return match ($this) {
            self::Admin => 'Admin paneline tam erişim; site ayarları, kullanıcı yönetimi ve tüm kaynaklar.',
            self::Editor => 'Admin paneline erişim; sipariş/içerik operasyonu. Site ayarları ve panel kullanıcıları yok.',
            self::Customer => 'Yalnızca müşteri hesabı; admin paneline giremez.',
        };
    }
}
