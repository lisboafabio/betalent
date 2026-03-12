<?php

namespace App\Domain\User\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case FINANCE = 'finance';
    case USER = 'user';
}
