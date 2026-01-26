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

    public function index(Request $request)
{
    try {
        $user = auth()->user();
        $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

        $perPage = min($request->get('per_page', 10), 100);

        $query = Zona::where('company_id', $company->id);

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        $zonas = $query
            ->orderBy('codigo')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Datos de las zonas consultados correctamente',
            'data' => $zonas->items(),
            'meta' => [
                'current_page' => $zonas->currentPage(),
                'per_page'     => $zonas->perPage(),
                'total'        => $zonas->total(),
                'last_page'    => $zonas->lastPage(),
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener las zonas',
        ], 500);
    }
}


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
            'message' => 'Datos guardados correctamente',
            'data' => $zona,
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
    
            $zona= Zona::where('company_id', $company->id)->first();
    
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
       
         $zona= Zona::findOrFail($id);
         $zona->delete();
          return response()->json([
            'message' => 'Operaciópn exitosa'
        ], 200);
        
    }
}
