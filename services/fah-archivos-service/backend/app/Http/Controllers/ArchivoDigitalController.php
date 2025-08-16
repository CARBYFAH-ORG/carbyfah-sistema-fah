<?php

namespace App\Http\Controllers;

use App\Models\ArchivoDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArchivoDigitalController extends Controller
{
    /**
     * Listar todos los archivos digitales
     * GET /api/archivos/archivos-digitales
     */
    public function index(Request $request)
    {
        try {
            $query = ArchivoDigital::with([
                'repositorio',
                'tipoArchivo',
                'categoriaContenido',
                'nivelAcceso'
            ])
                ->activos()
                ->orderBy('created_at', 'desc');

            // Filtros opcionales
            if ($request->has('categoria_id')) {
                $query->where('categoria_contenido_id', $request->categoria_id);
            }

            if ($request->has('nivel_acceso_id')) {
                $query->where('nivel_acceso_id', $request->nivel_acceso_id);
            }

            if ($request->has('repositorio_id')) {
                $query->where('repositorio_id', $request->repositorio_id);
            }

            $archivos = $query->paginate(20);

            return response()->json([
                'success' => true,
                'message' => 'Archivos digitales obtenidos correctamente',
                'data' => $archivos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener archivos digitales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo archivo digital
     * POST /api/archivos/archivos-digitales
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_archivo' => 'required|string|max:255',
                'nombre_original' => 'required|string|max:255',
                'ruta_storage' => 'required|string|max:500',
                'repositorio_id' => 'required|integer|exists:repositorios_storage,id',
                'tipo_archivo_id' => 'required|integer|exists:tipos_archivo,id',
                'categoria_contenido_id' => 'required|integer|exists:categorias_contenido,id',
                'nivel_acceso_id' => 'required|integer|exists:niveles_acceso,id',
                'tamano_bytes' => 'required|integer|min:1',
                'checksum_md5' => 'required|string|size:32',
                'hash_contenido' => 'required|string|size:64',
                'subido_por_id' => 'required|integer',
                'unidad_origen_id' => 'required|integer',
                'descripcion_archivo' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Verificar archivo duplicado por hash
            if (ArchivoDigital::where('hash_contenido', $request->hash_contenido)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un archivo con el mismo contenido (hash duplicado)'
                ], 409);
            }

            $archivo = ArchivoDigital::create([
                'nombre_archivo' => $request->nombre_archivo,
                'nombre_original' => $request->nombre_original,
                'ruta_storage' => $request->ruta_storage,
                'repositorio_id' => $request->repositorio_id,
                'tipo_archivo_id' => $request->tipo_archivo_id,
                'categoria_contenido_id' => $request->categoria_contenido_id,
                'nivel_acceso_id' => $request->nivel_acceso_id,
                'tamano_bytes' => $request->tamano_bytes,
                'checksum_md5' => $request->checksum_md5,
                'hash_contenido' => $request->hash_contenido,
                'subido_por_id' => $request->subido_por_id,
                'fecha_subida' => now(),
                'unidad_origen_id' => $request->unidad_origen_id,
                'descripcion_archivo' => $request->descripcion_archivo,
                'created_by' => 1,
                'updated_by' => 1
            ]);

            $archivo->load(['repositorio', 'tipoArchivo', 'categoriaContenido', 'nivelAcceso']);

            return response()->json([
                'success' => true,
                'message' => 'Archivo digital creado correctamente',
                'data' => $archivo
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear archivo digital',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener archivo específico
     * GET /api/archivos/archivos-digitales/{id}
     */
    public function show($id)
    {
        try {
            $archivo = ArchivoDigital::with([
                'repositorio',
                'tipoArchivo',
                'categoriaContenido',
                'nivelAcceso',
                'versionesArchivo',
                'metadatos',
                'permisosAcceso'
            ])->find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo digital no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Archivo digital obtenido correctamente',
                'data' => $archivo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener archivo digital',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar archivo digital
     * PUT /api/archivos/archivos-digitales/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $archivo = ArchivoDigital::find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo digital no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'categoria_contenido_id' => 'integer|exists:categorias_contenido,id',
                'nivel_acceso_id' => 'integer|exists:niveles_acceso,id',
                'descripcion_archivo' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $archivo->update([
                'categoria_contenido_id' => $request->categoria_contenido_id ?? $archivo->categoria_contenido_id,
                'nivel_acceso_id' => $request->nivel_acceso_id ?? $archivo->nivel_acceso_id,
                'descripcion_archivo' => $request->descripcion_archivo ?? $archivo->descripcion_archivo,
                'is_active' => $request->is_active ?? true,
                'updated_by' => 1,
                'version' => $archivo->version + 1
            ]);

            $archivo->load(['repositorio', 'tipoArchivo', 'categoriaContenido', 'nivelAcceso']);

            return response()->json([
                'success' => true,
                'message' => 'Archivo digital actualizado correctamente',
                'data' => $archivo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar archivo digital',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar archivo digital (soft delete)
     * DELETE /api/archivos/archivos-digitales/{id}
     */
    public function destroy($id)
    {
        try {
            $archivo = ArchivoDigital::find($id);

            if (!$archivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo digital no encontrado'
                ], 404);
            }

            // Verificar si tiene versiones asociadas
            if ($archivo->versionesArchivo()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el archivo porque tiene versiones asociadas'
                ], 409);
            }

            $archivo->update([
                'deleted_by' => 1,
            ]);

            $archivo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo digital eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar archivo digital',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
