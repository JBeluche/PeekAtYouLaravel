<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CalendarsResource extends JsonResource
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
                'name' => $this->name,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationsips' => [
                'user' => [
                    'id' => (string)$this->user->id,
                    'user name' => $this->user->name,
                    'user email' => $this->user->email,
                ],
                'colors' => ColorResource::collection($this->colors),
                'dates' => DatesResource::collection($this->dates),
                'color_associations' => ColorAssociationResource::collection($this->colorAssociations),


             

            ]
        ];
    }
}

