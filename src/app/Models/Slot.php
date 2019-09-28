<?php 

namespace App\Models;

use \App\Models\User;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Model;

class Slot extends Model 
{
    private $increments, $params;

    protected $table = 'slots';

    protected $primaryKey = 'id';
    
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

    public function __construct($params) 
    {
        // Call parent contructor
        parent::__construct();

        // set all params to be available to the class
        $this->params = $params;
        // setup datetime increments for slot/appointment
        $this->setDatetimeIncrements();
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
     * @return  void
     */
    private function setDatetimeIncrements() 
    {
        $slot_length = $this->params['slot_length_min'];
        $Datetime = self::convertToDateTimeObject($this->params['slot_begin']);
        
        // loop through slots in 30 minute increments until reaching zero
        while ($slot_length > 0) {
            $this->increments[] = self::convertToMysqlDate($Datetime);
            $datetime = self::addThirtyMinutes($Datetime);
            $slot_length -= 30;
        }
    }

    private function slotsOpenForDesiredStylist()
    {
        return self::whereIn('slot_begin', $this->increments)
        ->whereNull('client_id')
        ->where('stylist_id', $this->stylist_id)
        ->havingRaw('COUNT(*) = ' . count($this->increments))
        ->count() > 0;
    }

    /**
     * Check or any stylists available for the given increments and if so return first value
     *
     * @return  mixed  Null if no values found or Model of random one pulled from the collection
     */
    private function slotsOpenForAnyStylist() {
       return self::whereIn('slot_begin', $this->increments)
        ->whereNull('client_id')
        ->groupBy('stylist_id')
        ->havingRaw('COUNT(*) = ' . count($this->increments))
        ->get()
        ->random();
    }

    /**
     * Will update the slots for given stylist to being booked by the client
     *
     * @param   int  $stylist_id  Stylist ID if being set for other than desired stylist
     *
     * @return  int                Lines were affected by the update             
     */
    private function updateSlotsForStylist($stylist_id = NULL)
    {
        $stylist_id = is_null($stylist_id) ? $this->stylist_id : $stylist_id;
        return self::whereIn('slot_begin', $this->increments)
        ->where('stylist_id', $stylist_id)
        ->whereNull('client_id')
        ->havingRaw('COUNT(*) = ' . count($this->increments))
        ->update(['client_id' => $this->client_id]);
    }

    /**
     * Add stylist slot(s) into the database 
     *
     *
     * @return  array  Array of success or failure with message 
     */
    public function addSlot() 
    {
        // loop through increments and add to database
        foreach ($this->increments as $increment) {
            self::updateOrCreate(
                [
                    'stylist_id' => $this->stylist_id, 
                    'slot_begin' => $increment
                ],[
                    'stylist_id' => $this->stylist_id, 
                    'slot_begin' => $increment, 
                    'order_id' => $this->params['order_id']
                ]
            );
        }

        return array_merge(self::status_success, ['message' => 'Slots successfully added.']);
    }

    /**
     * Add client appointment to available sytlist slots
     *
     * @return  array  Array of success or failure with message 
     */
    public function addAppointment() 
    {
        $slot_count = (int) $this->slot_length_min / 30;

        if ( $this->slotsOpenForDesiredStylist() ) {
            $this->updateSlotsForStylist();
            return array_merge(self::status_success, ['message' => 'Appoint scheduled for your desired stylist']);
        }

        if ( $this->flexible_in_stylist ) {
            $slots_open_for_any_stylist = $this->slotsOpenForAnyStylist();
            if ( $slots_open_for_any_stylist ) {
                $slots_open_for_any_stylist = $slots_open_for_any_stylist->toArray();
                $this->updateSlotsForStylist($slots_open_for_any_stylist['stylist_id']);
                return array_merge(self::status_success, ['message' => 'Appoint scheduled for your an alternate stylist ' . User::find($slots_open_for_any_stylist['stylist_id'])->name]);
            }
        }


        return array_merge(self::status_failed, ['message' => 'An appointment with the given criteria could not be found.']);
    }

    public function __get($property)
    {
        return isset($this->params[$property]) ? $this->params[$property] : null;
    }

}