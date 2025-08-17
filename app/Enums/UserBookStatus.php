<?php

namespace App\Enums;

enum UserBookStatus: string
{
    case PlanToRead = 'Plan to Read';
    case Reading = 'Reading';
    case Read = 'Read';
    case OnHold = 'On Hold';
    case Dropped = 'Dropped';

    public static function values(): array
    {
        return array_map(fn ($status) => $status->value, self::cases());
    }

    public static function names(): array
    {
        return array_map(fn ($status) => $status->name, self::cases());
    }

    public static function options(): array
    {
        return array_map(
            fn ($status) => [
                'value' => $status->name,
                'label' => $status->value,
            ],
            self::cases()
        );
    }
}
