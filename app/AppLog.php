<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    //
    protected $table = 'log';

    protected $attributes = [
        'loggable_type' => '',
        'loggable_id' => 0
    ];

    public function loggable(){
        return $this->morphTo();
    }
    
    static public function logInfo($message, $object = NULL, $saveJSON = true){
        $log = new AppLog;
        $log->message = $message;

        if ($saveJSON && $object) 
            $log->object = json_encode($object);
        
        $log->tipe = 'INFO';
        // $log->save();

        // if object is not null, might want to attach it
        if ($object) {
            if (method_exists($object, 'logs')) {
                // attach it
                $object->logs()->save($log);
            }
        } else {
            $log->save();
        }

        return $log;
    }

    static public function logWarning($message, $object = NULL, $saveJSON = true){
        $log = new AppLog;
        $log->message = $message;

        if ($object && $saveJSON)
            $log->object = json_encode($object);
        
        $log->tipe = 'WARNING';
        // $log->save();

        // if object is not null, might want to attach it
        if ($object) {
            if (method_exists($object, 'logs')) {
                // attach it
                $object->logs()->save($log);
            }
        } else {
            $log->save();
        }

        return $log;
    }

    static public function logError($message, $object = NULL, $saveJSON = true){
        $log = new AppLog;
        $log->message = $message;

        if ($object && $saveJSON)
            $log->object = json_encode($object);
            
        $log->tipe = 'ERROR';
        // $log->save();

        // if object is not null, might want to attach it
        if ($object) {
            if (method_exists($object, 'logs')) {
                // attach it
                $object->logs()->save($log);
            }
        } else {
            $log->save();
        }

        return $log;
    }


}
