<?php

namespace App\Enums;

enum BookType: string
{
    case Physical = 'physical';
    case Digital = 'digital';
    case Audio = 'audio';
}
