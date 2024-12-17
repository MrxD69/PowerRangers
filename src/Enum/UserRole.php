<?php

namespace App\Enum;

enum UserRole: string {
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_FREELANCER = 'ROLE_FREELANCER';
    case ROLE_CLIENT = 'ROLE_CLIENT';
}

