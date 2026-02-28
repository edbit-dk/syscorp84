<?php

namespace App\Host;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\User\UserModel as User;
use App\Level\LevelModel as Level;
use App\File\FileModel as File;

use App\AppModel;

class HostModel extends AppModel
{
    protected $table = 'hosts';

    public $timestamps = true;

    protected $guarded = [];

    protected $maps = [
        'id' => 'id',
		'user_id' => 'user_id',
        'password' => 'password',
        'hostname' => 'hostname',
        'welcome' => 'welcome',
        'motd' => 'motd',
        'notes' => 'notes',
        'org' => 'org',
        'os' => 'os',
		'ip' => 'ip',
		'active' => 'active',
        'credits' => 'credits'
    ];

    // A host can have many files
    public function files()
    {
        return $this->belongsToMany(File::class, 'host_file')
                ->withPivot('owner_id')
                ->withTimestamps();
    }

    public function file($name)
    {
        return $this->files()->where('filename', $name)->first();
    }


    public function users(): BelongsToMany
    {
        return $this->BelongsToMany(User::class, 'host_user', 'host_id', 'user_id')->withPivot('last_session');
    }
    
    public function host($host)
    {
        return $this->where('id', $host)->first();
    }

    public function user($user)
    {
        return $this->users()->where('user_id', $user)->first();
    }

    public function nodes(): BelongsToMany
    {
        return $this->BelongsToMany(HostModel::class, 'host_node', 'host_id', 'node_id');
    }

    public function hosts()
    {
        return $this->BelongsToMany(HostModel::class, 'host_node', 'node_id', 'host_id');
    }

    public function connections()
    {
        $connections = $this->nodes;
        
        if(!$connections->isEmpty()) {
            return $connections;
        }

        $connections = $this->hosts;

        if(!$connections->isEmpty()) {
            return $connections;
        }
        
    }

    public function scopeNearestToIp(string $ip, int $limit = 5)
    {
        $numeric = ipToNum($ip);

        return $this->selectRaw('*, ABS(ip_numeric - ?) AS distance', [$numeric])
                     ->orderBy('distance', 'asc')
                     ->limit($limit);
    }

    public function node($host)
    {
        return $this->nodes()->where('node_id', $host)->first();
    }

    public function level(): HasOne   
    {
        return $this->hasOne(Level::class, 'id', 'level_id');
    }


    public static function relations()
    {

        // Indlæs ALLE hosts i hukommelsen én gang
        $hosts = self::all()->toArray();
        $relations = [];

        foreach ($hosts as $host) {
            $relatedHosts = self::findBestRelatedHosts($host, $hosts);

            foreach ($relatedHosts as $relatedHost) {
                $relations[] = [
                    'host_id' => $host['id'],
                    'node_id' => $relatedHost['id'],
                ];
            }
        }

        $relations = self::removeDuplicateRelations($relations);

        return $relations;
    }

    private static function findBestRelatedHosts(array $host, array $allHosts)
    {
        $related = [];

        // 1. Name pattern
        $pattern = self::buildNamePattern($host['hostname']);
        if ($pattern) {
            foreach ($allHosts as $candidate) {
                if ($candidate['id'] !== $host['id'] && stripos($candidate['hostname'], $pattern) === 0) {
                    $related[] = $candidate;
                    if (count($related) >= 5) break;
                }
            }
        }

        // 2. Organization
        if (empty($related)) {
            foreach ($allHosts as $candidate) {
                if ($candidate['id'] !== $host['id'] && $candidate['org'] === $host['org']) {
                    $related[] = $candidate;
                    if (count($related) >= 5) break;
                }
            }
        }

        /*
        // 3. Location
        if (empty($related)) {
            foreach ($allHosts as $candidate) {
                if ($candidate['id'] !== $host['id'] && $candidate['location'] === $host['location']) {
                    $related[] = $candidate;
                    if (count($related) >= 5) break;
                }
            }
        }
        */

        return $related;
    }

    private static function buildNamePattern($hostname)
    {
        if (preg_match('/^(\D+)\d+$/', $hostname, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function removeDuplicateRelations(array $relations)
    {
        $unique = [];
        foreach ($relations as $relation) {
            if ($relation['host_id'] == $relation['node_id']) {
                continue; // Ignore self-references
            }

            // Less Id first (fx 1-2 not 2-1)
            $first = min($relation['host_id'], $relation['node_id']);
            $second = max($relation['host_id'], $relation['node_id']);

            $key = $first . '-' . $second;

            if (!isset($unique[$key])) {
                $unique[$key] = [
                    'host_id' => $first,
                    'node_id' => $second,
                ];
            }
        }
        return array_values($unique);
    }



}