<?php 

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Slot extends Model 
{
    protected $table = 'slots';

    protected $fillable = [
        'stylist_id',
        'client_id',
        'slot_begin',
    ];
}