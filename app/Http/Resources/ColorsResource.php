<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorsResource extends JsonResource
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
                'hex_value' => $this->hex_value,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationsips' => [
                'calendars' => CalendarResource::collection($this->calendars),
                'palettes' => PaletteResource::collection($this->palettes),
                'dates' => DatesResource::collection($this->dates),

            ]
        ];
    }
}
