<?php

namespace App\Enums;

enum GoodreadsImportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isActive(): bool
    {
        return match ($this) {
            self::Pending, self::Processing => true,
            self::Completed, self::Failed => false,
        };
    }
}
