<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DatesResource extends JsonResource
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
                'information' => $this->information,
                'date' => $this->date,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationsips' => [
                'calendar' => [
                    'id' => (string)$this->calendar->id,
                    'name' => $this->calendar->name,
                    'user' => $this->calendar->user->name,
                ],
                'color' => [
                    'id' => (string)$this->color->id,
                    'hex_value' => $this->color->hex_value,
                ],
            ]
        ];
    }
}
