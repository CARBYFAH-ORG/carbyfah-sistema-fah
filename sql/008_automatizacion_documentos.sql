-- =====================================================
-- CARBYFAH - SCHEMA AUTOMATIZACIÓN DE DOCUMENTOS
-- ARCHIVO: 008_automatizacion_documentos.sql
-- SISTEMA PLANTILLAS, GENERACIÓN Y FIRMAS DIGITALES
-- ESTRUCTURA NORMALIZADA - SOLO DDL
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS automatizacion_documentos;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- 1. CATEGORÍAS DE DOCUMENTO
-- =====================================================
CREATE TABLE automatizacion_documentos.categorias_documento (
    id serial NOT NULL,
    codigo_categoria varchar(20) NOT NULL,
    nombre_categoria varchar(100) NOT NULL,
    descripcion text,
    color_categoria varchar(7),
    icono_categoria varchar(50),
    requiere_revision_legal boolean DEFAULT false,
    tiene_implicaciones_financieras boolean DEFAULT false,
    afecta_personal boolean DEFAULT false,
    requiere_notificacion_superior boolean DEFAULT false,
    tiempo_procesamiento_dias integer,
    prioridad_procesamiento integer DEFAULT 3,
    departamento_responsable varchar(100),
    requiere_seguimiento boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT categorias_documento_pkey PRIMARY KEY (id),
    CONSTRAINT categorias_documento_codigo_key UNIQUE (codigo_categoria)
);

COMMENT ON TABLE automatizacion_documentos.categorias_documento IS 'Categorías de documentos oficiales FAH';

-- =====================================================
-- 2. NIVELES DE CLASIFICACIÓN DOCUMENTO
-- =====================================================
CREATE TABLE automatizacion_documentos.niveles_clasificacion (
    id serial NOT NULL,
    codigo_nivel varchar(20) NOT NULL,
    nombre_nivel varchar(100) NOT NULL,
    descripcion text,
    orden_seguridad integer NOT NULL,
    color_nivel varchar(7),
    requiere_autorizacion_especial boolean DEFAULT false,
    tiempo_retencion_anos integer,
    puede_ser_digitalizado boolean DEFAULT true,
    requiere_custodia_especial boolean DEFAULT false,
    nivel_acceso_minimo_cargo varchar(50),
    genera_registro_acceso boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_clasificacion_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_clasificacion_codigo_key UNIQUE (codigo_nivel)
);

COMMENT ON TABLE automatizacion_documentos.niveles_clasificacion IS 'Niveles de clasificación de seguridad de documentos';

-- =====================================================
-- 3. FORMATOS DE SALIDA
-- =====================================================
CREATE TABLE automatizacion_documentos.formatos_salida (
    id serial NOT NULL,
    codigo_formato varchar(20) NOT NULL,
    nombre_formato varchar(100) NOT NULL,
    descripcion text,
    extension_archivo varchar(10),
    tipo_mime varchar(100),
    permite_edicion_posterior boolean DEFAULT false,
    requiere_software_especial boolean DEFAULT false,
    tamano_promedio_kb integer,
    es_formato_oficial boolean DEFAULT false,
    soporta_firmas_digitales boolean DEFAULT false,
    soporta_plantillas boolean DEFAULT true,
    calidad_impresion varchar(20),
    compatibilidad_movil boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT formatos_salida_pkey PRIMARY KEY (id),
    CONSTRAINT formatos_salida_codigo_key UNIQUE (codigo_formato)
);

COMMENT ON TABLE automatizacion_documentos.formatos_salida IS 'Formatos de salida de documentos (PDF, DOCX, HTML, etc.)';

