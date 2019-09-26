<?php 

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model 
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'type',
    ];

    /**
     * Check if user exists already based on email
     *
     * @param   string  $email  email
     *
     * @return  boolean         whether or not user exists
     */
    private static function exists($email)
    {
        // short ternery if there are more than zero results
        return self::where('email', $email)->count() > 0;
    }

    /**
     * Create a new user in the database
     *
     * @param   array  $params  params sent to the API
     *
     * @return  array           status array that includes detailed information on success of failure
     */
    public function addUser($params) 
    {
        // return error if the user exists
        if (self::exists($params['email'])) {
            return [
                'status' => 'failed',
                'code' => 400,
                'message' => 'User already exists with that email.'
            ];
        }

        // create user
        $user = self::create([
            'name' => $params['name'],
            'email' => $params['email'],
            'type' => $params['type'],
        ]);

        return [
            'status' => 'success',
            'code' => 200,
            'message' => 'New user successfully created.',
            'user' => $user,
        ];
    }
}