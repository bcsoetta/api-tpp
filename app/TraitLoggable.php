<?php

namespace App;

/**
 * 
 */
trait TraitLoggable
{
    public function logs(){
        return $this->morphMany('App\AppLog', 'loggable');
    }

    /* public function appendLog($message, $type="INFO", $object=null) {
        $l = new AppLog();

        $l->message = $message;
        $l->object = $object;
        $l->tipe = $type;

        // save it
        $this->logs()->save($l);

        return $l;
    } */
}
