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
            //'attributes' => [
                'long_note' => $this->long_note,
                'displayed_note' => $this->displayed_note,
                'extra_value' => $this->extra_value,
                'date' => $this->date,
               // 'created_at' => $this->created_at,
                //'updated_at' => $this->updated_at,
          //  ],
          //  'relationships' => [
               // 'calendar' => new CalendarResource($this->whenLoaded('calendar')),
                'color_association' => new ColorAssociationResource($this->colorAssociation),

            //]
        ];
    }
}
