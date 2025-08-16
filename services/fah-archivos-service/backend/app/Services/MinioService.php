<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MinioService
{
    protected $disk;
    protected $bucket;

    public function __construct()
    {
        $this->disk = Storage::disk('minio');
        $this->bucket = config('filesystems.disks.minio.bucket');
    }

    /**
     * Verificar conexión con MinIO
     */
    public function testConnection()
    {
        try {
            // Crear un archivo de prueba temporal
            $testFileName = 'test-connection-' . time() . '.txt';
            $testContent = 'Test connection from FAH Archivos Service at ' . now();

            // Intentar subir archivo de prueba
            $uploaded = $this->disk->put($testFileName, $testContent);

            if ($uploaded) {
                // Verificar que el archivo existe
                $exists = $this->disk->exists($testFileName);

                // Leer el contenido
                $content = $this->disk->get($testFileName);

                // Eliminar archivo de prueba
                $this->disk->delete($testFileName);

                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con MinIO',
                    'bucket' => $this->bucket,
                    'test_file_uploaded' => $uploaded,
                    'test_file_exists' => $exists,
                    'test_content_match' => ($content === $testContent),
                    'endpoint' => config('filesystems.disks.minio.endpoint'),
                    'timestamp' => now()->toISOString()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo subir archivo de prueba a MinIO'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión con MinIO: ' . $e->getMessage(),
                'error_type' => get_class($e),
                'bucket' => $this->bucket,
                'endpoint' => config('filesystems.disks.minio.endpoint')
            ];
        }
    }

    /**
     * Subir archivo a MinIO
     */
    public function uploadFile(UploadedFile $file, $path = null)
    {
        try {
            $fileName = $path ?? (Str::uuid() . '.' . $file->getClientOriginalExtension());

            $uploaded = $this->disk->put($fileName, file_get_contents($file));

            if ($uploaded) {
                return [
                    'success' => true,
                    'file_name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ];
            }

            return [
                'success' => false,
                'message' => 'No se pudo subir el archivo'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al subir archivo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener URL pública del archivo
     */
    public function getFileUrl($fileName)
    {
        try {
            // Construir URL manualmente para MinIO
            $endpoint = config('filesystems.disks.minio.endpoint');
            $bucket = config('filesystems.disks.minio.bucket');
            return $endpoint . '/' . $bucket . '/' . $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verificar si un archivo existe
     */
    public function fileExists($fileName)
    {
        try {
            return $this->disk->exists($fileName);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Eliminar archivo
     */
    public function deleteFile($fileName)
    {
        try {
            return $this->disk->delete($fileName);
        } catch (\Exception $e) {
            return false;
        }
    }
}
