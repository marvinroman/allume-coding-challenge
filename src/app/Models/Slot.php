<?php 

namespace App\Models;

use \App\Models\User;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * Check if all increments are open for the given stylist
     *
     * @return  boolean  whether or not the stylis is booked for those time increments
     */
    private function slotsOpenForDesiredStylist()
    {
        return self::whereIn('slot_begin', $this->increments)
            ->whereNull('client_id')
            ->where('stylist_id', $this->stylist_id)
            ->havingRaw('COUNT(*) = ' . count($this->increments))
            ->count() > 0;
    }

    /**
     * Check or any stylists available for the given increments
     *
     * @return  Collection  Collection of rows that match given increments that aren't booked
     */
    private function slotsOpenForAnyStylist() 
    {
       return self::whereIn('slot_begin', $this->increments)
            ->whereNull('client_id')
            ->groupBy('stylist_id')
            ->havingRaw('COUNT(*) = ' . count($this->increments))
            ->get();
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
     * Remove any slots that aren't booked
     *
     * @return  array  Array or success or failure with message 
     */
    public function removeSlot()
    {
        $number_deleted = 0;
        if ( $this->all_or_none ) {
            if ( $this->slotsOpenForDesiredStylist() ) {
                // delete all slots that aren't booked (client_id is NULL)
                $number_deleted = self::whereIn('slot_begin', $this->increments)
                    ->where('stylist_id', $this->stylist_id)
                    ->whereNull('client_id')
                    ->delete();          
            }
        } else {
            // delete all slots that aren't booked (client_id is NULL)
            $number_deleted = self::whereIn('slot_begin', $this->increments)
                ->where('stylist_id', $this->stylist_id)
                ->whereNull('client_id')
                ->delete();
        }

        // check if the number deleted is the same as the time increments
        if ( $number_deleted == count($this->increments) ) {
            return array_merge(self::status_success, ['message' => 'All the stylist slots were successfully deleted.']);
        } else {
            // pull the records for the given time increments that weren't deleted
            $not_deleted = self::whereIn('slot_begin', $this->increments)
                ->where('stylist_id', $this->stylist_id)
                ->get();
            // if all_or_none then return failure 
            $status = $this->all_or_none ? self::status_failed : self::status_success;
            return array_merge($status, [
                'message' => $number_deleted . ' of ' . count($this->increments) . ' where deleted.', 
                'not_deleted' => $not_deleted->toArray()
                ]);
        }

    }

    /**
     * Add client appointment to available sytlist slots
     *
     * @return  array  Array of success or failure with message 
     */
    public function addAppointment() 
    {

        // check if all available slots are open for the desired stylist 
        // values set when instantiating class
        if ( $this->slotsOpenForDesiredStylist() ) {
            // update slots with client_id already instantiated
            $this->updateSlotsForStylist();
            return array_merge(self::status_success, ['message' => 'Appoint scheduled for your desired stylist']);
        }

        // if client has chosen flexible_in_stylist then broaden out search for times available for all stylists
        if ( $this->flexible_in_stylist ) {
            // method will return values found 
            $slots_open_for_any_stylist = $this->slotsOpenForAnyStylist();

            // if there is more than zero then update a random stylist
            if ( $slots_open_for_any_stylist->count() != 0 ) {
                // get random row and convert to an array
                $random_stylist = $slots_open_for_any_stylist->random()->toArray();
                // update the slots for the selected stylist 
                $this->updateSlotsForStylist($random_stylist['stylist_id']);
                return array_merge(self::status_success, ['message' => 'Appoint scheduled for your an alternate stylist ' . User::find($slots_open_for_any_stylist['stylist_id'])->name]);
            }
        }

        return array_merge(self::status_failed, ['message' => 'An appointment with the given criteria could not be found.']);
    }

    /**
     * factory method to get params using property 
     *
     * @param   string  $property  property as a string
     *
     * @return  mixed              return the value from the params array / null
     */
    public function __get($property)
    {
        return isset($this->params[$property]) ? $this->params[$property] : null;
    }

}