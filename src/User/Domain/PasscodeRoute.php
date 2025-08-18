<?php

namespace MyEspacio\User\Domain;

enum PasscodeRoute: string
{
    case Phone = 'phone';
    case Email = 'email';
}
