<?php

namespace App\Http\Resources;

use App\Models\Date;
use Illuminate\Http\Resources\Json\JsonResource;

class CalendarResource extends JsonResource
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
            'name' => $this->name,
            /*  'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,*/
            // ],
            /*'relationships' => [
                'user' => [
                    'id' => (string)$this->user->id,
                    'user name' => $this->user->name,
                    'user email' => $this->user->email,
                ],*/
            'color_associations' => ColorAssociationResource::collection($this->colorAssociations),
            'dates' => DatesResource::collection($this->filterDates()),
            // ]
        ];
    }

    private function filterDates()
    {

        $month = request()->query('month');
        $year = request()->query('year');

        if (isset($month) && isset($year)) {
            $dates = Date::where('calendar_id', '=', $this->id)
                ->whereYear('date', '=', $year)
                ->whereMonth('date', '=', $month)
                ->get();

            return  $dates;
        }

        $dates = Date::where('calendar_id', '=', $this->id)
            ->whereYear('date', '=', date("Y"))
            ->whereMonth('date', '=', date("m"))
            ->get();

        return $dates;
    }
}
