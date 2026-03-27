<?php

namespace App\Enums;

enum RoleLevel: int
{
    case STAFF = 10;
    case CSIRT = 50;
    case ADMIN = 100;

    /**
     * Get the human readable label for the given RoleLevel.
     */
    public function label(): string
    {
        return match($this) {
            self::STAFF => 'Staff',
            self::CSIRT => 'CSIRT Responder',
            self::ADMIN => 'Administrator',
        };
    }
}