-- =====================================================
-- 4. ORIENTACIONES DE PÁGINA
-- =====================================================
CREATE TABLE automatizacion_documentos.orientaciones_pagina (
    id serial NOT NULL,
    codigo_orientacion varchar(20) NOT NULL,
    nombre_orientacion varchar(100) NOT NULL,
    descripcion text,
    ancho_mm integer,
    alto_mm integer,
    es_orientacion_default boolean DEFAULT false,
    formatos_compatibles varchar(200),
    uso_recomendado text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT orientaciones_pagina_pkey PRIMARY KEY (id),
    CONSTRAINT orientaciones_pagina_codigo_key UNIQUE (codigo_orientacion)
);

COMMENT ON TABLE automatizacion_documentos.orientaciones_pagina IS 'Orientaciones de página (Vertical, Horizontal)';

-- =====================================================
-- 5. TAMAÑOS DE PÁGINA
-- =====================================================
CREATE TABLE automatizacion_documentos.tamanos_pagina (
    id serial NOT NULL,
    codigo_tamano varchar(20) NOT NULL,
    nombre_tamano varchar(100) NOT NULL,
    descripcion text,
    ancho_mm integer,
    alto_mm integer,
    ancho_pulgadas numeric(4,2),
    alto_pulgadas numeric(4,2),
    es_tamano_default boolean DEFAULT false,
    region_uso varchar(50),
    compatible_impresoras boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tamanos_pagina_pkey PRIMARY KEY (id),
    CONSTRAINT tamanos_pagina_codigo_key UNIQUE (codigo_tamano)
);

COMMENT ON TABLE automatizacion_documentos.tamanos_pagina IS 'Tamaños de página estándar (A4, Carta, Legal, etc.)';

-- =====================================================
-- 6. TIPOS DE ELEMENTO HTML
-- =====================================================
CREATE TABLE automatizacion_documentos.tipos_elemento_html (
    id serial NOT NULL,
    codigo_elemento varchar(20) NOT NULL,
    nombre_elemento varchar(100) NOT NULL,
    descripcion text,
    etiqueta_html varchar(20),
    tipo_input varchar(20),
    permite_validacion boolean DEFAULT true,
    soporta_placeholder boolean DEFAULT true,
    soporta_valores_multiples boolean DEFAULT false,
    es_interactivo boolean DEFAULT true,
    requiere_javascript boolean DEFAULT false,
    compatible_movil boolean DEFAULT true,
    nivel_accesibilidad varchar(20),
    atributos_comunes_json text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_elemento_html_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_elemento_html_codigo_key UNIQUE (codigo_elemento)
);

COMMENT ON TABLE automatizacion_documentos.tipos_elemento_html IS 'Tipos de elementos HTML para formularios';

