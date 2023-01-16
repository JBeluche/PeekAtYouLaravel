<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\StoreColorsRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;

class ColorsController extends Controller
{
    public function index()
    {
        return ColorResource::collection(
            Color::all()
        );
    }

    public function show(Color $color)
    {
        return new ColorResource($color);
    }

    public function store(StoreColorRequest $request)
    {
        $color = Color::create([
            'hex_value' => $request->hex_value,
        ]);

        return new ColorResource($color);
    }

    public function storeMany(StoreColorsRequest $request)
    {
        $colors = [];

        foreach ($request->colors as $color) {

            $colorToAdd = Color::create([
                'hex_value' => $color['hex_value'],
            ]);

            array_push($colors, $colorToAdd);
        }

        return ColorResource::collection(
            $colors
        );
    }

    public function update(StoreColorRequest $request, Color $color)
    {
        $color->update([
            'hex_value' => $request->hex_value,
        ]);

        return new ColorResource($color->fresh());
    }

    public function destroy(Color $color)
    {
        return $color->delete();
    }
}
