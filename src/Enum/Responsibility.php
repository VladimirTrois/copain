<?php

// src/Enum/Responsibility.php

namespace App\Enum;

enum Responsibility: string
{
    case OWNER = 'OWNER';
    case MANAGER = 'MANAGER';
    case SELLER = 'SELLER';
    case BAKER = 'BAKER';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
