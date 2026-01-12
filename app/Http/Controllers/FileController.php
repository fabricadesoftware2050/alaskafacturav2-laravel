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
            'type' => 'nullable|string',
            ]);

            $directory = $user->id . '/';
            $archivo = $request->file('file');
            $tipo=$request->input('type')||'general';
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
            $totalFiles = $disk->files($directory);
            // Enviar correo si se supera el límite de 50 archivos
            if(count($totalFiles) > 50){
               Mail::html(
                "
                <div style='background:#f8fafa; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; margin:0; padding:32px 0; color:#1a2e44;'>

                <table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                    <td align='center'>

                        <table width='580' cellpadding='0' cellspacing='0' style='margin-bottom:20px;'>
                        <tr>
                            <td align='center' style='font-size:22px; font-weight:700; color:#e11d48;'>
                            Alaska Factura
                            </td>
                        </tr>
                        </table>

                        <table width='580' cellpadding='0' cellspacing='0' style='background:#ffffff; border-radius:8px; border:1px solid #e5e7eb;'>
                        <tr>
                            <td style='padding:32px;'>
                            <img src='https://alaskafactura.cloud/alaskaia.png' alt='Alaska Factura' width='100%'> 

                            <h1 style='margin:0 0 12px; font-size:20px; color:#111827;'>
                                ¡Límite de archivos alcanzado!
                            </h1>

                            <p style='margin:0 0 16px; font-size:14px;'>
                                Hola <strong>ALASKA</strong>,
                            </p>

                            <p style='margin:0 0 16px; font-size:14px; line-height:1.5;'>
                                Te informamos que el usuario:" . auth()->user()->name." y correo " . auth()->user()->email." ha alcanzado o superado el <strong>límite de archivos permitidos, tiene ".count($totalFiles)."</strong> en tu plan actual. Para continuar procesando nuevos documentos sin interrupciones, es necesario ampliar tu capacidad.
                            </p>

                            <div style='
                                text-align:center;
                                font-size:18px;
                                font-weight:600;
                                padding:20px;
                                margin:24px 0;
                                background:#fff1f2;
                                border: 1px solid #fecdd3;
                                border-radius:6px;
                                color:#e11d48;
                            '>
                                Capacidad Máxima Superada
                            </div>

                            <p style='margin:0 0 16px; font-size:14px;'>
                                Si deseas aumentar tu límite o conocer nuestros planes empresariales, por favor ponte en contacto con nuestro equipo de soporte.
                            </p>

                            <table width='100%' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td align='center'>
                                        <a href='mailto:info@alaskafactura.cloud' style='background:#2563eb; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;'>Contactar Soporte</a>
                                    </td>
                                </tr>
                            </table>

                            <hr style='border:none; border-top:1px solid #e5e7eb; margin:24px 0;'>

                            <p style='font-size:12px; color:#6b7280; margin:0;'>
                                © " . date('Y') . " Alaska Factura<br>
                                Este es un aviso automático enviado a " . auth()->user()->email . "
                            </p>

                            </td>
                        </tr>
                        </table>

                    </td>
                    </tr>
                </table>

                </div>
                ",
                function ($msg) {
                    $msg->to('info@alaskafactura.cloud')
                        ->from('no-responder@alaskafactura.cloud', 'Alaska Factura')
                        ->subject('Aviso: Límite de archivos superado');
                }
            );
            }
            foreach ($totalFiles as $file) {
                // Borra todos excepto el archivo que quieres conservar de ese tipo
                if ($file !== $nombreArchivo && str_contains($archivo, $tipo.'_')) {
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
                'total_files' => $totalFiles
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
