<?php

namespace App\File;

use App\File\FileModel as File;
use App\Folder\FolderModel as Folder;
use App\User\UserModel as User;
use App\Host\HostModel as Host;

class FileService
{
    public static function create($user_id, $host_id, $filename, $content): FileModel  
    {
        // Fetch the current authenticated user
        $user = User::find($user_id);
        $host = Host::find($host_id);

        if (!$user || !$host) {
            echo 'ERROR: INVALID INPUT';
            exit;
        }

                    // Check if the file already exists
            $existingFile = File::where('filename', $filename)->first();

            if ($existingFile) {
                echo 'ERROR: FILE EXISTS';
                exit;
            }

            // Create the new file
            $file = new File();
            $file->filename = $filename;
            $file->content = $content;
            $file->user_id = $user->id;
            $file->host_id = $host->id;
            $file->save();

            echo 'SUCCESS: FILE CREATED';
            return $file;
    }
    

    public static function list($host_id, $user_id = '')
    {
        $files = File::where('host_id', $host_id)
        ->orWhere('user_id', $user_id)
        ->get();

        if($files->isEmpty()) {
            echo 'ERROR: ACCESS DENIED';
            exit;
        }

        // Loop through each top-level folder and format the structure
        foreach ($files as $file) {
            echo "$file->id. [" . $file->file_name . "]\n";
        }
    }

    public static function open($file_name = '', $host_id = '')
    {

        $file = File::where('file_name', $file_name)
        ->orWhere('host_id', $host_id)
        ->orWhere('id', $file_name)
        ->first();

        if(empty($file->content)) {
            echo 'ERROR: FILE NOT FOUND';
        } else {
            echo $file->content;
        }
    }

}