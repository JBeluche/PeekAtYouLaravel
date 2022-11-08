<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarsRequest;
use App\Http\Requests\StoreColorsRequest;
use App\Http\Requests\UpdateCalendarRequest;
use App\Http\Resources\CalendarsResource;
use App\Models\Calendar;
use App\Models\Color;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CalendarsController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return CalendarsResource::collection(
            Calendar::where('user_id', Auth::user()->id)->get()
        );
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
            $calendar->colors()->attach($color);
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

        foreach($request->colors as $color)
        {
            //Sync the relationships
            $colorObj = Color::firstOrCreate(['hex_value' => $color['hex_value']]);
            
            //Then just add the relationship.
            array_push($calendarColors, $colorObj['id']);
        }

        //var_dump($calendarColors);
        $calendar->colors()->sync($calendarColors, true);


        $calendar->update([
            'name' => $request->name,
        ]);

        return new CalendarsResource($calendar);
    }

      public function destroy(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : $calendar->delete();
    }

    private function isNotAuthorized($calendar)
    {
        if(Auth::user()->id !== $calendar->user_id){
            return $this->error('', 'You are not authorized', 403);
        }
    }
}
