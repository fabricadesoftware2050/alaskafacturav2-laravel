<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmpresaController extends Controller
{
    /**
     * Crear o actualizar empresa (UPSERT por NIT + usuario)
     */
    public function store(Request $request)
    {
        try{
        $data = $request->validate([
            'logo' => 'nullable|string',
            'escudo' => 'nullable|string',
            'firma' => 'nullable|string',

            'tipo_identificacion' => 'required|string|max:20',
            'numero_identificacion' => 'required|string|max:30',
            'dv' => 'nullable|string|max:5',
            'razon_social' => 'required|string',
            'responsabilidad' => 'string|max:50',
            'nombre_comercial' => 'nullable|string',
            'direccion' => 'string',
            'ubicacion' => 'string',
            'telefono' => 'string|max:20',
            'correo' => 'email',
            'sitio_web' => 'nullable|string',

            'factura_defecto' => 'nullable|string',
            'emitir_estandar' => 'nullable|boolean',
            'emitir_exportacion' => 'nullable|boolean',
            'emitir_aiu' => 'nullable|boolean',
            'emitir_salud' => 'nullable|boolean',
            'mandato_ingresos' => 'nullable|boolean',
            'regimen_simple' => 'nullable|boolean',
            'gran_contribuyente' => 'nullable|boolean',
            'autorretenedor' => 'nullable|boolean',
            'agente_retencion_iva' => 'nullable|boolean',
            
            'representante_nombre' => 'required|string',
            'representante_cedula' => 'required|string|max:30',
            'representante_cargo' => 'required|string',
            'representante_celular' => 'string|max:20',
            'representante_correo' => 'email',
            'correo_alegra' => 'nullable|email|max:100',
            'token_alegra' => 'nullable|string|max:200'
        ]);

        $usuarioId = auth()->user()->id;

        $company = Empresa::updateOrCreate(
            [
                'numero_identificacion' => $data['numero_identificacion'],
                'usuario_id' => $usuarioId,
            ],
            array_merge($data, [
                'usuario_id' => $usuarioId,
            ])
        );

        // 🔑 LIMPIAR CACHE
        Cache::forget("empresa_usuario_{$usuarioId}");

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
        /*
        $company = Empresa::where('usuario_id', $idUser)->first();

        return response()->json([
            'success' => true,
            'message' => 'Datos de la empresa guardados correctamente',
            'data' => $company,
        ]);*/

        try {
            $idUser = auth()->user()->id;


            $cacheKey = "empresa_usuario_{$idUser}";

            $company = Cache::rememberForever($cacheKey, function () use ($idUser) {
                return Empresa::where('usuario_id', $idUser)->first();
            });

            return response()->json([
                'success' => true,
                'data' => $company,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la empresa'
            ], 500);
        }
    }

    /**
     * NO permitir eliminar
     */
    public function destroy()
    {
        return response()->json([
            'message' => 'No está permitido eliminar la empresa'
        ], 403);
    }
}
