<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DossierDetailsResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'dossier_id' => $this->dossier_id,
      'request_number' => $this->request_number,
      'received_date' => $this->received_date,
      'completion_date' => $this->completion_date,
      'status' => $this->status,
      'cadastral_service' => $this->cadastral_service,
    ];
  }
}
