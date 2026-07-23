<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentMode: string implements HasLabel
{
    case FileUpload = 'file_upload';
    case AiArticle = 'ai_article';
    case None = 'none';

    public function getLabel(): string
    {
        return match ($this) {
            self::FileUpload => 'Dosya yükleme',
            self::AiArticle => 'AI makale',
            self::None => 'Yok',
        };
    }
}
