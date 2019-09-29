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
    
    CONST STATUS_FAILED = [
        'status' => 'failed',
        'code' => 400
    ];

    CONST STATUS_SUCCESS = [
        'status' => 'success',
        'code' => 200
    ];

    CONST FLEX_TIME_VARIANCE = 90;

    public function __construct($params = NULL) 
    {
        // Call parent contructor
        parent::__construct();

        if (! is_null($params)) {
            // set all params to be available to the class
            $this->params = $params;
            // setup datetime increments for slot/appointment
            $this->setDatetimeIncrements();
        }
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

    private static function subTimeVariance($Datetime)
    {
        return $Datetime->sub(new \DateInterval('PT' . self::FLEX_TIME_VARIANCE . 'M'));
    }

    /**
     * Create an array of Datetime increments in MySQL format
     *
     * @return  void
     */
    private function setDatetimeIncrements() 
    {
        $slot_length = $this->slot_length_min;
        $Datetime = self::convertToDateTimeObject($this->slot_begin);
        
        $this->increments = self::getIncrements($slot_length, $Datetime);

    }

    /**
     * get 30 minute increments
     *
     * @param   integer     $slot_length    Minutes from slot begin to get slot increments for
     * @param   \Datetime   $Datetime       PHP Datetime object
     *
     * @return  array                       Array of datetime strings of increments
     */
    private function getIncrements($slot_length, $Datetime) 
    {
        $increments = [];
        // loop through slots in 30 minute increments until reaching zero
        while ($slot_length > 0) {
            $increments[] = self::convertToMysqlDate($Datetime);
            $datetime = self::addThirtyMinutes($Datetime);
            $slot_length -= 30;
        }
        return $increments;
    }

    /**
     * Check if all increments are open for the given stylist
     * 
     * @param   array   $increments  Time increments
     *
     * @return  boolean  whether or not the stylis is booked for those time increments
     */
    private function slotsOpenForDesiredStylist($increments = NULL)
    {
        $increments = is_null($increments) ? $this->increments : $increments;
        return self::whereIn('slot_begin', $increments)
            ->whereNull('client_id')
            ->where('stylist_id', $this->stylist_id)
            ->count() == count($increments);
    }

    /**
     * Check or any stylists available for the given increments
     * 
     * @param   array   $increments  Time increments
     *
     * @return  Collection  Collection of rows that match given increments that aren't booked
     */
    private function slotsOpenForAnyStylist($increments = NULL) 
    {
        $increments = is_null($increments) ? $this->increments : $increments;
        return self::whereIn('slot_begin', $increments)
            ->whereNull('client_id')
            ->groupBy('stylist_id')
            ->havingRaw('COUNT(*) = ' . count($increments))
            ->get();
    }

    /**
     * Will update the slots for given stylist to being booked by the client
     *
     * @param   int     $stylist_id  Stylist ID if being set for other than desired stylist
     * @param   array   $increments  Time increments
     *
     * @return  int                Lines were affected by the update             
     */
    private function updateSlotsForStylist($stylist_id = NULL, $increments = NULL)
    {
        $increments = is_null($increments) ? $this->increments : $increments;
        $stylist_id = is_null($stylist_id) ? $this->stylist_id : $stylist_id;
        return self::whereIn('slot_begin', $increments)
            ->where('stylist_id', $stylist_id)
            ->whereNull('client_id')
            ->havingRaw('COUNT(*) = ' . count($increments))
            ->update(['client_id' => $this->client_id]);
    }

    /**
     * removed the slots for stylist & time increments
     *
     * @return  int  how many records were deleted
     */
    private function deleteSlots()
    {
        // delete all slots that aren't booked (client_id is NULL)
        return self::whereIn('slot_begin', $this->increments)
            ->where('stylist_id', $this->stylist_id)
            ->whereNull('client_id')
            ->delete();
    }

    /**
     * Determine whether or not givent stylist is booked for client for time increments
     *
     * @return  boolean  whether or not stylist is booked for client for the time given
     */
    private function appointmentSet()
    {
        return self::whereIn('slot_begin', $this->increments)
            ->where('stylist_id', $this->stylist_id)
            ->where('client_id', $this->client_id)
            ->count() == count($this->increments);
    }

    /**
     * Books time in flexible time before or after given slot
     *
     * @return  mixed  False if alternate time is not found, array or stylist_id & alternate slot_begin time
     */
    private function bookFlexibleTime()
    {
        $Datetime = self::convertToDateTimeObject($this->slot_begin);
        // create a new begining time of times to iterate through
        $new_slot_begin = self::subTimeVariance($Datetime);
        // get time increments to iterate through these should be times +- FLEX_TIME_VARIANCE
        $flex_increments = $this->getIncrements((self::FLEX_TIME_VARIANCE * 2) + 30, $new_slot_begin);

        // iterate through time increments and see if appointments are available for alternate increments
        foreach ( $flex_increments as $increment ) {
            $variance_datetime = self::convertToDateTimeObject($increment);
            $increments = $this->getIncrements($this->slot_length_min, $variance_datetime);
            if ( $this->slotsOpenForDesiredStylist($increments) ) {
                $this->updateSlotsForStylist($this->stylist_id, $increments);
                return [
                    'slot_begin' => $increment,
                ];
            }
        }

        if ( $this->flexible_in_stylist ) {
            // iterate through time increments and see if appointments are available for alternate increments & any stylist
            foreach ( $flex_increments as $increment ) {
                $variance_datetime = self::convertToDateTimeObject($increment);
                $increments = $this->getIncrements($this->slot_length_min, $variance_datetime);
                $slots_open_for_any_stylist = $this->slotsOpenForAnyStylist($increments);
                if ( $slots_open_for_any_stylist->count() != 0 ) {
                    // get random stylist from available stylist available during given time slots
                    $random_stylist = $slots_open_for_any_stylist->random()->toArray();
                    $this->updateSlotsForStylist($random_stylist['stylist_id'], $increments);
                    return [
                        'slot_begin' => $increment,
                        'stylist_id' => $random_stylist['stylist_id'],
                    ];
                }
            }
        }

        return false;
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

        return array_merge(self::STATUS_SUCCESS, ['message' => 'Slots successfully added.']);
    }

    /**
     * Remove any slots that aren't booked
     *
     * @return  array  Array or success or failure with message 
     */
    public function removeSlot()
    {
        $number_deleted = 0;
        // check if stylist wants all or none deleted
        if ( $this->all_or_none ) {
            // check that all slots are still open for the stylist
            if ( $this->slotsOpenForDesiredStylist() ) {
                $number_deleted = $this->deleteSlots();
            }
        } else {
            $number_deleted = $this->deleteSlots();
        }

        // check if the number deleted is the same as the time increments
        if ( $number_deleted == count($this->increments) ) {
            return array_merge(self::STATUS_SUCCESS, ['message' => 'All the stylist slots were successfully deleted.']);
        } else {
            // pull the records for the given time increments that weren't deleted
            $not_deleted = self::whereIn('slot_begin', $this->increments)
                ->where('stylist_id', $this->stylist_id)
                ->get();
            // if all_or_none then return failure 
            $status = $this->all_or_none ? self::STATUS_FAILED : self::STATUS_SUCCESS;
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
            return array_merge(self::STATUS_SUCCESS, ['message' => 'Appointment scheduled for your desired stylist']);
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
                return array_merge(self::STATUS_SUCCESS, [
                    'message' => 'Appointment scheduled for an alternate stylist ' . User::find($random_stylist['stylist_id'])->name
                    ]);
            }
        }

        // if a client is flexible_in_time then broaden out the search for times available with a variance in minutes set by CONST FLEX_TIME_VARIANCE
        if ( $this->flexible_in_time ) {
            // bookFlexibleTime searches alternate times and alternate stylists if flexible_in_stylist is also true
            $status = $this->bookFlexibleTime();
            // $status is false if no times were found
            if ( $status ) {
                // if stylist_id is set then the appointment was booked for an alternate stylist
                if ( isset($status['stylist_id']) ) {
                    return array_merge(self::STATUS_SUCCESS, [
                        'message' => 'Appointment scheduled for an alternate stylist ' . User::find($status['stylist_id'])->name . ', with an alternate time . ' . $status['slot_begin']
                        ]);        
                } else {
                    return array_merge(self::STATUS_SUCCESS, [
                        'message' => 'Appointment scheduled for with an alternate time . ' . $status['slot_begin']
                        ]);  
                }
            }
        }

        return array_merge(self::STATUS_FAILED, ['message' => 'An appointment with the given criteria could not be found.']);
    }

    /**
     * cancel appointment for client
     *
     * @return  array  success or failure array with message
     */
    public function cancelAppointment() 
    {
        // check that appointment is set for stylist & client set during initialization
        if ( $this->appointmentSet() ) {
            // set client ID to null for the slots
            self::whereIn('slot_begin', $this->increments)
                ->where('stylist_id', $this->stylist_id)
                ->where('client_id', $this->client_id)
                ->update(['client_id' => NULL]);
            return array_merge(self::STATUS_SUCCESS, ['message' => 'Appointment has been canceled.']);
        } else {
            return array_merge(self::STATUS_FAILED, ['message' => 'Appointment could not be canceled.']);
        }
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