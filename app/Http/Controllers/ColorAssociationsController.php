<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyColorAssociationsRequest;
use App\Http\Requests\StoreColorAssociationsRequest;
use App\Http\Requests\UpdateColorAssociationsRequest;
use App\Http\Resources\ColorAssociationResource;
use App\Models\Calendar;
use App\Models\ColorAssociation;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class ColorAssociationsController extends Controller
{
    use HttpResponses;

    public function showByCalendar(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : ColorAssociationResource::collection($calendar->colorAssociations);
    }

    public function storeMany(StoreColorAssociationsRequest $request, Calendar $calendar)
    {
        $associations = array();

        foreach ($request->color_associations as $association) {

            //dd($association['color_hex_value']);

            $colorAssociation = ColorAssociation::create([
                'calendar_id' => $calendar->id,
                'hex_value' => $association['hex_value'],
                'association_text' => $association['association_text'],
            ]);
            array_push($associations, $colorAssociation);
        }

        return ColorAssociationResource::collection(
            $associations
        );
    }

    public function editMany(UpdateColorAssociationsRequest $request, Calendar $calendar)
    {
        $associations = array();

        foreach ($request->color_associations as $association) {
            $colorAssociation = ColorAssociation::find($association['id']);
            $colorAssociation->hex_value = $association['hex_value'];
            $colorAssociation->association_text = $association['association_text'];

            $colorAssociation->save();

            array_push($associations, $colorAssociation->fresh());
        }

        return ColorAssociationResource::collection(
            $associations
        );
    }

    public function destroyMany(DestroyColorAssociationsRequest $request)
    {
        foreach ($request->color_association_ids as $id) {
            ColorAssociation::where(['id' => $id])->first()->delete();
        }
    }

    private function isNotAuthorized($calendar)
    {
        if (Auth::user()->id != $calendar->user_id) {
            return $this->error('', 'You are not authorized', 403);
        }
    }
}
