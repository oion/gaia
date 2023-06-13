<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierDetails extends Model
{
  use HasFactory;

  protected $fillable = [
    'id',
    'dossier_id',
    'received_date',
    'completion_date',
    'status',
    'history',
    'notes'
  ];

  // get dossier details
  public function dossier(): BelongsTo
  {
    return $this->belongsTo(Dossier::class, 'dossier_id');
  }
}
