<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case BUSINESS_USER = 'business_user';
    case TESTER = 'tester';
    case DEVELOPER = 'developer';
}
