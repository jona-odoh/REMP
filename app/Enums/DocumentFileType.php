<?php

namespace App\Enums;

enum DocumentFileType: string
{
    case PHOTO = 'photo';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';

    public function label(): string
    {
        return match ($this) {
            self::PHOTO => 'Photograph',
            self::VIDEO => 'Video Recording',
            self::AUDIO => 'Audio Recording',
            self::DOCUMENT => 'Document / PDF',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
