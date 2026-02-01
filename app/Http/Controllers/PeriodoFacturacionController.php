<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PeriodoFacturacion;
use App\Models\Empresa;
use App\Models\CicloFacturacion;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PeriodoFacturacionController extends Controller
{
    /**
     * LISTAR PERIODOS
     * Muestra el historial de facturaciones (Enero, Febrero...)
     * filtrando por los ciclos de la empresa.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $perPage = min($request->get('per_page', 10), 100);

            // 1. QUERY BASE: Usamos whereHas para filtrar por la empresa del Ciclo padre
            // Ya no existe 'company_id' en la tabla periodos, hay que llegar a través del ciclo.
            $query = PeriodoFacturacion::with('ciclo') // Traemos el nombre del ciclo (Zona Norte, etc)
                ->whereHas('ciclo', function (Builder $q) use ($company) {
                    $q->where('company_id', $company->id);
                });

            // 🔍 BUSQUEDA: Buscamos por el NOMBRE del Ciclo o por Año
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    // Buscar si coincide con el año (ej: "2026")
                    $q->where('anio', 'like', "%{$search}%")
                      // O buscar dentro del ciclo relacionado (ej: "Norte")
                      ->orWhereHas('ciclo', function($subQ) use ($search){
                          $subQ->where('nombre', 'like', "%{$search}%")
                               ->orWhere('codigo', 'like', "%{$search}%");
                      });
                });
            }

            // Filtros Específicos
            if ($request->filled('status')) {
                $query->where('estado', $request->status);
            }
            if ($request->filled('mes')) {
                $query->where('mes', $request->mes);
            }
            if ($request->filled('anio')) {
                $query->where('anio', $request->anio);
            }
            if ($request->filled('ciclo_id')) {
                $query->where('ciclo_id', $request->ciclo_id);
            }

            // Ordenar: Primero lo más reciente
            $model = $query->orderBy('anio', 'desc')
                           ->orderBy('mes', 'desc')
                           ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $model->items(),
                'meta' => [
                    'current_page' => $model->currentPage(),
                    'total' => $model->total(),
                    'last_page' => $model->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al consultar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * CREAR MANUALMENTE (STORE)
     * Nota: Idealmente deberías usar CicloController::abrirPeriodo para que sea automático,
     * pero este método permite creación manual completa si se requiere.
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $data = $request->validate([
                'ciclo_id' => 'required|exists:ciclos,id', // Debe existir el ciclo padre
                'mes' => 'required|integer|between:1,12',
                'anio' => 'required|integer|digits:4',
                
                // Fechas Cronograma
                'fecha_inicio_lectura' => 'required|date',
                'fecha_fin_lectura' => 'nullable|date|after_or_equal:fecha_inicio_lectura',
                'fecha_emision' => 'required|date|after_or_equal:fecha_inicio_lectura',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
                'fecha_suspension' => 'nullable|date|after_or_equal:fecha_vencimiento',
                
                'estado' => 'required|in:ABIERTO,EN_LECTURA,FACTURADO,CERRADO',
            ]);

            // VALIDACIÓN DE SEGURIDAD:
            // Asegurar que el ciclo_id enviado pertenezca a la empresa del usuario
            $ciclo = CicloFacturacion::where('id', $request->ciclo_id)
                          ->where('company_id', $company->id)
                          ->first();

            if (!$ciclo) {
                return response()->json(['success' => false, 'message' => 'El ciclo seleccionado no pertenece a su empresa'], 403);
            }

            // Verificar duplicados (Unique constraint catch)
            $existe = PeriodoFacturacion::where('ciclo_id', $ciclo->id)
                        ->where('mes', $request->mes)
                        ->where('anio', $request->anio)
                        ->exists();

            if($existe) {
                return response()->json(['success' => false, 'message' => 'Ya existe un periodo para este mes y año en este ciclo.'], 422);
            }

            $model = PeriodoFacturacion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Periodo creado correctamente',
                'data' => $model,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json(['success' => false, 'errors' => $ex->errors()], 422);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * ACTUALIZAR FECHAS O ESTADO
     * Útil cuando hay días festivos imprevistos y hay que mover la fecha de vencimiento manualmente.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            // Buscar asegurando la cadena de propiedad: Periodo -> Ciclo -> Empresa
            $model = PeriodoFacturacion::where('id', $id)
                ->whereHas('ciclo', function ($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->firstOrFail();

            // Reglas de validación (Solo permitimos editar fechas y estado, no el ciclo ni el mes)
            $data = $request->validate([
                'fecha_inicio_lectura' => 'required|date',
                'fecha_fin_lectura' => 'nullable|date|after_or_equal:fecha_inicio_lectura',
                'fecha_emision' => 'required|date|after_or_equal:fecha_inicio_lectura', // Factura se emite después de leer
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_emision',
                'fecha_suspension' => 'nullable|date|after_or_equal:fecha_vencimiento',
                'estado' => 'required|in:ABIERTO,EN_LECTURA,FACTURADO,CERRADO,ANULADO',
            ]);

            $model->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Periodo actualizado correctamente',
                'data' => $model,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json(['success' => false, 'message' => 'Periodo no encontrado o acceso denegado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }

    /**
     * VER DETALLE
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $model = PeriodoFacturacion::with('ciclo')
                ->where('id', $id)
                ->whereHas('ciclo', function ($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $model,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Periodo no encontrado'], 404);
        }
    }

    /**
     * ELIMINAR (Solo si no hay facturas generadas)
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $model = PeriodoFacturacion::where('id', $id)
                ->whereHas('ciclo', function ($q) use ($company) {
                    $q->where('company_id', $company->id);
                })
                ->firstOrFail();

            // VALIDACIÓN LÓGICA: ¿Podemos borrar esto?
            // Si el estado es FACTURADO, significa que ya hay deuda creada en el sistema. No deberíamos borrarlo.
            if ($model->estado === 'FACTURADO' || $model->estado === 'CERRADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un periodo que ya ha sido facturado o cerrado. Intente anularlo.'
                ], 409);
            }

            $model->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periodo eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }
}