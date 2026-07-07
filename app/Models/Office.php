<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['office_name', 'office_address', 'office_phone', 'address_latitude', 'address_longitude'])]
class Office extends Model
{
    //
}
