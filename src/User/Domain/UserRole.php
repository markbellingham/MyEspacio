<?php

namespace MyEspacio\User\Domain;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
}
