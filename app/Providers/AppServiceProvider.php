<?php

namespace App\Providers;

use App\EntryManifest;
use App\Lampiran;
use App\Observers\EntryManifestObserver;
use App\Observers\LampiranObserver;
use App\Services\SSO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // try to register it?
        $this->app->bind(SSO::class, function () {
            // resolve for request object
            $request = app(Request::class);

            return new SSO($request);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // set it cause mysql would explode otherwise
        Schema::defaultStringLength(191);

        // obeservers
        Lampiran::observe(LampiranObserver::class);
        EntryManifest::observe(EntryManifestObserver::class);
    }
}
