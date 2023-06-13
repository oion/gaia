<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DossierResource extends JsonResource
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
            'customerId' => $this->customer_id,
            'name' => $this->name,
            'bcpiId' => $this->bcpi_id,
            'status' => $this->status,
            'statusDate' => $this->status_date,
            'dossierDetails' => new DossierDetailsResource($this->whenLoaded('dossierDetails'))
        ];
    }
}
