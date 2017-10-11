<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetailTemp extends Model
{
    protected $table = 'purchase_details_temp';
    public $timestamps = false;

    /**
     * Get the details related to the purchase entry
     */
    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
