<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractDokumen extends Model implements IDokumen, IStatusable, ILockable
{
    // no settings, only implements and traits
    use TraitDokumen;
    use TraitStatusable;
    use TraitLockable;
    use TraitAttachable;

    abstract public function getJenisDokumenAttribute();
    abstract public function getJenisDokumenLengkapAttribute();
    abstract public function getSkemaPenomoranAttribute();
}
