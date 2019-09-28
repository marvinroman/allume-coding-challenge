<?php

namespace App\Models\Validation\Rules;

use \App\Models\User;
use \Respect\Validation\Rules\AbstractRule;

class IsClient extends AbstractRule
{

    // return false if input is not a multiple of thirty
    public function validate($input)
    {
        return User::where('id', $input)
        ->where('type', User::CLIENT_TYPE)
        ->count() > 0;
    }
}