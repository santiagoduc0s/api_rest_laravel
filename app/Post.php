<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'posts';

    /**
     * Cada post pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Cada post pertenece a una categoria.
     */
    public function category()
    {
        return $this->belongsTo('App\Category', 'category_id');
    }
}
