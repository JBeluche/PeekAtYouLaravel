<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarsRequest;
use App\Http\Requests\StoreColorsRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarsResource;
use App\Http\Resources\DatesResource;
use App\Models\Calendar;
use App\Models\Color;
use App\Models\Date;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class CalendarsController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return CalendarsResource::collection(
            Calendar::where('user_id', Auth::user()->id)->get()
        );
    }

    public function calendar_dates($calendarId)
    {
        $calendar = Calendar::where('id', $calendarId)->first();
        if($calendar != null){
            if(Auth::user()->id !== $calendar->user->id){
                return $this->error('', 'You are not authorized', 403);
            }
    
            return DatesResource::collection(
                Date::where('calendar_id', $calendarId)->get()
            );
        }
        else{
            return $this->error('', 'No calendar found', 404);
        }
       
    }
   

    public function store(StoreCalendarsRequest $request)
    {

        $request->validated($request->all());

        //Create new calendar
        $calendar = Calendar::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
        ]);

        //Create all the colors and relations
        foreach($request->colors as $color)
        {
            //Check if color exist, otherwise create it.
            $color = Color::firstOrCreate(['hex_value' => $color['hex_value']]);
            
            //Then just add the relationship.
            if(!$calendar->hasColor($color)){
                $calendar->colors()->attach($color);
            }
        }
        
      return new CalendarsResource($calendar);
    }

    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarsResource($calendar);
    }

    public function update(UpdateCalendarRequest $request, Calendar $calendar )
    {
        if(Auth::user()->id !== $calendar->user_id){
            return $this->error('', 'You are not authorized', 403);
        }
       
        //Handle validation?
        $request->validated($request->all());
        
        $calendarColors = [];

        //Check if dates have a color that request is not going to pass.
        foreach($calendar->dates as $date){

            $colorFound = false;
            
            foreach($request->colors as $color){
                
                $colorObj = Color::where(['hex_value' => $color['old_hex_value']])->first();

                if($colorObj != null){
                    if($colorObj->id === $date->color_id){
                        $colorFound = true;
                    }
                }
            }

            if(!$colorFound){
                return $this->error('', 'One the dates in the calendar has a color you are removing. Send old colors from all dates to replace them correctly. Calendar not updated', 409);
            }
        }
            

        foreach($request->colors as $color)
        {
            $colorObj = Color::firstOrCreate(['hex_value' => $color['new_hex_value']]);

            //If you have an old color and a new color.
            if($color['old_hex_value'] != null && $color['new_hex_value'] != $color['old_hex_value']){
                           var_dump($color['new_hex_value'], $color['old_hex_value']);
                //Update all dates from this calendar with the new color.
                foreach($calendar->dates as $date){
                    if($date->color->hex_value === $color['old_hex_value']){
                        $date->color_id = $colorObj->id;
                        $date->save();
                    }
                }
            }
            array_push($calendarColors, $colorObj['id']);
        }

        

        $calendar->colors()->sync($calendarColors, true);


        $calendar->update([
            'name' => $request->name,
        ]);

        return new CalendarsResource($calendar->fresh());
    }

      public function destroy(Calendar $calendar)
    {
        if($calendar != null){
            return $this->error('', 'No calendar found', 404); 
        }
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : $calendar->delete();
    }

    private function isNotAuthorized($calendar)
    {
        if(Auth::user()->id !== $calendar->user_id){
            return $this->error('', 'You are not authorized', 403);
        }
    }
}
