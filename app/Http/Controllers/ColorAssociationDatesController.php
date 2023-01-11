<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyManyColorAssociationDates;
use App\Http\Requests\StoreColorAssociationDateRequest;
use App\Http\Requests\UpdateColorAssociationDateRequest;
use App\Http\Resources\ColorAssociationDateResource;
use App\Models\ColorAssociationDate;
use App\Models\Date;
use Illuminate\Http\Request;


class ColorAssociationDatesController extends Controller
{
    public function show(Date $date){

    }

    public function storeMany(StoreColorAssociationDateRequest $request)
    {
        $request->validated($request->all());

        $associations = array();


        foreach($request->associations as $association){

            $dateColorAssociation = ColorAssociationDate::create([
                'date_id' => $request->date_id,
                'color_association_id' => $association['color_association_id'],
                'extra_value' => $association['extra_value']
            ]);

            array_push($associations, $dateColorAssociation);

        }

        return ColorAssociationDateResource::collection(
            $associations
        );
    }

    public function updateMany(UpdateColorAssociationDateRequest $request){
        
       $request->validated($request->all());
        
        $associations = array();

        foreach($request->color_association_dates as $association){

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

    public function destroyMany(DestroyManyColorAssociationDates $request){
        
        $request->validated($request->all());
      
       
       /*
       foreach($request->ids as $id){

       }

        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : $calendar->delete();

        */


    }



}
