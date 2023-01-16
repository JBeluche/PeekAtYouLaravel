<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyColorAssociationDatesRequest;
use App\Http\Requests\StoreColorAssociationDateRequest;
use App\Http\Requests\UpdateColorAssociationDatesRequest;
use App\Http\Resources\ColorAssociationDateResource;
use App\Models\Calendar;
use App\Models\ColorAssociationDate;
use App\Models\Date;
use Illuminate\Support\Facades\Auth;

class ColorAssociationDatesController extends Controller
{
    public function showByDate(Date $date)
    {
        $calendar = Calendar::find($date->calendar_id);
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : ColorAssociationDateResource::collection($date->colorAssociations);
    }

    public function storeMany(StoreColorAssociationDateRequest $request, Date $date)
    {

        $colorAssociationDates = array();

        foreach ($request->color_association_dates as $coloar_association_date) {

            $colorAssociationDate = ColorAssociationDate::create([
                'date_id' => $date->id,
                'color_association_id' => $coloar_association_date['color_association_id'],
                'extra_value' => $coloar_association_date['extra_value']
            ]);

            array_push($colorAssociationDates, $colorAssociationDate);
        }

        return ColorAssociationDateResource::collection(
            $colorAssociationDates
        );
    }

    public function updateMany(UpdateColorAssociationDatesRequest $request, Date $date)
    {
        $associations = array();

        foreach ($request->color_association_dates as $association) {

            $colorDateAssociation = ColorAssociationDate::find($association['id']);

            $colorDateAssociation->update([
                'date_id' => $request->date_id,
                'color_association_id' => $association['color_association_id'],
                'extra_value' => $association['extra_value']
            ]);

            array_push($associations, $colorDateAssociation);
        }
        return ColorAssociationDateResource::collection(
            $associations
        );
    }

    public function destroyMany(DestroyColorAssociationDatesRequest $request)
    {
        foreach ($request->color_association_date_ids as $id) {
            ColorAssociationDate::where(['id' => $id])->first()->delete();
        }
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
}
