<?php

namespace App\File;

use App\AppModel;

use App\User\UserModel as User;
use App\Host\HostModel as Host;

class FileModel extends AppModel
{
    protected $table = 'files';

    protected $guarded = [];

    protected $maps = [
        'filename' => 'filename',
        'content' => 'content'
    ];

    // A file belongs to a user (owner)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A file belongs to a host
    public function host()
    {
        return $this->belongsTo(Host::class);
    }

}
