<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
class FileController extends Controller
{
    public function subir(Request $request)
    {
        try {
            $user = auth()->user();
            $request->validate([
            'file' => 'required|file|max:5120|mimes:jpg,png,gif,svg,doc,docx,pdf,webp', // puedes agregar mimes:jpg,png,pdf según necesites
            'type_file' => 'required|string',
            ]);

            $directory = $user->id . '/';
            $archivo = $request->file('file');
            $tipo=$request->input('type_file');
            $nombreArchivo = $directory.$tipo."_".uniqid() . '.' . $archivo->getClientOriginalExtension();
            if($request->input('filename')){
                $nombreArchivo = $request->input('filename');
                $nombreArchivo = basename(parse_url($nombreArchivo, PHP_URL_PATH)); // obtiene "firma_auxiliar_123.png"
                $nombreArchivo = $directory.$tipo."_".$nombreArchivo. '.'  . $archivo->getClientOriginalExtension();
            }
            if (Storage::disk('public')->exists($nombreArchivo)) {
                Storage::disk('public')->delete($nombreArchivo);
            }
            $disk = Storage::disk('public');
            $userFiles = $disk->files($directory);

            
            foreach ($userFiles as $file) {
                // Borra todos excepto el archivo que quieres conservar de ese tipo
                if ($file !== $nombreArchivo && str_contains($file, $tipo.'_')) {
                    $disk->delete($file);
                }
            }
            // Guardar en public/files/idUsuario usando el disco "public" configurado a public_path('files')
            $disk->put($nombreArchivo, file_get_contents($archivo));

            // Retornar la URL relativa
            $urlRelativa = '/files/' . $nombreArchivo;

            

            return response()->json([
                'url' => $urlRelativa,
                'nombre' => $nombreArchivo,
                'user_files' => $userFiles
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al subir el archivo: ' . $e->getMessage()], 500);
        }
    }


    public function eliminarArchivo(Request $request)
{
    try {
    $request->validate([
        'filename' => 'required|string',
    ]);

    $filename = $request->input('filename');
    $filename = basename(parse_url($filename, PHP_URL_PATH)); // obtiene "firma_auxiliar_123.png"


    if (Storage::disk('public')->exists($filename)) {
        Storage::disk('public')->delete($filename);
        return response()->json([
            'message' => 'Archivo eliminado correctamente',
        ]);
    } else {
        return response()->json([
            'message' => 'El archivo no existe',
        ], 404);
    }
    } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el archivo: ' . $e->getMessage()], 500);
        }
}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
