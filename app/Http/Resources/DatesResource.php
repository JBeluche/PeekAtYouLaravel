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

        $date = [
            'id' => (string)$this->id,
            'attributes' => [
                'long_note' => $this->long_note,
                'displayed_note' => $this->displayed_note,
                'date' => $this->date,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];

        //If no color_associations yet
        if ($this->color_association_date === null) {
            return array_merge($date, [
                'relationsips' => [
                    'calendar' => [
                        'id' => (string)$this->calendar->id,
                        'name' => $this->calendar->name,
                        'user' => $this->calendar->user->name,
                    ],
                    'color_association' => [
                        ''
                    ],

                ]
            ]);
        }

        //All the data
        return array_merge($date, [
            'relationsips' => [
                'calendar' => [
                    'id' => (string)$this->calendar->id,
                    'name' => $this->calendar->name,
                    'user' => $this->calendar->user->name,
                ],
                'color_association' => [
                    'id' => (string)$this->color_association_date->id,
                    'extra_value' => $this->color_association_date->extra_value,
                    'color' => $this->color_association_date->color->hex_value,
                ],
            ]
        ]);
    }
}
