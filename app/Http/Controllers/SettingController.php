<?php

namespace App\Http\Controllers;

use App\Setting;
use App\Transformers\SettingTransformer;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    public function index() {
        // read em all
        try {
            // query them all, then report
            $settings = Setting::get();
            return $this->respondWithCollection($settings, new SettingTransformer);
        } catch (\Throwable $e) {
            return $this->errorBadRequest($e->getMessage());
        }
    }

    public function store() {
        // store em all
        
    }
}
