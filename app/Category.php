<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'categories';

    /**
     * Cada categoria tiene cero o muchos posts.
     */
    public function posts()
    {
        return $this->hasMany('App\Post');
    }
}
