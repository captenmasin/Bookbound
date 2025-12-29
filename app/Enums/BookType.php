<?php

declare(strict_types=1);

namespace App\Enums;

enum BookType: string
{
    case HardBack = 'hardback';
    case PaperBack = 'paperback';
    case EBook = 'ebook';
    case AudioBook = 'audiobook';
    case Other = 'other';
}
