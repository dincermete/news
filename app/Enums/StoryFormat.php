<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StoryFormat: string implements HasLabel
{
    case Post = 'post';
    case Story = 'story';

    public function getLabel(): string
    {
        return match ($this) {
            self::Post => 'Post',
            self::Story => 'Story',
        };
    }
}
