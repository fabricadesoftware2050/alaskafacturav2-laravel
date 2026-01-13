<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MunicipioDepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $municipios = Municipio::with('departamento')->get();

            $resultado = $municipios->map(function ($municipio) {
                return $municipio->municipio . ', ' . $municipio->departamento->nombre;
            });

            return response()->json(['municipios' => $resultado]);

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
