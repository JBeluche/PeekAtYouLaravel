<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorAssociationResource extends JsonResource
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
           // 'attributes' => [
                'association_text' => $this->association_text,
                'hex_value' => $this->hex_value,
                //'created_at' => $this->created_at,
                //'updated_at' => $this->updated_at,
            //],
            /*'relationships' => [
                'calendars' => new CalendarResource($this->whenLoaded('calendar')),

            ]*/
        ];
    }
}
