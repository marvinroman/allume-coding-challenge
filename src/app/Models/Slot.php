<?php 

namespace App\Models;

use \App\Models\User;
use \Illuminate\Database\Eloquent\Model;

class Slot extends Model 
{
    protected $table = 'slots';
    
    protected $fillable = [
        'stylist_id',
        'client_id',
        'slot_begin',
        'order_id',
    ];
    
    CONST status_failed = [
        'status' => 'failed',
        'code' => 400
    ];

    CONST status_success = [
        'status' => 'success',
        'code' => 200
    ];

    private static function userExists($id)
    {
        return User::whereId($id)->count() > 0;
    }

    private static function convertToDateTimeObject($datetime) 
    {
        return new \DateTime($datetime);
    }

    private static function convertToMysqlDate($Datetime)
    {
        return $Datetime->format("Y-m-d H:i:s");
    }

    private static function addThirtyMinutes($Datetime)
    {
        return $Datetime->add(new \DateInterval('PT30M'));
    }

    /**
     * Create an array of Datetime increments in MySQL format
     *
     * @param   string  $slot_begin     Slot begining time
     * @param   int     $slot_length    How many minutes the slots span in 30 minute increments
     *
     * @return  array                   Array of datetime strings in MySQL format
     */
    private static function getDatetimeIncrements($slot_begin, $slot_length) 
    {
        $increments = [];
        $Datetime = self::convertToDateTimeObject($slot_begin);
        
        // loop through slots in 30 minute increments until reaching zero
        while ($slot_length > 0) {
            $increments[] = self::convertToMysqlDate($Datetime);
            $datetime = self::addThirtyMinutes($Datetime);
            $slot_length -= 30;
        }
        return $increments;
    }

    /**
     * Add stylist slot(s) into the database 
     *
     * @param   array  $params  Array of params that were sent
     *
     * @return  array           Array of success or failure
     */
    public static function addSlot($params) 
    {
        // make sure stylist exists
        if ( self::userExists($params['stylist_id']) === false ) {
            return array_merge(self::status_failed, ['message' => 'Stylist doesn\'t exist.']);
        }

        // convert into and array of increments
        $increments = self::getDatetimeIncrements($params['slot_begin'], $params['slot_length_min']);

        // loop through increments and add to database
        foreach ($increments as $increment) {
            self::updateOrCreate(
                ['stylist_id' => $params['stylist_id'], 'slot_begin' => $increment], 
                ['order_id' => $params['order_id']]
            );
        }

        return array_merge(self::status_success, ['message' => 'Slots successfully added.']);
    }


}