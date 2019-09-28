<?php 

namespace App\Models;

use \Slim\Http\Request;
use \Respect\Validation\Validator as Respect;
use \Respect\Validation\Exceptions\NestedValidationException;

class Validator 
{
    protected $errors;

    /**
     * Loop through rules and fields 
     *
     * @param   Request $request  Slim Request object
     * @param   array   $rules    Array of Respect rules
     *
     * @return  Validator         return this object
     */
    public function validate($request, array $rules) 
    {
        foreach ($rules as $field => $rule) {
            try {
                $rule->setName(ucfirst($field))->assert($request->getParam($field));
            } catch (NestedValidationException $e) {
                $this->errors[$field] = $e->getMessages();
            }
        }

        return $this;
    }

    /**
     * Whether validation failed
     *
     * @return  boolean  Whether validation passed
     */
    public function failed() 
    {
        return !empty($this->errors);
    }

    /**
     * Get errors 
     *
     * @return  array  array of errors
     */
    public function errors()
    {
        return $this->errors;
    }
}