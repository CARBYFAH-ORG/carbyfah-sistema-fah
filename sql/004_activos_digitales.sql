-- =====================================================
-- CARBYFAH - SISTEMA ACTIVOS DIGITALES DEFINITIVO
-- ARCHIVO: 004_activos_digitales.sql
-- PREPARADO PARA TODO EL PROYECTO - SCHEMAS FUTUROS 100% CUBIERTOS
-- VERSIÓN ENTERPRISE COMPLETA CON TODAS LAS FUNCIONES AVANZADAS
-- COMPATIBLE CON: catalogos, organizacion, personal
-- =====================================================

-- =====================================================
-- CREAR SCHEMA Y FUNCIONES BASE REQUERIDAS
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS digital_assets;

-- Función para actualizar timestamp automáticamente (si no existe)
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- 1. CATEGORÍAS DE ARCHIVO (ESCALABLE)
-- Propósito: Clasificación técnica por tipo de archivo para organización visual y funcional
-- Uso: Iconografía UI, validación uploads, filtros en galerías, organización automática
-- Ejemplos para TODO EL PROYECTO FAH:
--   - codigo_categoria: 'IMAGEN', nombre_categoria: 'Imágenes', extension_principal: '.jpg', icono_categoria: 'image', color_categoria: '#28a745'
--   - codigo_categoria: 'VIDEO', nombre_categoria: 'Videos', extension_principal: '.mp4', icono_categoria: 'video', color_categoria: '#6f42c1'
--   - codigo_categoria: 'DOCUMENTO', nombre_categoria: 'Documentos', extension_principal: '.pdf', icono_categoria: 'file-text', color_categoria: '#007bff'
--   - codigo_categoria: 'AUDIO', nombre_categoria: 'Audio', extension_principal: '.mp3', icono_categoria: 'music', color_categoria: '#fd7e14'
--   - codigo_categoria: 'ARCHIVO', nombre_categoria: 'Archivos Comprimidos', extension_principal: '.zip', icono_categoria: 'archive', color_categoria: '#6c757d'
--   - codigo_categoria: 'MODELO_3D', nombre_categoria: 'Modelos 3D', extension_principal: '.obj', icono_categoria: 'cube', color_categoria: '#e83e8c'
-- =====================================================
CREATE TABLE digital_assets.categorias_archivo (
    id SERIAL PRIMARY KEY,
    codigo_categoria VARCHAR(20) UNIQUE NOT NULL, -- IMAGEN, VIDEO, AUDIO, DOCUMENTO, ARCHIVO, MODELO_3D
    nombre_categoria VARCHAR(100) NOT NULL, -- Nombre descriptivo para interfaz
    descripcion TEXT, -- Descripción detallada del tipo y sus usos
    extension_principal VARCHAR(10), -- Extensión más común (.jpg, .pdf, .mp4)
    icono_categoria VARCHAR(50), -- Icono FontAwesome (image, video, music, file-text, archive, cube)
    color_categoria VARCHAR(7), -- Color hex para UI (#28a745=verde, #007bff=azul)
    orden_listado INTEGER, -- Orden para mostrar en interfaces
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.categorias_archivo IS 'Clasificación técnica de archivos - preparada para TODOS los tipos del proyecto FAH';
COMMENT ON COLUMN digital_assets.categorias_archivo.codigo_categoria IS 'Códigos: IMAGEN, VIDEO, DOCUMENTO, AUDIO, ARCHIVO, MODELO_3D, etc.';
COMMENT ON COLUMN digital_assets.categorias_archivo.icono_categoria IS 'Iconos FontAwesome: image, video, file-text, music, archive, cube';

-- =====================================================
-- 2. TIPOS DE STORAGE (ESCALABLE)
-- Propósito: Configuración de repositorios de almacenamiento para alta disponibilidad
-- Uso: Gestión distribuida, respaldos automáticos, balanceo de carga
-- Ejemplos para escalabilidad enterprise:
--   - codigo_tipo: 'MINIO', nombre_tipo: 'MinIO Object Storage', soporta_versionado: true, soporta_encriptacion: true, costo_relativo: 2
--   - codigo_tipo: 'AWS_S3', nombre_tipo: 'Amazon S3', soporta_versionado: true, soporta_encriptacion: true, costo_relativo: 4
--   - codigo_tipo: 'LOCAL', nombre_tipo: 'Sistema Local', requiere_configuracion: false, costo_relativo: 1
--   - codigo_tipo: 'FTP', nombre_tipo: 'Servidor FTP', costo_relativo: 1
--   - codigo_tipo: 'GOOGLE_CLOUD', nombre_tipo: 'Google Cloud Storage', soporta_versionado: true, costo_relativo: 3
-- =====================================================
CREATE TABLE digital_assets.tipos_storage (
    id SERIAL PRIMARY KEY,
    codigo_tipo VARCHAR(20) UNIQUE NOT NULL, -- MINIO, AWS_S3, LOCAL, FTP, GOOGLE_CLOUD
    nombre_tipo VARCHAR(100) NOT NULL, -- Nombre descriptivo del tipo
    descripcion TEXT, -- Descripción técnica detallada
    requiere_configuracion BOOLEAN DEFAULT true, -- Si requiere setup (false para LOCAL)
    soporta_versionado BOOLEAN DEFAULT false, -- Versionado automático (true para cloud)
    soporta_encriptacion BOOLEAN DEFAULT false, -- Encriptación nativa (true para cloud)
    costo_relativo INTEGER DEFAULT 1, -- Costo: 1=económico, 5=costoso
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.tipos_storage IS 'Tipos de almacenamiento escalables para alta disponibilidad enterprise';
COMMENT ON COLUMN digital_assets.tipos_storage.soporta_versionado IS 'Si soporta control de versiones automático';
COMMENT ON COLUMN digital_assets.tipos_storage.costo_relativo IS 'Costo relativo: 1=muy económico, 5=muy costoso';

-- =====================================================
-- 3. ESTADOS CONEXIÓN (ESCALABLE)
-- Propósito: Monitoreo en tiempo real de estado de repositorios
-- Uso: Alertas automáticas, balanceo de carga, troubleshooting
-- Ejemplos para operación 24/7:
--   - codigo_estado: 'ACTIVO', nombre_estado: 'Activo', permite_operaciones: true, color_estado: '#28a745', icono_estado: 'check-circle'
--   - codigo_estado: 'INACTIVO', nombre_estado: 'Inactivo', permite_operaciones: false, requiere_intervencion: true, color_estado: '#6c757d'
--   - codigo_estado: 'ERROR', nombre_estado: 'Error', permite_operaciones: false, requiere_intervencion: true, color_estado: '#dc3545', icono_estado: 'times-circle'
--   - codigo_estado: 'MANTENIMIENTO', nombre_estado: 'Mantenimiento', permite_operaciones: false, color_estado: '#ffc107', icono_estado: 'wrench'
-- =====================================================
CREATE TABLE digital_assets.estados_conexion (
    id SERIAL PRIMARY KEY,
    codigo_estado VARCHAR(20) UNIQUE NOT NULL, -- ACTIVO, INACTIVO, ERROR, MANTENIMIENTO
    nombre_estado VARCHAR(100) NOT NULL, -- Nombre descriptivo
    descripcion TEXT, -- Descripción del estado y acciones
    permite_operaciones BOOLEAN DEFAULT true, -- Si permite upload/download
    requiere_intervencion BOOLEAN DEFAULT false, -- Si necesita intervención técnica
    color_estado VARCHAR(7), -- Color hex para dashboards (#28a745, #dc3545)
    icono_estado VARCHAR(50), -- Icono FontAwesome (check-circle, times-circle, wrench)
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.estados_conexion IS 'Estados de conexión para monitoreo 24/7 de repositorios';
COMMENT ON COLUMN digital_assets.estados_conexion.permite_operaciones IS 'Si permite operaciones de archivos en este estado';

-- =====================================================
-- 4. TIPOS DE ARCHIVO (MEJORADO CON FK)
-- Propósito: Definición específica de archivos soportados con validaciones automáticas
-- Uso: Validación estricta, configuración límites, procesamiento automático
-- Ejemplos para TODOS LOS TIPOS del proyecto:
--   IMÁGENES:
--   - categoria_archivo_id: 1, extension: '.jpg', mime_type: 'image/jpeg', tamano_maximo_mb: 10, requiere_antivirus: false
--   - categoria_archivo_id: 1, extension: '.png', mime_type: 'image/png', tamano_maximo_mb: 15, requiere_antivirus: false
--   DOCUMENTOS:
--   - categoria_archivo_id: 3, extension: '.pdf', mime_type: 'application/pdf', tamano_maximo_mb: 100, requiere_antivirus: true
--   - categoria_archivo_id: 3, extension: '.docx', mime_type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', tamano_maximo_mb: 50
--   VIDEOS:
--   - categoria_archivo_id: 2, extension: '.mp4', mime_type: 'video/mp4', tamano_maximo_mb: 2000, compresion_automatica: true
--   MODELOS 3D:
--   - categoria_archivo_id: 6, extension: '.obj', mime_type: 'model/obj', tamano_maximo_mb: 500, requiere_antivirus: true
-- =====================================================
CREATE TABLE digital_assets.tipos_archivo (
    id SERIAL PRIMARY KEY,
    categoria_archivo_id INTEGER NOT NULL, -- FK a categorias_archivo
    extension VARCHAR(10) NOT NULL, -- .jpg, .png, .pdf, .mp4, .obj
    mime_type VARCHAR(100) NOT NULL, -- MIME type oficial
    tamano_maximo_mb INTEGER NOT NULL DEFAULT 10, -- Límite en MB
    descripcion TEXT, -- Descripción específica del tipo
    requiere_antivirus BOOLEAN DEFAULT true, -- Escaneo obligatorio
    compresion_automatica BOOLEAN DEFAULT false, -- Compresión automática para videos
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    
    UNIQUE(extension, mime_type)
);

COMMENT ON TABLE digital_assets.tipos_archivo IS 'Tipos específicos de archivo soportados - preparado para TODOS los formatos del proyecto';
COMMENT ON COLUMN digital_assets.tipos_archivo.tamano_maximo_mb IS 'Límite de tamaño en megabytes para uploads';

-- =====================================================
-- 5. CATEGORÍAS DE CONTENIDO
-- Propósito: Clasificación por contenido y propósito específico militar FAH
-- Uso: Organización funcional, aplicación de políticas automáticas, búsquedas especializadas
-- Ejemplos para TODOS LOS MÓDULOS futuros:
--   PERSONAL:
--   - codigo_categoria: 'FOTO_PERFIL', nombre_categoria: 'Fotografías de Perfil', requiere_clasificacion: false
--   - codigo_categoria: 'DOCUMENTO_PERSONAL', nombre_categoria: 'Documentos Personales', requiere_clasificacion: true
--   CATÁLOGOS:
--   - codigo_categoria: 'INSIGNIA_GRADO', nombre_categoria: 'Insignias de Grado', permite_descarga_publica: true
--   - codigo_categoria: 'LOGO_UNIDAD', nombre_categoria: 'Logos de Unidades', permite_descarga_publica: true
--   EQUIPOS (FUTURO):
--   - codigo_categoria: 'FOTO_AERONAVE', nombre_categoria: 'Fotografías de Aeronaves', requiere_clasificacion: false
--   - codigo_categoria: 'MANUAL_AERONAVE', nombre_categoria: 'Manuales de Aeronaves', requiere_clasificacion: true
--   - codigo_categoria: 'CERTIFICADO_AERONAVEGABILIDAD', nombre_categoria: 'Certificados de Aeronavegabilidad', requiere_clasificacion: true
--   OPERACIONES (FUTURO):
--   - codigo_categoria: 'PLAN_VUELO', nombre_categoria: 'Planes de Vuelo', requiere_clasificacion: true
--   - codigo_categoria: 'REPORTE_MISION', nombre_categoria: 'Reportes de Misión', requiere_clasificacion: true
--   - codigo_categoria: 'VIDEO_ENTRENAMIENTO', nombre_categoria: 'Videos de Entrenamiento', requiere_clasificacion: false
--   INTELIGENCIA (FUTURO):
--   - codigo_categoria: 'DOCUMENTO_INTELIGENCIA', nombre_categoria: 'Documentos de Inteligencia', requiere_clasificacion: true
--   - codigo_categoria: 'FOTO_RECONOCIMIENTO', nombre_categoria: 'Fotografías de Reconocimiento', requiere_clasificacion: true
-- =====================================================
CREATE TABLE digital_assets.categorias_contenido (
    id SERIAL PRIMARY KEY,
    codigo_categoria VARCHAR(50) UNIQUE NOT NULL, -- Código único de contenido
    nombre_categoria VARCHAR(100) NOT NULL, -- Nombre descriptivo
    descripcion TEXT, -- Descripción del contenido y uso
    requiere_clasificacion BOOLEAN DEFAULT false, -- Si requiere nivel de seguridad obligatorio
    permite_descarga_publica BOOLEAN DEFAULT false, -- Si puede descargarse sin autenticación
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.categorias_contenido IS 'Categorías de contenido - preparadas para TODOS los módulos futuros del proyecto';
COMMENT ON COLUMN digital_assets.categorias_contenido.codigo_categoria IS 'Códigos: FOTO_PERFIL, INSIGNIA_GRADO, PLAN_VUELO, MANUAL_AERONAVE, etc.';

-- =====================================================
-- 6. REPOSITORIOS DE STORAGE (MEJORADO CON FKs)
-- Propósito: Configuración específica de repositorios físicos para almacenamiento distribuido
-- Uso: Gestión múltiple de ubicaciones, balanceo automático, respaldos
-- Ejemplos para arquitectura enterprise:
--   - nombre_repositorio: 'MinIO Principal FAH', tipo_storage_id: 1 (MINIO), estado_conexion_id: 1 (ACTIVO)
--     configuracion_json: '{"endpoint": "minio.fah.mil.hn:9000", "access_key": "fah_admin", "secret_key": "...", "region": "us-east-1"}'
--     capacidad_gb: 2000, bucket_default: 'fah-principal', url_base: 'https://files.fah.mil.hn'
--   - nombre_repositorio: 'Respaldo Local Seguro', tipo_storage_id: 3 (LOCAL), estado_conexion_id: 1 (ACTIVO)
--     configuracion_json: '{"ruta_base": "/var/fah/backup_secure"}', capacidad_gb: 1000
--   - nombre_repositorio: 'AWS S3 Archivo Histórico', tipo_storage_id: 2 (AWS_S3), estado_conexion_id: 2 (MANTENIMIENTO)
--     configuracion_json: '{"region": "us-east-1", "bucket": "fah-historical-archive"}'
-- =====================================================
CREATE TABLE digital_assets.repositorios_storage (
    id SERIAL PRIMARY KEY,
    nombre_repositorio VARCHAR(100) UNIQUE NOT NULL, -- Nombre descriptivo único
    tipo_storage_id INTEGER NOT NULL, -- FK a tipos_storage
    estado_conexion_id INTEGER NOT NULL, -- FK a estados_conexion
    configuracion_json JSONB, -- Configuración completa en JSON
    capacidad_gb INTEGER, -- Capacidad total en gigabytes
    usado_gb DECIMAL(10,2) DEFAULT 0, -- Espacio usado (actualizado automáticamente)
    url_base VARCHAR(500), -- URL base para acceso directo
    bucket_default VARCHAR(100), -- Bucket/contenedor por defecto
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.repositorios_storage IS 'Repositorios de almacenamiento configurados para arquitectura distribuida';
COMMENT ON COLUMN digital_assets.repositorios_storage.configuracion_json IS 'Configuración completa: credenciales, endpoints, buckets, rutas';
COMMENT ON COLUMN digital_assets.repositorios_storage.usado_gb IS 'Espacio usado en GB - actualizado automáticamente por triggers';

-- =====================================================
-- 7. NIVELES DE ACCESO
-- Propósito: Clasificación de seguridad militar para control de acceso
-- Uso: Políticas automáticas de seguridad, auditoría, control de acceso
-- Ejemplos para clasificación militar completa:
--   - codigo_nivel: 'PUBLICO', nombre_nivel: 'Público', orden_seguridad: 1, requiere_autenticacion: false, log_accesos: false
--   - codigo_nivel: 'INTERNO', nombre_nivel: 'Uso Interno FAH', orden_seguridad: 2, requiere_autenticacion: true, log_accesos: false
--   - codigo_nivel: 'RESTRINGIDO', nombre_nivel: 'Restringido', orden_seguridad: 3, requiere_autenticacion: true, requiere_autorizacion: true, log_accesos: true
--   - codigo_nivel: 'CONFIDENCIAL', nombre_nivel: 'Confidencial', orden_seguridad: 4, requiere_autenticacion: true, requiere_autorizacion: true, log_accesos: true
--   - codigo_nivel: 'SECRETO', nombre_nivel: 'Secreto', orden_seguridad: 5, requiere_autenticacion: true, requiere_autorizacion: true, log_accesos: true
-- =====================================================
CREATE TABLE digital_assets.niveles_acceso (
    id SERIAL PRIMARY KEY,
    codigo_nivel VARCHAR(20) UNIQUE NOT NULL, -- PUBLICO, INTERNO, RESTRINGIDO, CONFIDENCIAL, SECRETO
    nombre_nivel VARCHAR(100) NOT NULL, -- Nombre descriptivo
    descripcion TEXT, -- Descripción del nivel y restricciones
    orden_seguridad INTEGER NOT NULL, -- 1=menor seguridad, 5=mayor seguridad
    requiere_autenticacion BOOLEAN DEFAULT false, -- Login obligatorio
    requiere_autorizacion BOOLEAN DEFAULT false, -- Autorización del comando
    log_accesos BOOLEAN DEFAULT false, -- Registro de accesos para auditoría
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.niveles_acceso IS 'Niveles de seguridad militar para clasificación completa de archivos';
COMMENT ON COLUMN digital_assets.niveles_acceso.orden_seguridad IS 'Orden numérico: 1=público, 5=máximo secreto';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 8. TIPOS DE PERMISO (ESCALABLE)
-- Propósito: Tipos de operaciones granulares sobre archivos para control total
-- Uso: Matriz de permisos, auditoría de operaciones, control de acceso
-- Ejemplos para control completo:
--   - codigo_permiso: 'READ', nombre_permiso: 'Solo Lectura', nivel_criticidad: 1, afecta_auditoria: false
--   - codigo_permiso: 'DOWNLOAD', nombre_permiso: 'Descarga', nivel_criticidad: 2, afecta_auditoria: true
--   - codigo_permiso: 'WRITE', nombre_permiso: 'Escritura/Modificación', nivel_criticidad: 3, requiere_autorizacion: true
--   - codigo_permiso: 'DELETE', nombre_permiso: 'Eliminación', nivel_criticidad: 5, requiere_autorizacion: true
--   - codigo_permiso: 'SHARE', nombre_permiso: 'Compartir', nivel_criticidad: 3, requiere_autorizacion: true
--   - codigo_permiso: 'ADMIN', nombre_permiso: 'Administración Completa', nivel_criticidad: 5, requiere_autorizacion: true
-- =====================================================
CREATE TABLE digital_assets.tipos_permiso (
    id SERIAL PRIMARY KEY,
    codigo_permiso VARCHAR(20) UNIQUE NOT NULL, -- READ, WRITE, DELETE, SHARE, ADMIN
    nombre_permiso VARCHAR(100) NOT NULL, -- Nombre descriptivo
    descripcion TEXT, -- Descripción detallada del permiso
    nivel_criticidad INTEGER DEFAULT 1, -- 1=básico, 5=crítico
    requiere_autorizacion BOOLEAN DEFAULT false, -- Si requiere autorización del comando
    afecta_auditoria BOOLEAN DEFAULT true, -- Si se registra para auditoría
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.tipos_permiso IS 'Tipos de permisos granulares para control total de operaciones';
COMMENT ON COLUMN digital_assets.tipos_permiso.nivel_criticidad IS 'Nivel de criticidad: 1=bajo riesgo, 5=alto riesgo';

-- =====================================================
-- 9. MOTIVOS DE CAMBIO (ESCALABLE)
-- Propósito: Clasificación de motivos para auditoría completa de modificaciones
-- Uso: Trazabilidad, justificación de cambios, notificaciones automáticas
-- Ejemplos para auditoría completa:
--   - codigo_motivo: 'ACTUALIZACION', nombre_motivo: 'Actualización de Contenido', orden_criticidad: 1
--   - codigo_motivo: 'CORRECCION_DISEÑO', nombre_motivo: 'Corrección de Diseño', requiere_justificacion: true, orden_criticidad: 2
--   - codigo_motivo: 'SOLICITUD_COMANDO', nombre_motivo: 'Solicitud Comando Superior', requiere_justificacion: true, genera_notificacion: true, orden_criticidad: 4
--   - codigo_motivo: 'RECLASIFICACION', nombre_motivo: 'Cambio Clasificación Seguridad', requiere_justificacion: true, genera_notificacion: true, orden_criticidad: 5
--   - codigo_motivo: 'MANTENIMIENTO', nombre_motivo: 'Mantenimiento Técnico', requiere_justificacion: true, orden_criticidad: 3
-- =====================================================
CREATE TABLE digital_assets.motivos_cambio (
    id SERIAL PRIMARY KEY,
    codigo_motivo VARCHAR(30) UNIQUE NOT NULL, -- ACTUALIZACION, CORRECCION_DISEÑO, etc.
    nombre_motivo VARCHAR(100) NOT NULL, -- Nombre descriptivo
    descripcion TEXT, -- Descripción del motivo
    requiere_justificacion BOOLEAN DEFAULT false, -- Justificación obligatoria
    genera_notificacion BOOLEAN DEFAULT false, -- Notificación al comando
    orden_criticidad INTEGER DEFAULT 1, -- 1=normal, 5=crítico
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.motivos_cambio IS 'Motivos de cambio para auditoría completa de modificaciones';
COMMENT ON COLUMN digital_assets.motivos_cambio.genera_notificacion IS 'Si genera notificación automática al comando superior';

-- =====================================================
-- 10. ARCHIVOS DIGITALES (Tabla principal - ESCALABLE)
-- Propósito: REGISTRO PRINCIPAL de cada archivo digital en el sistema FAH completo
-- Uso: Control total de archivos, metadatos, ubicación, seguridad, auditoría
-- Ejemplos para TODOS LOS MÓDULOS del proyecto:
--   CATÁLOGOS:
--   - nombre_archivo: 'insignia_aviacion_001.png', categoria_contenido_id: 5 (INSIGNIA_GRADO), nivel_acceso_id: 1 (PUBLICO)
--   - nombre_archivo: 'logo_fah_oficial_2024.png', categoria_contenido_id: 3 (LOGO_UNIDAD), nivel_acceso_id: 1 (PUBLICO)
--   PERSONAL:
--   - nombre_archivo: 'perfil_juan_perez_001.jpg', categoria_contenido_id: 1 (FOTO_PERFIL), nivel_acceso_id: 2 (INTERNO)
--   EQUIPOS (FUTURO):
--   - nombre_archivo: 'aeronave_f5e_fah4001_2024.jpg', categoria_contenido_id: 10 (FOTO_AERONAVE), nivel_acceso_id: 2 (INTERNO)
--   - nombre_archivo: 'manual_f5e_tiger_ii_v3_2.pdf', categoria_contenido_id: 11 (MANUAL_AERONAVE), nivel_acceso_id: 4 (CONFIDENCIAL)
--   OPERACIONES (FUTURO):
--   - nombre_archivo: 'plan_vuelo_001_2024_03_15.pdf', categoria_contenido_id: 15 (PLAN_VUELO), nivel_acceso_id: 3 (RESTRINGIDO)
--   - nombre_archivo: 'video_entrenamiento_simulador_f5e.mp4', categoria_contenido_id: 17 (VIDEO_ENTRENAMIENTO), nivel_acceso_id: 2 (INTERNO)
-- =====================================================
CREATE TABLE digital_assets.archivos_digitales (
    id SERIAL PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL, -- Nombre único generado por sistema
    nombre_original VARCHAR(255) NOT NULL, -- Nombre original subido por usuario
    ruta_storage VARCHAR(500) NOT NULL, -- Ruta completa en repositorio
    repositorio_id INTEGER NOT NULL, -- FK a repositorios_storage
    tipo_archivo_id INTEGER NOT NULL, -- FK a tipos_archivo
    categoria_contenido_id INTEGER NOT NULL, -- FK a categorias_contenido
    nivel_acceso_id INTEGER NOT NULL, -- FK a niveles_acceso
    tamano_bytes BIGINT NOT NULL, -- Tamaño exacto en bytes
    checksum_md5 VARCHAR(32) NOT NULL, -- MD5 para verificación
    hash_contenido VARCHAR(64) NOT NULL, -- SHA-256 para evitar duplicados
    subido_por_id INTEGER NOT NULL, -- FK a personal.perfiles_militares
    fecha_subida TIMESTAMPTZ DEFAULT NOW() NOT NULL, -- Timestamp de subida
    unidad_origen_id INTEGER NOT NULL, -- FK a organizacion.estructura_militar
    descripcion_archivo TEXT, -- Descripción detallada
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    
    -- Evitar duplicados por hash
    UNIQUE(hash_contenido)
);

COMMENT ON TABLE digital_assets.archivos_digitales IS 'TABLA PRINCIPAL - Registro de cada archivo del sistema FAH completo';
COMMENT ON COLUMN digital_assets.archivos_digitales.hash_contenido IS 'SHA-256 para evitar duplicados y verificar integridad';
COMMENT ON COLUMN digital_assets.archivos_digitales.subido_por_id IS 'Perfil militar de quien subió el archivo';

-- =====================================================
-- 11. VERSIONES DE ARCHIVO (ESCALABLE)
-- Propósito: Control completo de versiones y historial de modificaciones
-- Uso: Auditoría de cambios, rollback, historial completo, trazabilidad
-- Ejemplos para control total de versiones:
--   VERSION INICIAL:
--   - archivo_digital_id: 15, numero_version: 1, modificado_por_id: 8, motivo_cambio_id: 1 (ACTUALIZACION)
--     comentarios_version: 'Versión inicial del manual de operaciones F-5E'
--   ACTUALIZACIÓN TÉCNICA:
--   - archivo_digital_id: 15, numero_version: 2, modificado_por_id: 12, motivo_cambio_id: 2 (CORRECCION_DISEÑO)
--     comentarios_version: 'Corrección de procedimientos de emergencia según nueva normativa'
--   CAMBIO POR COMANDO:
--   - archivo_digital_id: 15, numero_version: 3, modificado_por_id: 8, motivo_cambio_id: 3 (SOLICITUD_COMANDO)
--     comentarios_version: 'Actualización solicitada por Comando General para homologación internacional'
-- =====================================================
CREATE TABLE digital_assets.versiones_archivo (
    id SERIAL PRIMARY KEY,
    archivo_digital_id INTEGER NOT NULL, -- FK a archivos_digitales
    numero_version INTEGER NOT NULL, -- Número secuencial (1, 2, 3...)
    ruta_storage_version VARCHAR(500) NOT NULL, -- Ruta específica de esta versión
    tamano_bytes BIGINT NOT NULL, -- Tamaño de esta versión
    checksum_md5 VARCHAR(32) NOT NULL, -- MD5 específico de esta versión
    modificado_por_id INTEGER NOT NULL, -- FK a personal.perfiles_militares
    fecha_modificacion TIMESTAMPTZ DEFAULT NOW() NOT NULL, -- Timestamp de modificación
    comentarios_version TEXT, -- Comentarios detallados sobre cambios
    motivo_cambio_id INTEGER NOT NULL, -- FK a motivos_cambio
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    
    -- Una versión por archivo
    UNIQUE(archivo_digital_id, numero_version)
);

COMMENT ON TABLE digital_assets.versiones_archivo IS 'Control completo de versiones con historial detallado';
COMMENT ON COLUMN digital_assets.versiones_archivo.numero_version IS 'Número secuencial: 1=original, 2=primera modificación';

-- =====================================================
-- 12. PERMISOS DE ACCESO (ESCALABLE - SEGURIDAD CRÍTICA)
-- Propósito: Control granular de permisos por archivo individual
-- Uso: Seguridad militar crítica, control de acceso por persona Y por rol
-- Ejemplos para seguridad completa:
--   PERMISO INDIVIDUAL:
--   - archivo_digital_id: 25, persona_id: 15, tipo_permiso_id: 1 (read), concedido_por_id: 8
--     motivo_permiso: 'Consulta para elaboración de reporte técnico'
--   PERMISO POR ROL:
--   - archivo_digital_id: 30, rol_id: 5 (JEFE_MANTENIMIENTO), tipo_permiso_id: 3 (WRITE), concedido_por_id: 2
--     motivo_permiso: 'Actualización de manuales técnicos de mantenimiento'
--   PERMISO TEMPORAL:
--   - archivo_digital_id: 45, persona_id: 22, tipo_permiso_id: 2 (DOWNLOAD), fecha_expiracion: '2024-12-31'
--     motivo_permiso: 'Comisión temporal en unidad externa'
-- =====================================================
CREATE TABLE digital_assets.permisos_acceso (
    id SERIAL PRIMARY KEY,
    archivo_digital_id INTEGER NOT NULL, -- FK a archivos_digitales
    persona_id INTEGER, -- FK a personal.perfiles_militares (permiso individual)
    rol_id INTEGER, -- FK a organizacion.roles_funcionales (permiso por rol)
    tipo_permiso_id INTEGER NOT NULL, -- FK a tipos_permiso
    fecha_concedido TIMESTAMPTZ DEFAULT NOW() NOT NULL, -- Cuándo se otorgó
    concedido_por_id INTEGER NOT NULL, -- FK a personal.perfiles_militares (quien otorgó)
    fecha_expiracion TIMESTAMPTZ, -- Cuándo expira (NULL = permanente)
    motivo_permiso TEXT, -- Justificación del permiso
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    
    -- Constraint: debe tener persona_id O rol_id (no ambos)
    CHECK ((persona_id IS NOT NULL AND rol_id IS NULL) OR (persona_id IS NULL AND rol_id IS NOT NULL))
);

COMMENT ON TABLE digital_assets.permisos_acceso IS 'Control granular de permisos - seguridad militar crítica';
COMMENT ON COLUMN digital_assets.permisos_acceso.persona_id IS 'Permiso individual (NULL si es por rol)';
COMMENT ON COLUMN digital_assets.permisos_acceso.rol_id IS 'Permiso por rol (NULL si es individual)';

-- =====================================================
-- 13. REGISTRO DE ACCESOS (Auditoría completa)
-- Propósito: Auditoría completa de todos los accesos para seguridad militar
-- Uso: Investigación de incidentes, cumplimiento, análisis de uso
-- Ejemplos para auditoría militar:
--   DESCARGA EXITOSA:
--   - archivo_digital_id: 25, usuario_id: 15, tipo_acceso: 'DOWNLOAD', exitoso: true
--     direccion_ip: '192.168.1.50', tamano_descargado: 2345678, tiempo_acceso_segundos: 45
--   ACCESO FALLIDO:
--   - archivo_digital_id: 35, usuario_id: 18, tipo_acceso: 'DOWNLOAD', exitoso: false
--     motivo_fallo: 'Permisos insuficientes para archivo clasificado CONFIDENCIAL'
--   MODIFICACIÓN:
--   - archivo_digital_id: 40, usuario_id: 12, tipo_acceso: 'MODIFICATION', exitoso: true
--     tiempo_acceso_segundos: 1200
-- =====================================================
CREATE TABLE digital_assets.registro_accesos (
    id SERIAL PRIMARY KEY,
    archivo_digital_id INTEGER NOT NULL, -- FK a archivos_digitales
    usuario_id INTEGER NOT NULL, -- FK a personal.perfiles_militares
    tipo_acceso VARCHAR(20) NOT NULL, -- DOWNLOAD, VIEW, MODIFICATION, DELETE
    fecha_acceso TIMESTAMPTZ DEFAULT NOW() NOT NULL, -- Timestamp del acceso
    direccion_ip VARCHAR(15), -- IP desde donde se accedió
    user_agent TEXT, -- Información del navegador/aplicación
    exitoso BOOLEAN DEFAULT true, -- Si el acceso fue exitoso
    motivo_fallo VARCHAR(200), -- Motivo del fallo si no fue exitoso
    tamano_descargado BIGINT, -- Bytes descargados (para descargas)
    tiempo_acceso_segundos INTEGER, -- Duración del acceso
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL
);

COMMENT ON TABLE digital_assets.registro_accesos IS 'Auditoría completa de accesos para seguridad militar';
COMMENT ON COLUMN digital_assets.registro_accesos.tipo_acceso IS 'Tipos: DOWNLOAD, VIEW, MODIFICATION, DELETE, SHARE';

-- =====================================================
-- 14. METADATOS ARCHIVO (Extensible)
-- Propósito: Metadatos extensibles para enriquecer información específica
-- Uso: Búsquedas avanzadas, clasificación adicional, información técnica
-- Ejemplos para TODOS LOS TIPOS de archivos:
--   METADATOS AERONAVE:
--   - archivo_digital_id: 25, clave_metadato: 'matricula_aeronave', valor_metadato: 'FAH-4001'
--   - archivo_digital_id: 25, clave_metadato: 'modelo_aeronave', valor_metadato: 'F-5E Tiger II'
--   - archivo_digital_id: 25, clave_metadato: 'horas_vuelo', valor_metadato: '2547.5', tipo_dato: 'NUMBER'
--   METADATOS DOCUMENTO:
--   - archivo_digital_id: 30, clave_metadato: 'numero_documento', valor_metadato: 'FAH-DOC-2024-001'
--   - archivo_digital_id: 30, clave_metadato: 'clasificacion_original', valor_metadato: 'CONFIDENCIAL'
--   - archivo_digital_id: 30, clave_metadato: 'fecha_vencimiento', valor_metadato: '2025-12-31', tipo_dato: 'DATE'
--   METADATOS PERSONAL:
--   - archivo_digital_id: 35, clave_metadato: 'numero_empleado', valor_metadato: 'FAH-2024-0156'
--   - archivo_digital_id: 35, clave_metadato: 'categoria_personal', valor_metadato: 'OFICIAL'
-- =====================================================
CREATE TABLE digital_assets.metadatos_archivo (
    id SERIAL PRIMARY KEY,
    archivo_digital_id INTEGER NOT NULL, -- FK a archivos_digitales
    clave_metadato VARCHAR(100) NOT NULL, -- Nombre del metadato
    valor_metadato TEXT, -- Valor en formato texto
    tipo_dato VARCHAR(20) DEFAULT 'TEXT', -- TEXT, NUMBER, DATE, BOOLEAN
    es_indexable BOOLEAN DEFAULT true, -- Si aparece en búsquedas
    es_modificable BOOLEAN DEFAULT true, -- Si puede modificarse
    orden_display INTEGER, -- Orden para mostrar en UI
    
    -- Campos de auditoría estándar
    created_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    created_by INTEGER NOT NULL DEFAULT 1,
    updated_at TIMESTAMPTZ DEFAULT NOW() NOT NULL,
    updated_by INTEGER NOT NULL DEFAULT 1,
    deleted_at TIMESTAMPTZ NULL,
    deleted_by INTEGER NULL,
    is_active BOOLEAN DEFAULT true NOT NULL,
    version INTEGER DEFAULT 1 NOT NULL,
    
    UNIQUE(archivo_digital_id, clave_metadato)
);

COMMENT ON TABLE digital_assets.metadatos_archivo IS 'Metadatos extensibles para enriquecer información de archivos';
COMMENT ON COLUMN digital_assets.metadatos_archivo.clave_metadato IS 'Nombres: matricula_aeronave, numero_documento, categoria_personal, etc.';

-- =====================================================
-- FOREIGN KEYS - TODAS LAS RELACIONES
-- =====================================================

-- Tipos de Archivo → Categorías de Archivo
ALTER TABLE digital_assets.tipos_archivo
    ADD CONSTRAINT tipos_archivo_categoria_id_fkey FOREIGN KEY (categoria_archivo_id)
    REFERENCES digital_assets.categorias_archivo (id);

-- Repositorios Storage → Tipos Storage y Estados Conexión
ALTER TABLE digital_assets.repositorios_storage
    ADD CONSTRAINT repositorios_tipo_storage_id_fkey FOREIGN KEY (tipo_storage_id)
    REFERENCES digital_assets.tipos_storage (id);

ALTER TABLE digital_assets.repositorios_storage
    ADD CONSTRAINT repositorios_estado_conexion_id_fkey FOREIGN KEY (estado_conexion_id)
    REFERENCES digital_assets.estados_conexion (id);

-- Archivos Digitales → TODAS las referencias
ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_repositorio_id_fkey FOREIGN KEY (repositorio_id)
    REFERENCES digital_assets.repositorios_storage (id);

ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_tipo_archivo_id_fkey FOREIGN KEY (tipo_archivo_id)
    REFERENCES digital_assets.tipos_archivo (id);

ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_categoria_contenido_id_fkey FOREIGN KEY (categoria_contenido_id)
    REFERENCES digital_assets.categorias_contenido (id);

ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_nivel_acceso_id_fkey FOREIGN KEY (nivel_acceso_id)
    REFERENCES digital_assets.niveles_acceso (id);

ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_subido_por_id_fkey FOREIGN KEY (subido_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE digital_assets.archivos_digitales
    ADD CONSTRAINT archivos_unidad_origen_id_fkey FOREIGN KEY (unidad_origen_id)
    REFERENCES organizacion.estructura_militar (id);

-- Versiones de Archivo → Referencias
ALTER TABLE digital_assets.versiones_archivo
    ADD CONSTRAINT versiones_archivo_digital_id_fkey FOREIGN KEY (archivo_digital_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE digital_assets.versiones_archivo
    ADD CONSTRAINT versiones_modificado_por_id_fkey FOREIGN KEY (modificado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE digital_assets.versiones_archivo
    ADD CONSTRAINT versiones_motivo_cambio_id_fkey FOREIGN KEY (motivo_cambio_id)
    REFERENCES digital_assets.motivos_cambio (id);

-- Permisos de Acceso → Referencias
ALTER TABLE digital_assets.permisos_acceso
    ADD CONSTRAINT permisos_archivo_digital_id_fkey FOREIGN KEY (archivo_digital_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE digital_assets.permisos_acceso
    ADD CONSTRAINT permisos_persona_id_fkey FOREIGN KEY (persona_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE digital_assets.permisos_acceso
    ADD CONSTRAINT permisos_rol_id_fkey FOREIGN KEY (rol_id)
    REFERENCES organizacion.roles_funcionales (id);

ALTER TABLE digital_assets.permisos_acceso
    ADD CONSTRAINT permisos_tipo_permiso_id_fkey FOREIGN KEY (tipo_permiso_id)
    REFERENCES digital_assets.tipos_permiso (id);

ALTER TABLE digital_assets.permisos_acceso
    ADD CONSTRAINT permisos_concedido_por_id_fkey FOREIGN KEY (concedido_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Registro de Accesos → Referencias
ALTER TABLE digital_assets.registro_accesos
    ADD CONSTRAINT registro_archivo_digital_id_fkey FOREIGN KEY (archivo_digital_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE digital_assets.registro_accesos
    ADD CONSTRAINT registro_usuario_id_fkey FOREIGN KEY (usuario_id)
    REFERENCES personal.perfiles_militares (id);

-- Metadatos de Archivo
ALTER TABLE digital_assets.metadatos_archivo
    ADD CONSTRAINT metadatos_archivo_digital_id_fkey FOREIGN KEY (archivo_digital_id)
    REFERENCES digital_assets.archivos_digitales (id);

-- =====================================================
-- TRIGGERS PARA AUDITORÍA AUTOMÁTICA
-- =====================================================

CREATE TRIGGER trg_categorias_archivo_updated_at 
    BEFORE UPDATE ON digital_assets.categorias_archivo 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_tipos_storage_updated_at 
    BEFORE UPDATE ON digital_assets.tipos_storage 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_estados_conexion_updated_at 
    BEFORE UPDATE ON digital_assets.estados_conexion 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_tipos_archivo_updated_at 
    BEFORE UPDATE ON digital_assets.tipos_archivo 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_categorias_contenido_updated_at 
    BEFORE UPDATE ON digital_assets.categorias_contenido 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_repositorios_storage_updated_at 
    BEFORE UPDATE ON digital_assets.repositorios_storage 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_niveles_acceso_updated_at 
    BEFORE UPDATE ON digital_assets.niveles_acceso 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_tipos_permiso_updated_at 
    BEFORE UPDATE ON digital_assets.tipos_permiso 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_motivos_cambio_updated_at 
    BEFORE UPDATE ON digital_assets.motivos_cambio 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_archivos_digitales_updated_at 
    BEFORE UPDATE ON digital_assets.archivos_digitales 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_versiones_archivo_updated_at 
    BEFORE UPDATE ON digital_assets.versiones_archivo 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_permisos_acceso_updated_at 
    BEFORE UPDATE ON digital_assets.permisos_acceso 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_registro_accesos_updated_at 
    BEFORE UPDATE ON digital_assets.registro_accesos 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();

CREATE TRIGGER trg_metadatos_archivo_updated_at 
    BEFORE UPDATE ON digital_assets.metadatos_archivo 
    FOR EACH ROW EXECUTE FUNCTION update_timestamp();