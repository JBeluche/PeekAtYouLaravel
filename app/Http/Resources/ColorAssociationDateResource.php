<?php

namespace App\Http\Resources;

use App\Models\ColorAssociation;
use Illuminate\Http\Resources\Json\JsonResource;

class ColorAssociationDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'extra_value' => $this->extra_value,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationsips' => [
                'date' => new DatesResource ($this->date),
                'color_association' => new ColorAssociationResource ($this->colorAssociation),

            ]
        ];
    }
}
