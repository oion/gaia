<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email',
        'address',
        'postal_code',
        'city',
        'county',
        'country_code'
    ];


    public function dossiers(): HasMany
    {
        return  $this->hasMany(Dossier::class);
    }
}
