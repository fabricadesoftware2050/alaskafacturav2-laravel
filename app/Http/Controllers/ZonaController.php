<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Zona;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;


class ZonaController extends Controller
{
    /**
     * Crear o actualizar empresa (UPSERT por NIT + usuario)
     */
    public function store(Request $request)
    {
        try{
            $idUser = auth()->user()->id;
        $company = Empresa::where('usuario_id', $idUser)->first();
    
        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('zonas')->where(fn ($q) =>
                    $q->where('company_id', $company->id)
                ),
            ],
            'nombre' => 'required|string|max:30',
        ]);


        

        $zona = Zona::updateOrCreate(
            [
                'codigo' => $data['codigo'],
                'company_id' => $company->id,
            ],
            [
                'nombre' => $data['nombre'],
            ]
        );



        return response()->json([
            'success' => true,
            'message' => 'Datos de la empresa guardados correctamente',
            'data' => $company,
        ]);
        } catch (\Exception $ex) {
        return response()->json([
            'error' => 'Register failed',
            'message' => $ex->getMessage()
        ], 500);
    }
    }

    /**
     * Obtener empresa del usuario autenticado
     */
    public function show(string $idNoUsado)
    {
        
        
        try {
            $idUser = auth()->user()->id;
            $company = Empresa::where('usuario_id', $idUser)->first();
    
            $zona= Zona::where('company_id', $company ->id)->first();
    
            return response()->json([
                'success' => true,
                'message' => 'Datos de la zona consultados correctamente',
                'data' => $zona,
            ]);
            
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la zona'
            ], 500);
        }
    }

    /**
     * NO permitir eliminar
     */
    public function destroy($id)
    {
        try{
         $zona= Zona::findOrFail($id);
         $zona->delete();
          return response()->json([
            'message' => 'Operaciópn exitosa'
        ], 2000);
        } catch (\Exception $e) {


        return response()->json([
            'message' => 'No está permitido eliminar la empresa'
        ], 403);
        }
    }
}
