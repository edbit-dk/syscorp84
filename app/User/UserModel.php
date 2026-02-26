<?php

namespace App\User;

use App\AppModel;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Host\HostModel as Host;
use App\File\FileModel as File;
use App\Folder\FolderModel as Folder;
use App\Mission\MissionModel as Mission;
use App\Email\EmailModel as Email;

class UserModel extends AppModel
{
    protected $table = 'users';

    public $timestamps = true;

    protected $guarded = [];
    
    protected $maps = [
        'email' => 'email',
        'username' => 'username',
        'password' => 'password',
        'code' => 'code',
        'fullname' => 'fullname',
        'role' => 'role',
        'active' => 'active',
        'level_id' => 'level_id',
        'ip' => 'ip',
        'credits' => 'credits',
        'last_login' => 'last_login',
        'created' => 'created_at'
    ];


    // A user can have many files
    public function files()
    {
        return $this->belongsToMany(File::class, 'user_file')
                    ->withPivot('version', 'is_active') // Hent ekstra felter fra pivot
                    ->withTimestamps();
    }

    public function tools()
    {
        return $this->files()->wherePivot('is_active', true)->get();
    }

    public function emails()
    {
        return $this->hasMany(Email::class);    
    }

    public function hosts(): BelongsToMany
    {
        return $this->BelongsToMany(Host::class, 'host_user', 'user_id', 'host_id');
    }

    public function host($host)
    {
        return $this->hosts()->where('host_id', $host)->first();
    }

    public function missions() 
    {
        return $this->belongsToMany(Mission::class, 'user_missions')
                    ->withPivot('status', 'started_at', 'completed_at')
                    ->withTimestamps();
    }

}