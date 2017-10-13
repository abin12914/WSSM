<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetailTemp extends Model
{
    protected $table = 'sale_details_temp';
    public $timestamps = false;

    /**
     * Get the details related to the purchase entry
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
