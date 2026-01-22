<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;
use Illuminate\Support\Facades\Cache;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        try {
            


            return response()->json(['data' => json_encode('
            {
            "name": "ACUEDUCTO",
            "description": null,
            "status": "active",
            "id": "18"
        },
        {
            "name": "ALCANTARRILLADO",
            "description": "Servicio de desague",
            "status": "active",
            "id": "19"
        }
            ')]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar municipios ' . $e->getMessage()], 500);
        }

  

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
