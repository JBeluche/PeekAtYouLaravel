<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\StoreColorsRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorsController extends Controller
{
    public function index()
    {
        return ColorResource::collection(
            Color::all()
        );
    }

    public function store(StoreColorRequest $request)
    {
        $request->validated($request->all());

        $color = Color::create([
            'hex_value' => $request->hex_value,
        ]);

      return new ColorResource($color);
    }   

    public function storeMany(StoreColorsRequest $request)
    {
        $request->validated($request->all());


        $colors = [];

        foreach($request->colors as $color)
        {
            $colorObj = Color::where(['hex_value' => $color['hex_value']])->first();

            if($colorObj === null){
                $colorToAdd = Color::create([
                    'hex_value' => $color['hex_value'],
                ]);
                array_push($colors, $colorToAdd);
            }
          

      }


        return ColorResource::collection(
            $colors
        );
    }   

    public function show(Color $color)
    {
        return new ColorResource($color);
    }

    public function update(StoreColorRequest $request, Color $color)
    {
        $request->validated($request->all());
        $color->update([
            'hex_value' => $request->hex_value,
        ]);

        return new ColorResource($color->fresh());

    }

    public function destroy(Color $color)
    {
        if($color != null){
            return $this->error('', 'No color found', 404); 
        }
        return $color->delete();
    }
    
}
