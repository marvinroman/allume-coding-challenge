<?php

namespace App\Models\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

class MultipleOfThirty extends AbstractRule
{

    // return false if input is not a multiple of thirty
    public function validate($input)
    {
        return $input % 30 == 0;
    }
}