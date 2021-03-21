<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    // deleted_at
    use SoftDeletes;

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
