<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierDetails extends Model
{
  use HasFactory;

  // get dossier details
  public function dosierDetails(): BelongsTo
  {
    return $this->belongsTo(Dossier::class, 'dossier_id');
  }
}
