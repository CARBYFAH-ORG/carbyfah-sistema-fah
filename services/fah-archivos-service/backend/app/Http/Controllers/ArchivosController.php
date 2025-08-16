<?php

namespace App\Http\Controllers;

use App\Models\ArchivoDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\MinioService;
use Aws\S3\S3Client;

class ArchivosController extends Controller
{
    /**
     * Listar todos los archivos
     * GET /api/archivos
     */
    public function index()
    {
        try {
            $archivos = ArchivoDigital::activos()
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Archivos obtenidos correctamente',
                'data' => $archivos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener archivos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir nuevo archivo
     * POST /api/archivos/upload
     */
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'archivo' => 'required|file|max:102400', // 100MB máximo
                'categoria_contenido_id' => 'nullable|integer',
                'nivel_acceso_id' => 'nullable|integer',
                'descripcion_archivo' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $file = $request->file('archivo');
            $hash = hash_file('sha256', $file->getRealPath());

            // Verificar archivo duplicado
            if (ArchivoDigital::where('hash_contenido', $hash)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo ya existe en el sistema',
                    'error' => 'Archivo duplicado'
                ], 409);
            }

            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

            // Subir a MinIO
            Storage::disk('minio')->put($fileName, file_get_contents($file));

            $archivo = ArchivoDigital::create([
                'nombre_archivo' => $fileName,
                'nombre_original' => $file->getClientOriginalName(),
                'ruta_storage' => $fileName,
                'repositorio_id' => 1,
                'tipo_archivo_id' => 1,
                'categoria_contenido_id' => $request->categoria_contenido_id ?? 1,
                'nivel_acceso_id' => $request->nivel_acceso_id ?? 1,
                'tamano_bytes' => $file->getSize(),
                'checksum_md5' => md5_file($file->getRealPath()),
                'hash_contenido' => $hash,
                'subido_por_id' => 1,
                'unidad_origen_id' => 1,
                'descripcion_archivo' => $request->descripcion_archivo,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'data' => $archivo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener archivo específico
     * GET /api/archivos/{id}
     */
    public function show($id)
    {
        try {
            $archivo = ArchivoDigital::find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Archivo obtenido correctamente',
                'data' => $archivo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar archivo
     * GET /api/archivos/{id}/download
     */
    public function download($id)
    {
        try {
            $archivo = ArchivoDigital::find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            // Verificar que el archivo existe en MinIO
            if (!Storage::disk('minio')->exists($archivo->ruta_storage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado en storage'
                ], 404);
            }

            // Obtener el contenido del archivo
            $contenido = Storage::disk('minio')->get($archivo->ruta_storage);

            // Determinar el tipo MIME
            $extension = pathinfo($archivo->nombre_original, PATHINFO_EXTENSION);
            $mimeType = $this->getMimeType($extension);

            // Crear la respuesta con headers correctos
            return response($contenido, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $archivo->nombre_original . '"')
                ->header('Content-Length', strlen($contenido));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar archivo (soft delete)
     * DELETE /api/archivos/{id}
     */
    public function destroy($id)
    {
        try {
            $archivo = ArchivoDigital::find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            $archivo->update([
                'deleted_by' => 1,
            ]);

            $archivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipo MIME por extensión
     */
    private function getMimeType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip' => 'application/zip',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Probar conexión con MinIO
     * GET /api/archivos/test-minio
     */
    public function testMinio(MinioService $minioService)
    {
        $result = $minioService->testConnection();

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Probar conexión con MinIO - Método simplificado
     * GET /api/archivos/test-minio-simple
     */
    public function testMinioSimple()
    {
        try {
            // Test básico sin AWS SDK
            $config = [
                'endpoint' => env('MINIO_ENDPOINT'),
                'key' => env('MINIO_KEY'),
                'secret' => env('MINIO_SECRET'),
                'bucket' => env('MINIO_BUCKET')
            ];

            // Verificar configuración
            if (!$config['endpoint'] || !$config['key']) {
                throw new \Exception('MinIO configuration missing');
            }

            return response()->json([
                'success' => true,
                'message' => 'MinIO config OK',
                'config' => $config,
                'test_time' => now()->toISOString()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Diagnóstico de configuración MinIO (sin conexión)
     * GET /api/archivos/debug-config
     */
    public function debugConfig()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Configuración MinIO',
                'env_vars' => [
                    'MINIO_KEY' => env('MINIO_KEY'),
                    'MINIO_SECRET' => env('MINIO_SECRET'),
                    'MINIO_ENDPOINT' => env('MINIO_ENDPOINT'),
                    'MINIO_BUCKET' => env('MINIO_BUCKET'),
                    'MINIO_REGION' => env('MINIO_REGION'),
                    'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
                ],
                'config_values' => [
                    'filesystems.default' => config('filesystems.default'),
                    'filesystems.disks.minio.key' => config('filesystems.disks.minio.key'),
                    'filesystems.disks.minio.secret' => config('filesystems.disks.minio.secret'),
                    'filesystems.disks.minio.endpoint' => config('filesystems.disks.minio.endpoint'),
                    'filesystems.disks.minio.bucket' => config('filesystems.disks.minio.bucket'),
                    'filesystems.disks.minio.region' => config('filesystems.disks.minio.region'),
                ],
                'php_extensions' => [
                    'curl' => extension_loaded('curl'),
                    'openssl' => extension_loaded('openssl'),
                    'fileinfo' => extension_loaded('fileinfo'),
                ],
                'docker_env' => [
                    'hostname' => gethostname(),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en diagnóstico: ' . $e->getMessage(),
                'error_class' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de MinIO
     * GET /api/archivos/storage-stats
     */
    public function storageStats(MinioService $minioService)
    {
        try {
            $files = Storage::disk('minio')->allFiles();
            $totalSize = 0;

            foreach ($files as $file) {
                $totalSize += Storage::disk('minio')->size($file);
            }

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_files' => count($files),
                    'total_size_bytes' => $totalSize,
                    'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                    'bucket' => config('filesystems.disks.minio.bucket')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