-- =====================================================
-- 7. ESTADOS DE DOCUMENTO
-- =====================================================
CREATE TABLE automatizacion_documentos.estados_documento (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_edicion boolean DEFAULT false,
    es_estado_final boolean DEFAULT false,
    requiere_accion boolean DEFAULT false,
    color_estado varchar(7),
    icono_estado varchar(50),
    orden_flujo integer,
    genera_notificacion boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_documento_pkey PRIMARY KEY (id),
    CONSTRAINT estados_documento_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE automatizacion_documentos.estados_documento IS 'Estados del ciclo de vida de documentos';

-- =====================================================
-- 8. PRIORIDADES DE DOCUMENTO
-- =====================================================
CREATE TABLE automatizacion_documentos.prioridades_documento (
    id serial NOT NULL,
    codigo_prioridad varchar(20) NOT NULL,
    nombre_prioridad varchar(100) NOT NULL,
    descripcion text,
    orden_prioridad integer,
    tiempo_respuesta_horas integer,
    color_prioridad varchar(7),
    requiere_justificacion boolean DEFAULT false,
    genera_alerta_automatica boolean DEFAULT false,
    escalamiento_automatico boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT prioridades_documento_pkey PRIMARY KEY (id),
    CONSTRAINT prioridades_documento_codigo_key UNIQUE (codigo_prioridad)
);

COMMENT ON TABLE automatizacion_documentos.prioridades_documento IS 'Niveles de prioridad para documentos';

-- =====================================================
-- 9. ESTADOS DE APROBACIÓN
-- =====================================================
CREATE TABLE automatizacion_documentos.estados_aprobacion (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_accion boolean DEFAULT true,
    es_estado_final boolean DEFAULT false,
    requiere_comentarios boolean DEFAULT false,
    color_estado varchar(7),
    genera_notificacion boolean DEFAULT true,
    afecta_flujo boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_aprobacion_pkey PRIMARY KEY (id),
    CONSTRAINT estados_aprobacion_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE automatizacion_documentos.estados_aprobacion IS 'Estados de los flujos de aprobación';

-- =====================================================
-- 10. TIPOS DE FIRMA
-- =====================================================
CREATE TABLE automatizacion_documentos.tipos_firma (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    nivel_seguridad integer,
    requiere_certificado boolean DEFAULT false,
    requiere_biometria boolean DEFAULT false,
    es_juridicamente_valida boolean DEFAULT true,
    costo_implementacion varchar(20),
    tecnologia_requerida text,
    tiempo_procesamiento_segundos integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_firma_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_firma_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE automatizacion_documentos.tipos_firma IS 'Tipos de firma digital disponibles';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 11. TIPOS DE DOCUMENTO (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.tipos_documento (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    categoria_documento_id integer NOT NULL,
    nivel_clasificacion_id integer NOT NULL,
    formato_salida_id integer NOT NULL,
    requiere_aprobacion boolean DEFAULT false,
    plantilla_base varchar(100),
    requiere_firma_digital boolean DEFAULT false,
    tiempo_vigencia_dias integer,
    numeracion_automatica boolean DEFAULT true,
    prefijo_numeracion varchar(10),
    permite_modificacion_posterior boolean DEFAULT false,
    requiere_sello_oficial boolean DEFAULT false,
    mongodb_plantilla_id varchar(24),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_documento_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_documento_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE automatizacion_documentos.tipos_documento IS 'Tipos de documentos oficiales FAH';

-- =====================================================
-- 12. TIPOS DE CAMPO (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.tipos_campo (
    id serial NOT NULL,
    codigo_campo varchar(20) NOT NULL,
    nombre_campo varchar(100) NOT NULL,
    descripcion text,
    elemento_html_id integer NOT NULL,
    validacion_regex varchar(500),
    mensaje_validacion varchar(200),
    tiene_opciones boolean DEFAULT false,
    permite_valores_multiples boolean DEFAULT false,
    formato_display varchar(100),
    valor_por_defecto varchar(200),
    es_obligatorio_default boolean DEFAULT false,
    orden_display integer DEFAULT 1,
    mongodb_configuracion_campo_id varchar(24),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_campo_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_campo_codigo_key UNIQUE (codigo_campo)
);

COMMENT ON TABLE automatizacion_documentos.tipos_campo IS 'Tipos de campos para formularios de plantillas';

-- =====================================================
-- 13. PLANTILLAS DE DOCUMENTO (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.plantillas_documento (
    id serial NOT NULL,
    tipo_documento_id integer NOT NULL,
    nombre_plantilla varchar(200) NOT NULL,
    descripcion_plantilla text,
    contenido_plantilla text NOT NULL,
    formato_salida_id integer NOT NULL,
    orientacion_id integer NOT NULL,
    tamano_pagina_id integer NOT NULL,
    encabezado_plantilla text,
    pie_pagina_plantilla text,
    estilos_css text,
    mongodb_margenes_config_id varchar(24),
    mongodb_layout_config_id varchar(24),
    creado_por_id integer NOT NULL,
    fecha_creacion date DEFAULT CURRENT_DATE,
    version_plantilla varchar(10) DEFAULT '1.0',
    es_version_activa boolean DEFAULT true,
    requiere_revision boolean DEFAULT false,
    revisado_por_id integer,
    fecha_revision date,
    notas_version text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT plantillas_documento_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE automatizacion_documentos.plantillas_documento IS 'Plantillas para generación automática de documentos';

-- =====================================================
-- 14. CAMPOS DE PLANTILLA (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.campos_plantilla (
    id serial NOT NULL,
    plantilla_id integer NOT NULL,
    nombre_campo varchar(100) NOT NULL,
    etiqueta_campo varchar(200) NOT NULL,
    tipo_campo_id integer NOT NULL,
    es_obligatorio boolean DEFAULT false,
    valor_por_defecto varchar(500),
    mongodb_opciones_config_id varchar(24),
    mongodb_dependencias_config_id varchar(24),
    mongodb_validacion_config_id varchar(24),
    orden_campo integer NOT NULL,
    seccion_plantilla varchar(100),
    ancho_campo varchar(20) DEFAULT '100%',
    texto_ayuda text,
    placeholder varchar(200),
    validacion_personalizada text,
    es_campo_calculado boolean DEFAULT false,
    formula_calculo text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT campos_plantilla_pkey PRIMARY KEY (id),
    CONSTRAINT campos_plantilla_plantilla_nombre_key UNIQUE (plantilla_id, nombre_campo)
);

COMMENT ON TABLE automatizacion_documentos.campos_plantilla IS 'Campos dinámicos de plantillas de documentos';

-- =====================================================
-- 15. DOCUMENTOS GENERADOS (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.documentos_generados (
    id serial NOT NULL,
    plantilla_id integer NOT NULL,
    numero_correlativo varchar(50) NOT NULL,
    titulo_documento varchar(300) NOT NULL,
    generado_por_id integer NOT NULL,
    fecha_generacion timestamp with time zone DEFAULT now(),
    destinatario_id integer,
    estado_documento_id integer NOT NULL,
    prioridad_documento_id integer NOT NULL,
    mongodb_destinatarios_copia_id varchar(24),
    mongodb_valores_campos_id varchar(24),
    mongodb_metadatos_id varchar(24),
    contenido_final text,
    archivo_generado_id integer,
    hash_contenido varchar(64),
    fecha_vencimiento timestamp with time zone,
    requiere_respuesta boolean DEFAULT false,
    fecha_limite_respuesta timestamp with time zone,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT documentos_generados_pkey PRIMARY KEY (id),
    CONSTRAINT documentos_generados_numero_key UNIQUE (numero_correlativo)
);

COMMENT ON TABLE automatizacion_documentos.documentos_generados IS 'Documentos generados a partir de plantillas';

-- =====================================================
-- 16. FLUJOS DE APROBACIÓN (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.flujos_aprobacion (
    id serial NOT NULL,
    documento_id integer NOT NULL,
    nivel_aprobacion integer NOT NULL,
    aprobador_id integer NOT NULL,
    cargo_aprobador varchar(200),
    fecha_envio timestamp with time zone,
    fecha_respuesta timestamp with time zone,
    estado_aprobacion_id integer NOT NULL,
    comentarios_aprobador text,
    tiempo_limite_horas integer DEFAULT 72,
    fecha_limite timestamp with time zone,
    orden_secuencial integer NOT NULL,
    es_aprobacion_final boolean DEFAULT false,
    requiere_justificacion_rechazo boolean DEFAULT true,
    delegado_a_id integer,
    motivo_delegacion text,
    notificacion_enviada boolean DEFAULT false,
    recordatorios_enviados integer DEFAULT 0,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT flujos_aprobacion_pkey PRIMARY KEY (id),
    CONSTRAINT flujos_aprobacion_documento_nivel_aprobador_key UNIQUE (documento_id, nivel_aprobacion, aprobador_id)
);

COMMENT ON TABLE automatizacion_documentos.flujos_aprobacion IS 'Flujos de aprobación de documentos por niveles jerárquicos';

-- =====================================================
-- 17. FIRMAS DIGITALES (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.firmas_digitales (
    id serial NOT NULL,
    documento_id integer NOT NULL,
    firmante_id integer NOT NULL,
    orden_firma integer NOT NULL,
    fecha_firma timestamp with time zone DEFAULT now(),
    tipo_firma_id integer NOT NULL,
    hash_firma varchar(256) NOT NULL,
    certificado_digital text,
    algoritmo_hash varchar(20) DEFAULT 'SHA-256',
    ubicacion_firma varchar(100),
    razon_firma varchar(200),
    datos_biometricos_hash varchar(256),
    direccion_ip inet,
    agente_usuario text,
    marca_tiempo_servidor timestamp with time zone DEFAULT now(),
    es_valida boolean DEFAULT true,
    fecha_validacion timestamp with time zone,
    validado_por_id integer,
    observaciones_firma text,
    archivo_firma_id integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT firmas_digitales_pkey PRIMARY KEY (id),
    CONSTRAINT firmas_digitales_documento_firmante_orden_key UNIQUE (documento_id, firmante_id, orden_firma)
);

COMMENT ON TABLE automatizacion_documentos.firmas_digitales IS 'Firmas digitales aplicadas a documentos';

-- =====================================================
-- 18. HISTORIAL CAMBIOS DOCUMENTO (Dependiente)
-- =====================================================
CREATE TABLE automatizacion_documentos.historial_cambios_documento (
    id serial NOT NULL,
    documento_id integer NOT NULL,
    usuario_id integer NOT NULL,
    tipo_cambio varchar(50) NOT NULL, -- 'CREACION', 'EDICION', 'APROBACION', 'FIRMA', 'ANULACION'
    fecha_cambio timestamp with time zone NOT NULL DEFAULT now(),
    estado_anterior varchar(50),
    estado_nuevo varchar(50),
    campo_modificado varchar(100),
    valor_anterior text,
    valor_nuevo text,
    motivo_cambio text,
    direccion_ip varchar(15),
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT historial_cambios_documento_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE automatizacion_documentos.historial_cambios_documento IS 'Historial de cambios de documentos para auditoría';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Tipos de Documento
ALTER TABLE automatizacion_documentos.tipos_documento
    ADD CONSTRAINT tipos_documento_categoria_id_fkey FOREIGN KEY (categoria_documento_id)
    REFERENCES automatizacion_documentos.categorias_documento (id);

ALTER TABLE automatizacion_documentos.tipos_documento
    ADD CONSTRAINT tipos_documento_nivel_clasificacion_id_fkey FOREIGN KEY (nivel_clasificacion_id)
    REFERENCES automatizacion_documentos.niveles_clasificacion (id);

ALTER TABLE automatizacion_documentos.tipos_documento
    ADD CONSTRAINT tipos_documento_formato_salida_id_fkey FOREIGN KEY (formato_salida_id)
    REFERENCES automatizacion_documentos.formatos_salida (id);

-- Tipos de Campo
ALTER TABLE automatizacion_documentos.tipos_campo
    ADD CONSTRAINT tipos_campo_elemento_html_id_fkey FOREIGN KEY (elemento_html_id)
    REFERENCES automatizacion_documentos.tipos_elemento_html (id);

-- Plantillas de Documento
ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_tipo_documento_id_fkey FOREIGN KEY (tipo_documento_id)
    REFERENCES automatizacion_documentos.tipos_documento (id);

ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_formato_salida_id_fkey FOREIGN KEY (formato_salida_id)
    REFERENCES automatizacion_documentos.formatos_salida (id);

ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_orientacion_id_fkey FOREIGN KEY (orientacion_id)
    REFERENCES automatizacion_documentos.orientaciones_pagina (id);

ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_tamano_pagina_id_fkey FOREIGN KEY (tamano_pagina_id)
    REFERENCES automatizacion_documentos.tamanos_pagina (id);

ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_creado_por_id_fkey FOREIGN KEY (creado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.plantillas_documento
    ADD CONSTRAINT plantillas_revisado_por_id_fkey FOREIGN KEY (revisado_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Campos de Plantilla
ALTER TABLE automatizacion_documentos.campos_plantilla
    ADD CONSTRAINT campos_plantilla_id_fkey FOREIGN KEY (plantilla_id)
    REFERENCES automatizacion_documentos.plantillas_documento (id);

ALTER TABLE automatizacion_documentos.campos_plantilla
    ADD CONSTRAINT campos_tipo_campo_id_fkey FOREIGN KEY (tipo_campo_id)
    REFERENCES automatizacion_documentos.tipos_campo (id);

-- Documentos Generados
ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_plantilla_id_fkey FOREIGN KEY (plantilla_id)
    REFERENCES automatizacion_documentos.plantillas_documento (id);

ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_generado_por_id_fkey FOREIGN KEY (generado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_destinatario_id_fkey FOREIGN KEY (destinatario_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_estado_documento_id_fkey FOREIGN KEY (estado_documento_id)
    REFERENCES automatizacion_documentos.estados_documento (id);

ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_prioridad_documento_id_fkey FOREIGN KEY (prioridad_documento_id)
    REFERENCES automatizacion_documentos.prioridades_documento (id);

ALTER TABLE automatizacion_documentos.documentos_generados
    ADD CONSTRAINT documentos_archivo_generado_id_fkey FOREIGN KEY (archivo_generado_id)
    REFERENCES activos_digitales.archivos_digitales (id);

-- Flujos de Aprobación
ALTER TABLE automatizacion_documentos.flujos_aprobacion
    ADD CONSTRAINT flujos_documento_id_fkey FOREIGN KEY (documento_id)
    REFERENCES automatizacion_documentos.documentos_generados (id);

ALTER TABLE automatizacion_documentos.flujos_aprobacion
    ADD CONSTRAINT flujos_aprobador_id_fkey FOREIGN KEY (aprobador_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.flujos_aprobacion
    ADD CONSTRAINT flujos_estado_aprobacion_id_fkey FOREIGN KEY (estado_aprobacion_id)
    REFERENCES automatizacion_documentos.estados_aprobacion (id);

ALTER TABLE automatizacion_documentos.flujos_aprobacion
    ADD CONSTRAINT flujos_delegado_a_id_fkey FOREIGN KEY (delegado_a_id)
    REFERENCES personal.perfiles_militares (id);

-- Firmas Digitales
ALTER TABLE automatizacion_documentos.firmas_digitales
    ADD CONSTRAINT firmas_documento_id_fkey FOREIGN KEY (documento_id)
    REFERENCES automatizacion_documentos.documentos_generados (id);

ALTER TABLE automatizacion_documentos.firmas_digitales
    ADD CONSTRAINT firmas_firmante_id_fkey FOREIGN KEY (firmante_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.firmas_digitales
    ADD CONSTRAINT firmas_tipo_firma_id_fkey FOREIGN KEY (tipo_firma_id)
    REFERENCES automatizacion_documentos.tipos_firma (id);

ALTER TABLE automatizacion_documentos.firmas_digitales
    ADD CONSTRAINT firmas_validado_por_id_fkey FOREIGN KEY (validado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE automatizacion_documentos.firmas_digitales
    ADD CONSTRAINT firmas_archivo_firma_id_fkey FOREIGN KEY (archivo_firma_id)
    REFERENCES activos_digitales.archivos_digitales (id);

-- Historial Cambios Documento
ALTER TABLE automatizacion_documentos.historial_cambios_documento
    ADD CONSTRAINT historial_documento_id_fkey FOREIGN KEY (documento_id)
    REFERENCES automatizacion_documentos.documentos_generados (id);

ALTER TABLE automatizacion_documentos.historial_cambios_documento
    ADD CONSTRAINT historial_usuario_id_fkey FOREIGN KEY (usuario_id)
    REFERENCES personal.perfiles_militares (id);