<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CicloFacturacion; // Asegúrate de crear este Modelo
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CicloFacturacionController extends Controller
{
    /**
     * Listar Ciclos con paginación y búsqueda
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la empresa asociada al usuario'
                ], 403);
            }

            $perPage = min($request->get('per_page', 10), 100);
            
            // Iniciar consulta filtrada por empresa
            $query = CicloFacturacion::where('company_id', $company->id);

            // 🔍 Búsqueda por código o nombre
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('nombre', 'like', "%{$search}%");
                });
            }

            // Filtro por Estado
            if ($request->filled('status')) {
                $query->where('estado', $request->status);
            }

            // Filtro por Año/Mes (Muy útil para la vista de "Facturación de este mes")
            if ($request->filled('anio')) {
                $query->where('periodo_anio', $request->anio);
            }
            if ($request->filled('mes')) {
                $query->where('periodo_mes', $request->mes);
            }

            // Ordenar: Lo más reciente primero
            $model = $query->orderBy('periodo_anio', 'desc')
                           ->orderBy('periodo_mes', 'desc')
                           ->orderBy('codigo', 'asc')
                           ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Ciclos consultados correctamente',
                'data' => $model->items(),
                'meta' => [
                    'current_page' => $model->currentPage(),
                    'per_page'     => $model->perPage(),
                    'total'        => $model->total(),
                    'last_page'    => $model->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar los ciclos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Crear un nuevo Ciclo
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->first();

            if (!$company) {
                return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 403);
            }

            // Validaciones Estrictas
            $data = $request->validate([
                'codigo' => 'required|string|max:50',
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'zona_id' => 'nullable|integer|exists:zonas,id', // Asumiendo tabla zonas
                
                // Periodo
                'periodo_mes' => 'required|integer|between:1,12',
                'periodo_anio' => 'required|integer|digits:4',

                // Fechas Cronograma (Lógica temporal)
                'fecha_inicio_lectura' => 'required|date',
                'fecha_fin_lectura' => 'nullable|date|after_or_equal:fecha_inicio_lectura',
                'fecha_facturacion' => 'required|date|after_or_equal:fecha_inicio_lectura',
                'fecha_pago_oportuno' => 'nullable|date|after_or_equal:fecha_facturacion',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_facturacion',
                'fecha_suspension' => 'required|date|after_or_equal:fecha_vencimiento',

                // Configuración
                'dia_corte_sugerido' => 'nullable|integer|between:1,31',
                'dias_vencimiento' => 'nullable|integer|min:1',
                'estado' => 'required|in:ABIERTO,EN_LECTURA,FACTURADO,CERRADO',
            ]);

            // Validación de Unicidad Manual (Composite Unique Key)
            // No podemos permitir el mismo ciclo en el mismo mes para la misma empresa
            $exists = CicloFacturacion::where('company_id', $company->id)
                        ->where('codigo', $request->codigo)
                        ->where('periodo_mes', $request->periodo_mes)
                        ->where('periodo_anio', $request->periodo_anio)
                        ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false, 
                    'message' => "El ciclo '{$request->codigo}' ya existe para este periodo ({$request->periodo_mes}-{$request->periodo_anio})."
                ], 422);
            }

            $data['company_id'] = $company->id;
            
            $model = CicloFacturacion::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Ciclo creado exitosamente',
                'data' => $model,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json(['success' => false, 'message' => 'Error de validación', 'errors' => $ex->errors()], 422);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => 'Error al guardar', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Actualizar Ciclo
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            // Buscar asegurando pertenencia a la empresa
            $model = CicloFacturacion::where('id', $id)
                          ->where('company_id', $company->id)
                          ->firstOrFail();

            // Si el ciclo ya está CERRADO o FACTURADO, restringir edición de fechas críticas
            // (Opcional: lógica de negocio de seguridad)
            /* if ($model->estado === 'CERRADO') {
                return response()->json(['message' => 'No se puede editar un ciclo cerrado'], 403);
            }
            */

            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'zona_id' => 'nullable|integer',
                
                // Permitimos editar fechas si hubo error humano
                'fecha_inicio_lectura' => 'required|date',
                'fecha_fin_lectura' => 'nullable|date|after_or_equal:fecha_inicio_lectura',
                'fecha_facturacion' => 'required|date|after_or_equal:fecha_inicio_lectura',
                'fecha_pago_oportuno' => 'nullable|date|after_or_equal:fecha_facturacion',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_facturacion',
                'fecha_suspension' => 'required|date|after_or_equal:fecha_vencimiento',
                
                'estado' => 'required|in:ABIERTO,EN_LECTURA,FACTURADO,CERRADO',
                
                // Nota: Generalmente NO permitimos editar codigo/mes/año de un ciclo ya creado para no romper integridad
            ]);

            $model->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Ciclo actualizado correctamente',
                'data' => $model,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return response()->json(['success' => false, 'message' => 'Ciclo no encontrado'], 404);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar', 'error' => $ex->getMessage()], 500);
        }
    }

    /**
     * Ver detalle de un ciclo
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $model = CicloFacturacion::where('id', $id)
                          ->where('company_id', $company->id)
                          ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $model,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ciclo no encontrado'], 404);
        }
    }

    /**
     * Eliminar ciclo (Soft Delete)
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $model = CicloFacturacion::where('id', $id)
                          ->where('company_id', $company->id)
                          ->firstOrFail();

            // Validación extra: No borrar si ya tiene facturas generadas (Lógica de negocio futura)
            // if ($model->facturas()->exists()) { ... }

            $model->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ciclo eliminado correctamente (papelera)'
            ], 200);

        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }
}