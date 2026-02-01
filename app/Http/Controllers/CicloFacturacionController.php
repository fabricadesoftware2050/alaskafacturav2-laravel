<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CicloFacturacion; // Usamos el nuevo modelo de Configuración
use App\Models\Empresa;
use App\Models\PeriodoFacturacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicloFacturacionController extends Controller
{
    /**
     * LISTAR: Muestra las CONFIGURACIONES de ciclos (Ej: Zona Norte, Zona Sur).
     * No muestra los meses facturados, solo las reglas.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->first();

            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 403);
            }

            $perPage = min($request->get('per_page', 10), 100);
            
            // Iniciamos consulta
            $query = CicloFacturacion::where('company_id', $company->id);

            // 1. Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%");
                });
            }

            if ($request->filled('activo')) {
                $query->where('activo', $request->boolean('activo'));
            }

            // 2. Eager Loading: Opcional, traer el último periodo abierto para ver en qué van
            $query->with(['periodos' => function($q) {
                $q->orderBy('id', 'desc')->limit(1); // Traer solo el último mes generado
            }]);

            // 3. Paginación
            $ciclos = $query->orderBy('codigo', 'asc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $ciclos->items(),
                'meta' => [
                    'current_page' => $ciclos->currentPage(),
                    'total' => $ciclos->total(),
                    'last_page' => $ciclos->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * CREAR: Define una nueva REGLA de facturación.
     * Ya NO se envían fechas (febrero, marzo), solo reglas (día 1, día 5).
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->first();

            // 1. Validación de REGLAS (No de fechas calendario)
            $data = $request->validate([
                'codigo' => [
                    'required', 'string', 'max:50',
                    // Validamos que el código sea único dentro de la empresa
                    \Illuminate\Validation\Rule::unique('ciclos')->where(function ($query) use ($company) {
                        return $query->where('company_id', $company->id);
                    })
                ],
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'zona_id' => 'nullable|integer', 
                
                // Configuración de Días (La lógica abstracta)
                'dia_inicio_lectura_sugerido' => 'required|integer|between:1,28',
                'dias_duracion_lectura'       => 'required|integer|min:0',
                'dia_emision_sugerido'        => 'required|integer|between:1,31',
                'dias_para_vencimiento'       => 'required|integer|min:1',
            ]);

            $data['company_id'] = $company->id;
            $data['activo'] = true;

            // 2. Crear la configuración
            $ciclo = CicloFacturacion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Configuración de Ciclo creada. Ahora puedes abrir periodos.',
                'data' => $ciclo,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json(['success' => false, 'errors' => $ex->errors()], 422);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * NUEVO MÉTODO CRÍTICO: Abrir un Periodo Mensual
     * Esto convierte la "Regla" en "Fechas Reales" para un mes específico.
     * POST /ciclos/{id}/abrir-periodo
     */
    public function abrirPeriodo(Request $request, $id)
    {
        $request->validate([
            'mes' => 'required|integer|between:1,12',
            'anio' => 'required|integer|min:2024',
        ]);

        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->first();
            
            // 1. Buscar el ciclo padre
            $ciclo = CicloFacturacion::where('id', $id)
                          ->where('company_id', $company->id)
                          ->firstOrFail();

            // 2. Verificar si ya existe ese mes (Para no duplicar)
            $existe = PeriodoFacturacion::where('ciclo_id', $ciclo->id)
                        ->where('mes', $request->mes)
                        ->where('anio', $request->anio)
                        ->exists();

            if ($existe) {
                return response()->json(['success' => false, 'message' => 'Este periodo ya está abierto.'], 422);
            }

            // 3. INVOCAR LA MAGIA DEL MODELO
            // Aquí se calculan las fechas automáticamente
            $nuevoPeriodo = $ciclo->generarPeriodo($request->mes, $request->anio);

            return response()->json([
                'success' => true,
                'message' => "Periodo {$request->mes}/{$request->anio} generado correctamente.",
                'data' => $nuevoPeriodo // Retorna las fechas calculadas (vencimiento, corte, etc)
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * MOSTRAR: Ver detalle del ciclo y sus últimos periodos
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $ciclo = CicloFacturacion::with(['periodos' => function($q) {
                        $q->orderBy('anio', 'desc')->orderBy('mes', 'desc')->limit(12);
                    }])
                    ->where('id', $id)
                    ->where('company_id', $company->id)
                    ->firstOrFail();

            return response()->json(['success' => true, 'data' => $ciclo]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ciclo no encontrado'], 404);
        }
    }

    /**
     * ACTUALIZAR: Modifica las reglas futuras.
     * Nota: Esto no cambia las fechas de facturas viejas (Periodos), solo la configuración.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();
            $ciclo = CicloFacturacion::where('id', $id)->where('company_id', $company->id)->firstOrFail();

            $data = $request->validate([
                'nombre' => 'string|max:100',
                'descripcion' => 'nullable|string',
                'dia_inicio_lectura_sugerido' => 'integer|between:1,28',
                'dia_emision_sugerido' => 'integer|between:1,31',
                'dias_para_vencimiento' => 'integer|min:1',
                'activo' => 'boolean'
            ]);

            $ciclo->update($data);

            return response()->json(['success' => true, 'message' => 'Reglas actualizadas', 'data' => $ciclo]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * ELIMINAR (Soft Delete)
     * Envía el ciclo a la papelera, pero mantiene la integridad histórica.
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            // 1. Buscar el ciclo asegurando que pertenece a la empresa
            $ciclo = CicloFacturacion::where('id', $id)
                          ->where('company_id', $company->id)
                          ->firstOrFail();

            // 2. VALIDACIÓN DE NEGOCIO CRÍTICA
            // Verificar si hay un periodo "vivo" (Abierto, En Lectura o Facturando).
            // Si eliminamos la regla mientras se está usando, el proceso mensual fallará.
            $tieneProcesoActivo = $ciclo->periodos()
                                        ->whereIn('estado', ['ABIERTO', 'EN_LECTURA', 'FACTURADO'])
                                        ->exists();

            if ($tieneProcesoActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el ciclo: Tiene un periodo de facturación en curso. Termine o anule el periodo actual primero.'
                ], 409); // 409 Conflict
            }

            // 3. Ejecutar Soft Delete
            // Al usar SoftDeletes en el modelo, los periodos históricos (facturas viejas)
            // seguirán existiendo en la BD, lo cual es correcto para auditoría.
            $ciclo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo eliminado correctamente.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Ciclo no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
} // Fin de la clase CicloController
