<?php

namespace App\Models\Validation\Rules;

use \App\Models\User;
use \Respect\Validation\Rules\AbstractRule;

class IsStylist extends AbstractRule
{

    // return false if input is not a multiple of thirty
    public function validate($input)
    {
        return User::where('id', $input)
        ->where('type', User::STYLIST_TYPE)
        ->count() > 0;
    }
}