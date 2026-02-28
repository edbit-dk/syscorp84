<?php

namespace App\Level;

use App\AppModel;

class LevelModel extends AppModel
{
    protected $table = 'levels';

    protected $fillable = [
		'status',
        'level',
        'credit'
    ];

    public $timestamps = true;
    
}