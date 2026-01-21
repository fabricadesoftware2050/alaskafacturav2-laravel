<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;
use Illuminate\Support\Facades\Cache;


class MunicipioDepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /*
        try {
            $municipios = Municipio::with('departamento')
                ->join('departamentos', 'departamentos.id_departamento', '=', 'municipios.departamento_id')
                ->orderBy('departamentos.departamento')
                ->orderBy('municipios.municipio')
                ->select('municipios.*')
                ->get();

            $resultado = $municipios->map(function ($municipio) {
                return strtoupper($municipio->municipio) . ', ' . strtoupper($municipio->departamento->departamento);
            });


            return response()->json(['municipios' => $resultado]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar municipios ' . $e->getMessage()], 500);
        }*/

    try {

        $resultado = Cache::rememberForever('municipios_departamentos', function () {

            return Municipio::join(
                    'departamentos',
                    'departamentos.id_departamento',
                    '=',
                    'municipios.departamento_id'
                )
                ->orderBy('departamentos.departamento')
                ->orderBy('municipios.municipio')
                ->selectRaw("
                    UPPER(municipios.municipio) as municipio,
                    UPPER(departamentos.departamento) as departamento
                ")
                ->get()
                ->map(function ($row) {
                    return "{$row->municipio}, {$row->departamento}";
                });

        });

        return response()->json(['municipios' => $resultado]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Error al listar municipios'
        ], 500);
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
