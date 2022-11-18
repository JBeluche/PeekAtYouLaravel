<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePalettesRequest;
use App\Http\Requests\UpdatePalettesRequest;
use App\Http\Resources\PalettesResource;
use App\Models\Color;
use App\Models\Palette;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PalettesController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return PalettesResource::collection(
            Palette::where('user_id', Auth::user()->id)
                ->get()
        );
    }

    public function store(StorePalettesRequest $request)
    {
        $palette = Palette::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
        ]);

        foreach($request->colors as $color)
        {
            $color = Color::firstOrCreate(['hex_value' => $color['hex_value']]);
            
            //Then just add the relationship.
            if(!$palette->hasColor($color)){
                $palette->colors()->attach($color);
            }
        }
        
        return new PalettesResource($palette);
    }

    public function show(Palette $palette)
    {
        return $this->isNotAuthorized($palette) ? $this->isNotAuthorized($palette) : new PalettesResource($palette);
    }

    public function update(UpdatePalettesRequest $request, Palette $palette)
    {
        if(Auth::user()->id !== $palette->user_id){
            return $this->error('', 'You are not authorized', 403);
        }

        $paletteColors = [];

        foreach($request->colors as $color)
        {
            $colorObj = Color::firstOrCreate(['hex_value' => $color['hex_value']]);

            array_push($paletteColors, $colorObj['id']);
        }

        $palette->colors()->sync($paletteColors, true);

        $palette->update([
            'name' => $request->name,
        ]);

        return new PalettesResource($palette->fresh());
    }

    public function destroy(Palette $palette)
    {
        return $this->isNotAuthorized($palette) ? $this->isNotAuthorized($palette) : $palette->delete();
    }

    private function isNotAuthorized($palette)
    {
        if(Auth::user()->id !== $palette->user_id){
            return $this->error('', 'You are not authorized', 403);
        }
    }

}
