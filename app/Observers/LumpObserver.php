<?php

namespace App\Observers;

use App\Models\Lump;

class LumpObserver
{
    public function creating(Lump $lump)
    {
        $lump->name = strtoupper(trim($lump->name));
    }
}
