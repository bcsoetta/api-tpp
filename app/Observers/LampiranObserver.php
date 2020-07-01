<?php

namespace App\Observers;

use App\Lampiran;

class LampiranObserver
{
    /**
     * Handle the lampiran "created" event.
     *
     * @param  \App\Lampiran  $lampiran
     * @return void
     */
    public function created(Lampiran $lampiran)
    {
        // instantiate on disk if not already
        $lampiran->instantiateOnDisk();
    }

    /**
     * Handle the lampiran "updated" event.
     *
     * @param  \App\Lampiran  $lampiran
     * @return void
     */
    public function updated(Lampiran $lampiran)
    {
        // usually will not happen
    }

    /**
     * Handle the lampiran "deleted" event.
     *
     * @param  \App\Lampiran  $lampiran
     * @return void
     */
    public function deleted(Lampiran $lampiran)
    {
        // when deleted, delete the file on disk too
        $lampiran->deleteOnDisk();
    }

    /**
     * Handle the lampiran "restored" event.
     *
     * @param  \App\Lampiran  $lampiran
     * @return void
     */
    public function restored(Lampiran $lampiran)
    {
        // when restored, reinstantiate file
        $lampiran->instantiateOnDisk();
    }

    /**
     * Handle the lampiran "force deleted" event.
     *
     * @param  \App\Lampiran  $lampiran
     * @return void
     */
    public function forceDeleted(Lampiran $lampiran)
    {
        //
    }
}
