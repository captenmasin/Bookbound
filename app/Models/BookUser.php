<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BookUser extends Pivot
{
    protected $table = 'book_user';

    protected function casts(): array
    {
        return [
            'tags' => 'json',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }
}
