<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Estrato;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstratoController extends Controller
{
    /**
     * Listado de estratos por empresa
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $perPage = min($request->get('per_page', 10), 100);

            $query = Estrato::where('company_id', $company->id);

            // 🔍 SEARCH
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhere('clase_uso', 'like', "%{$search}%");
                });
            }

            // 🎯 Filtro residencial
            if ($request->residencial === '1' || $request->residencial === '0') {
                $query->where('residencial', $request->residencial);
            }

            $estratos = $query
                ->orderBy('estrato_nivel')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Datos de los estratos consultados correctamente',
                'data' => $estratos->items(),
                'meta' => [
                    'current_page' => $estratos->currentPage(),
                    'per_page'     => $estratos->perPage(),
                    'total'        => $estratos->total(),
                    'last_page'    => $estratos->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los estratos',
            ], 500);
        }
    }

    /**
     * Crear estrato
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $data = $request->validate([
                'codigo' => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('estratos')
                        ->where(fn ($q) => $q->where('company_id', $company->id)),
                ],
                'descripcion' => 'required|string|max:255',
                'clase_uso' => 'required|string|max:50',
                'estrato_nivel' => 'required|integer|min:0|max:10',

                'codigo_clase_uso' => 'nullable|string|max:50',
                'factor_produccion' => 'nullable|string|max:50',

                'acueducto' => 'nullable|array',
                'alcantarillado' => 'nullable|array',
                'aseo' => 'nullable|array',

                'sui_acueducto' => 'nullable|string|max:10',
                'sui_alcantarillado' => 'nullable|string|max:10',
                'sui_aseo' => 'nullable|string|max:10',

                'tipo_productor' => 'nullable|string|max:50',
                'residencial' => 'boolean',
            ]);

            $data['company_id'] = $company->id;

            $estrato = Estrato::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Estrato creado correctamente',
                'data' => $estrato,
            ], 201);

        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el estrato',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar estrato (de la empresa)
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $estrato = Estrato::where('id', $id)
                ->where('company_id', $company->id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Datos del estrato consultados correctamente',
                'data' => $estrato,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estrato',
            ], 500);
        }
    }

    /**
     * Actualizar estrato
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $estrato = Estrato::where('id', $id)
                ->where('company_id', $company->id)
                ->firstOrFail();

            $data = $request->validate([
                'codigo' => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('estratos')
                        ->where(fn ($q) => $q->where('company_id', $company->id))
                        ->ignore($estrato->id),
                ],
                'descripcion' => 'required|string|max:255',
                'clase_uso' => 'required|string|max:50',
                'estrato_nivel' => 'required|integer|min:0|max:10',

                'codigo_clase_uso' => 'nullable|string|max:50',
                'factor_produccion' => 'nullable|string|max:50',

                'acueducto' => 'nullable|array',
                'alcantarillado' => 'nullable|array',
                'aseo' => 'nullable|array',

                'sui_acueducto' => 'nullable|string|max:10',
                'sui_alcantarillado' => 'nullable|string|max:10',
                'sui_aseo' => 'nullable|string|max:10',

                'tipo_productor' => 'nullable|string|max:50',
                'residencial' => 'boolean',
            ]);

            $estrato->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Estrato actualizado correctamente',
                'data' => $estrato,
            ]);

        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estrato',
                'error' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar estrato
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $company = Empresa::where('usuario_id', $user->id)->firstOrFail();

            $estrato = Estrato::where('id', $id)
                ->where('company_id', $company->id)
                ->firstOrFail();

            $estrato->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estrato eliminado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el estrato',
            ], 500);
        }
    }
}
