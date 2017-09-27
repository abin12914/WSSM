<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
     public $timestamps = false;
     
    /**
     * Get the comments for the blog post.
     */
    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id');
    }
}
