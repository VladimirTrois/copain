<?php

// src/Enum/Responsibility.php

namespace App\Enum;

enum Responsibility: string
{
    case OWNER = 'OWNER';
    case MANAGER = 'MANAGER';
    case SELLER = 'SELLER';
    case BAKER = 'BAKER';
}
