<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditColorAssociationsRequest;
use App\Http\Requests\StoreColorAssociationsRequest;
use App\Http\Resources\ColorAssociationResource;
use App\Models\Calendar;
use App\Models\Color;
use App\Models\ColorAssociation;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ColorAssociationsController extends Controller
{
    use HttpResponses;

    public function showByCalendar($calendar_id)
    {
        if (Calendar::where(['id' => $calendar_id])->first() === null) {
            return $this->error('', 'Calendar not found', 404);
        }
        return ColorAssociationResource::collection(
            ColorAssociation::where(['calendar_id' => $calendar_id])->get()
        );
    }

    //Edit
    public function editMany(EditColorAssociationsRequest $request)
    {
        $request->validated($request->all());


        if ($this->hasColorNotFound($request)) {
            return $this->error('', 'Wrong color id', 409);
        }
        
        if(!$this->confirmIds($request)[0]){
            
            return $this->confirmIds($request)[1];
        }
    
        $associations = array();

        foreach($request->items as $association)
        {
            $colorAssociation = ColorAssociation::find($association['id']);
            $colorAssociation->color_id = $association['color_id']; 
            $colorAssociation->association_text = $association['association_text']; 

            $colorAssociation->save();

            array_push($associations, $colorAssociation->fresh());
        }

        return ColorAssociationResource::collection(
            $associations
        );

    }

    //Store
    public function storeMany(StoreColorAssociationsRequest $request)
    {
        $request->validated($request->all());

        if ($this->hasColorNotFound($request)) {
            return $this->error('', 'Wrong color id', 409);
        }
      
        $associations = array();

        foreach ($request->items as $association) {

            $obj = ColorAssociation::where(['color_id' => $association['color_id']])->where(['calendar_id' => $request->calendar_id])->first();

            if ($obj === NULL) {

                $colorAssociation = ColorAssociation::create([
                    'calendar_id' => $request->calendar_id,
                    'color_id' => $association['color_id'],
                    'association_text' => $association['association_text'],
                ]);
                array_push($associations, $colorAssociation);
            }
        }

        return ColorAssociationResource::collection(
            $associations
        );
    }

    //Destroy
    public function destroyMany(Request $request)
    {
        $this->validate($request, [
            'items' => 'required|array',
            'items.*' => 'numeric',
        ]);

        $associationsArray = [];

        //Check if all id's are valid
        foreach ($request->items as $id) {
            $association = ColorAssociation::where(['id' => $id])->first();
            if($association === null){
                return $this->error('', 'One or more id s did not exist ', 404); 
            } 
            array_push($associationsArray, $association);
        }

        //Delete found assoications
        foreach ($associationsArray as $association) {
            $association->delete();
        }
    }

    /* Helper functions */
    private function hasColorNotFound($request)
    {
        foreach ($request->items as $item) {
            if (Color::where('id', $item['color_id'])->first() != null) {
                continue;
            } else {
                return true;
            }
        }

        return false;
    }

    private function confirmIds($request){
        
        foreach ($request->items as $item) {
             //Check if duplicate
             $counter = 0;

             foreach($request->items as $itemToCompare){
                 if($item['id'] === $itemToCompare['id'])
                 {
                     $counter++;
                     if($counter >= 2){
                         return [false, $this->error('', 'Multiple id\'s are the same', 409)];
                     }
                 }
             }

            //Check if exist
            if (ColorAssociation::find($item['id']) != null) {
                continue;
            } else {
                return [false, $this->error('', 'Wrong color_association id found', 409)];
            }
           
        }
        return [true];
    }
}
