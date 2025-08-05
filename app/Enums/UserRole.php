<?php

namespace App\Enums;

/**
 * Enum representing user roles in the application.
 */
enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super-admin';
}
