<?php

namespace App\Http\Controllers;

use App\Http\Resources\ColorsResource;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorsController extends Controller
{
  
    public function index()
    {
        return ColorsResource::collection(
            Color::all());
    }

    public function store(Request $request)
    {
        //
    }
 
    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
