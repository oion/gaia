<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Dossier extends Model
{
    use HasFactory;

    // get customer that owns the dossier
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function dossierDetails(): HasOne
    {
        return $this->hasOne(DossierDetails::class, 'dossier_id');
    }
}
