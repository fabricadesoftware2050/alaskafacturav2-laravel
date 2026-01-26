<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PeriodoFacturacion;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;


class PeriodoFacturacionController extends Controller
{

    public function index(Request $request)
{
    try {
        $user = auth()->user();
        $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

        $perPage = min($request->get('per_page', 10), 100);

        $query = PeriodoFacturacion::where('company_id', $company->id);

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            $query->where(function ($q) use ($estado) {
                $q->where('codigo', $estado);
            });
        }


        $zonas = $query
            ->orderBy('fecha_inicio')
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
                'max:10',
            ],
            'feha_inicio' => 'required|date|max:16',
            'feha_fin' => 'required|date|max:16',
            'fecha_lectura' => 'nullable|date|max:16',
            'fecha_facturacion' => 'nullable|date|max:16',
            'fecha_vencimiento' => 'nullable|date|max:16',
            'estado' => "required|string|in:'ABIERTO','EN LECTURA','FACTURADO','CERRADO'",
        ]);


        

        $model = PeriodoFacturacion::create($data);



        return response()->json([
            'success' => true,
            'message' => 'Datos guardados correctamente',
            'data' => $model,
        ]);
        } catch (\Exception $ex) {
        return response()->json([
            'error' => 'Register failed',
            'message' => $ex->getMessage()
        ], 500);
    }
    }

    public function update(Request $request, $id)
{
    try {
        $user = auth()->user();
        $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

        $model = PeriodoFacturacion::where('id', $id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $data = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:10',
            ],
            'feha_inicio' => 'required|date|max:16',
            'feha_fin' => 'required|date|max:16',
            'fecha_lectura' => 'nullable|date|max:16',
            'fecha_facturacion' => 'nullable|date|max:16',
            'fecha_vencimiento' => 'nullable|date|max:16',
            'estado' => "required|string|in:'ABIERTO','EN LECTURA','FACTURADO','CERRADO'",
        ]);

        $model->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Información actualizada',
            'data' => $model,
        ]);

    } catch (\Exception $ex) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la zona',
            'error' => $ex->getMessage()
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
    
            $model= PeriodoFacturacion::where('company_id', $company->id)->first();
    
            return response()->json([
                'success' => true,
                'message' => 'Datos consultados correctamente',
                'data' => $model,
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
       
         $model= PeriodoFacturacion::findOrFail($id);
         $model->delete();
          return response()->json([
            'message' => 'Operaciópn exitosa'
        ], 200);
        
    }
}
