<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarsRequest;
use App\Http\Requests\StoreColorsRequest;
use App\Http\Resources\CalendarsResource;
use App\Models\Calendar;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
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

    public function store(StoreCalendarsRequest $request)
    {


        //Validate the colors
       /* foreach($request->colors as $color_request)
        {
            $color_request = new StoreColorsRequest;
            var_dump($color_request);
        }
*/

        //Check if they exixt in color table
            //Create them if they don't
        //Add the colors to the calendar via pivot table (calendar_color)

        $request->validated($request->all());
        
        $calendar = Calendar::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
        ]);

        return new CalendarsResource($calendar);
    }

    public function show(Calendar $calendar)
    {
        return $this->isNotAuthorized($calendar) ? $this->isNotAuthorized($calendar) : new CalendarsResource($calendar);
    }

    public function update(Request $request, Calendar $calendar )
    {
        if(Auth::user()->id !== $calendar->user_id){
            return $this->error('', 'You are not authorized', 403);
        }
        $calendar->update($request->all());

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
