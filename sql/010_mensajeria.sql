-- =====================================================
-- CARBYFAH - SCHEMA MENSAJERÍA MILITAR
-- ARCHIVO: 010_mensajeria.sql
-- SISTEMA CHAT MILITAR + NOVEDADES + ESCALAMIENTO
-- ESTRUCTURA NORMALIZADA - SOLO DDL
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS mensajeria;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- 1. TIPOS DE CONVERSACIÓN
-- =====================================================
CREATE TABLE mensajeria.tipos_conversacion (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    es_grupal boolean DEFAULT false,
    permite_archivos boolean DEFAULT true,
    requiere_jerarquia boolean DEFAULT false,
    duracion_maxima_dias integer,
    participantes_maximos integer,
    nivel_seguridad_requerido integer DEFAULT 1,
    color_tipo varchar(7),
    icono_tipo varchar(50),
    es_oficial boolean DEFAULT false,
    requiere_aprobacion boolean DEFAULT false,
    genera_historial_completo boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_conversacion_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_conversacion_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE mensajeria.tipos_conversacion IS 'Tipos de conversaciones militares (Comando, Operacional, Técnico, etc.)';

-- =====================================================
-- 2. PRIORIDADES DE MENSAJE
-- =====================================================
CREATE TABLE mensajeria.prioridades_mensaje (
    id serial NOT NULL,
    codigo_prioridad varchar(20) NOT NULL,
    nombre_prioridad varchar(100) NOT NULL,
    descripcion text,
    orden_prioridad integer NOT NULL,
    tiempo_respuesta_esperado_minutos integer,
    requiere_confirmacion_lectura boolean DEFAULT false,
    genera_notificacion_push boolean DEFAULT false,
    escalamiento_automatico boolean DEFAULT false,
    color_prioridad varchar(7),
    icono_prioridad varchar(50),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT prioridades_mensaje_pkey PRIMARY KEY (id),
    CONSTRAINT prioridades_mensaje_codigo_key UNIQUE (codigo_prioridad)
);

COMMENT ON TABLE mensajeria.prioridades_mensaje IS 'Niveles de prioridad para mensajes militares';

-- =====================================================
-- 3. ESTADOS DE MENSAJE
-- =====================================================
CREATE TABLE mensajeria.estados_mensaje (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    es_estado_final boolean DEFAULT false,
    requiere_accion boolean DEFAULT false,
    permite_edicion boolean DEFAULT false,
    color_estado varchar(7),
    orden_flujo integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_mensaje_pkey PRIMARY KEY (id),
    CONSTRAINT estados_mensaje_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE mensajeria.estados_mensaje IS 'Estados del ciclo de vida de mensajes';

-- =====================================================
-- 4. TIPOS DE NOVEDAD
-- =====================================================
CREATE TABLE mensajeria.tipos_novedad (
    id serial NOT NULL,
    codigo_novedad varchar(30) NOT NULL,
    nombre_novedad varchar(100) NOT NULL,
    descripcion text,
    departamento_origen varchar(10), -- 'FA-1', 'FA-2', 'FA-3', 'FA-4', 'FA-5', 'FA-6'
    nivel_criticidad integer DEFAULT 3,
    requiere_escalamiento boolean DEFAULT false,
    tiempo_limite_atencion_horas integer,
    requiere_seguimiento boolean DEFAULT false,
    formato_reporte_automatico text,
    color_novedad varchar(7),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_novedad_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_novedad_codigo_key UNIQUE (codigo_novedad)
);

COMMENT ON TABLE mensajeria.tipos_novedad IS 'Tipos de novedades reportables por departamento';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 5. CONVERSACIONES (Dependiente)
-- =====================================================
CREATE TABLE mensajeria.conversaciones (
    id serial NOT NULL,
    codigo_conversacion varchar(50) NOT NULL,
    nombre_conversacion varchar(200) NOT NULL,
    descripcion_conversacion text,
    tipo_conversacion_id integer NOT NULL,
    creado_por_id integer NOT NULL,
    departamento_responsable varchar(10),
    estructura_militar_id integer,
    fecha_creacion timestamp with time zone NOT NULL DEFAULT now(),
    fecha_cierre timestamp with time zone,
    cerrado_por_id integer,
    motivo_cierre text,
    es_publica boolean DEFAULT false,
    requiere_invitacion boolean DEFAULT true,
    configuracion_notificaciones jsonb,
    mongodb_configuracion_avanzada_id varchar(24),
    mongodb_participantes_config_id varchar(24),
    mongodb_permisos_especiales_id varchar(24),
    ultima_actividad timestamp with time zone DEFAULT now(),
    total_mensajes integer DEFAULT 0,
    total_participantes integer DEFAULT 0,
    archivada boolean DEFAULT false,
    fecha_archivo timestamp with time zone,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT conversaciones_pkey PRIMARY KEY (id),
    CONSTRAINT conversaciones_codigo_key UNIQUE (codigo_conversacion)
);

COMMENT ON TABLE mensajeria.conversaciones IS 'Conversaciones militares (chats grupales e individuales)';

-- =====================================================
-- 6. PARTICIPANTES DE CONVERSACIÓN (Dependiente)
-- =====================================================
CREATE TABLE mensajeria.participantes_conversacion (
    id serial NOT NULL,
    conversacion_id integer NOT NULL,
    perfil_militar_id integer NOT NULL,
    rol_participante varchar(50) DEFAULT 'PARTICIPANTE', -- 'ADMINISTRADOR', 'MODERADOR', 'PARTICIPANTE'
    fecha_ingreso timestamp with time zone NOT NULL DEFAULT now(),
    fecha_salida timestamp with time zone,
    invitado_por_id integer,
    puede_escribir boolean DEFAULT true,
    puede_invitar boolean DEFAULT false,
    puede_administrar boolean DEFAULT false,
    notificaciones_habilitadas boolean DEFAULT true,
    ultimo_mensaje_leido_id integer,
    fecha_ultima_lectura timestamp with time zone,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT participantes_conversacion_pkey PRIMARY KEY (id),
    CONSTRAINT participantes_conversacion_unique_key UNIQUE (conversacion_id, perfil_militar_id)
);

COMMENT ON TABLE mensajeria.participantes_conversacion IS 'Participantes de conversaciones con roles y permisos';

-- =====================================================
-- 7. MENSAJES (Dependiente)
-- =====================================================
CREATE TABLE mensajeria.mensajes (
    id serial NOT NULL,
    conversacion_id integer NOT NULL,
    emisor_id integer NOT NULL,
    prioridad_mensaje_id integer NOT NULL,
    estado_mensaje_id integer NOT NULL,
    contenido_mensaje text NOT NULL,
    fecha_envio timestamp with time zone NOT NULL DEFAULT now(),
    fecha_editado timestamp with time zone,
    editado_por_id integer,
    mensaje_respondido_id integer,
    requiere_confirmacion boolean DEFAULT false,
    confirmaciones_recibidas integer DEFAULT 0,
    es_mensaje_sistema boolean DEFAULT false,
    reenvio_mensaje_id integer,
    mongodb_archivos_adjuntos_id varchar(24),
    mongodb_metadatos_mensaje_id varchar(24),
    mongodb_contenido_enriquecido_id varchar(24),
    mongodb_confirmaciones_detalle_id varchar(24),
    coordenadas_gps varchar(50),
    ubicacion_envio varchar(200),
    direccion_ip inet,
    agente_usuario text,
    eliminado_por_emisor boolean DEFAULT false,
    fecha_eliminacion timestamp with time zone,
    motivo_eliminacion text,
    total_lecturas integer DEFAULT 0,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT mensajes_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE mensajeria.mensajes IS 'Mensajes intercambiados en conversaciones militares';
COMMENT ON COLUMN mensajeria.mensajes.mongodb_archivos_adjuntos_id IS 'ObjectId MongoDB: archivos adjuntos al mensaje';

-- =====================================================
-- 8. REPORTES DE NOVEDADES (Dependiente)
-- =====================================================
CREATE TABLE mensajeria.reportes_novedades (
    id serial NOT NULL,
    numero_reporte varchar(50) NOT NULL,
    tipo_novedad_id integer NOT NULL,
    reportado_por_id integer NOT NULL,
    estructura_origen_id integer NOT NULL,
    fecha_novedad timestamp with time zone NOT NULL,
    fecha_reporte timestamp with time zone NOT NULL DEFAULT now(),
    titulo_novedad varchar(300) NOT NULL,
    descripcion_detallada text NOT NULL,
    impacto_operacional text,
    acciones_tomadas text,
    requiere_seguimiento boolean DEFAULT false,
    fecha_seguimiento date,
    responsable_seguimiento_id integer,
    conversacion_asociada_id integer,
    coordenadas_incidente varchar(50),
    ubicacion_incidente varchar(200),
    personal_involucrado_ids jsonb,
    recursos_involucrados_ids jsonb,
    mongodb_evidencias_id varchar(24),
    estado_reporte varchar(20) DEFAULT 'ABIERTO',
    fecha_cierre timestamp with time zone,
    cerrado_por_id integer,
    observaciones_cierre text,
    nivel_confidencialidad integer DEFAULT 1,
    distribuido_automaticamente boolean DEFAULT false,
    notificaciones_enviadas jsonb,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT reportes_novedades_pkey PRIMARY KEY (id),
    CONSTRAINT reportes_novedades_numero_key UNIQUE (numero_reporte)
);

COMMENT ON TABLE mensajeria.reportes_novedades IS 'Reportes de novedades militares con escalamiento automático';
COMMENT ON COLUMN mensajeria.reportes_novedades.personal_involucrado_ids IS 'Array JSON de IDs de personal involucrado';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Conversaciones
ALTER TABLE mensajeria.conversaciones
    ADD CONSTRAINT conversaciones_tipo_id_fkey FOREIGN KEY (tipo_conversacion_id)
    REFERENCES mensajeria.tipos_conversacion (id);

ALTER TABLE mensajeria.conversaciones
    ADD CONSTRAINT conversaciones_creado_por_id_fkey FOREIGN KEY (creado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.conversaciones
    ADD CONSTRAINT conversaciones_estructura_militar_id_fkey FOREIGN KEY (estructura_militar_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE mensajeria.conversaciones
    ADD CONSTRAINT conversaciones_cerrado_por_id_fkey FOREIGN KEY (cerrado_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Participantes Conversación
ALTER TABLE mensajeria.participantes_conversacion
    ADD CONSTRAINT participantes_conversacion_id_fkey FOREIGN KEY (conversacion_id)
    REFERENCES mensajeria.conversaciones (id);

ALTER TABLE mensajeria.participantes_conversacion
    ADD CONSTRAINT participantes_perfil_militar_id_fkey FOREIGN KEY (perfil_militar_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.participantes_conversacion
    ADD CONSTRAINT participantes_invitado_por_id_fkey FOREIGN KEY (invitado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.participantes_conversacion
    ADD CONSTRAINT participantes_ultimo_mensaje_id_fkey FOREIGN KEY (ultimo_mensaje_leido_id)
    REFERENCES mensajeria.mensajes (id);

-- Mensajes
ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_conversacion_id_fkey FOREIGN KEY (conversacion_id)
    REFERENCES mensajeria.conversaciones (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_emisor_id_fkey FOREIGN KEY (emisor_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_prioridad_id_fkey FOREIGN KEY (prioridad_mensaje_id)
    REFERENCES mensajeria.prioridades_mensaje (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_estado_id_fkey FOREIGN KEY (estado_mensaje_id)
    REFERENCES mensajeria.estados_mensaje (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_editado_por_id_fkey FOREIGN KEY (editado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_respondido_id_fkey FOREIGN KEY (mensaje_respondido_id)
    REFERENCES mensajeria.mensajes (id);

ALTER TABLE mensajeria.mensajes
    ADD CONSTRAINT mensajes_reenvio_id_fkey FOREIGN KEY (reenvio_mensaje_id)
    REFERENCES mensajeria.mensajes (id);

-- Reportes Novedades
ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_tipo_novedad_id_fkey FOREIGN KEY (tipo_novedad_id)
    REFERENCES mensajeria.tipos_novedad (id);

ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_reportado_por_id_fkey FOREIGN KEY (reportado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_estructura_origen_id_fkey FOREIGN KEY (estructura_origen_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_responsable_seguimiento_id_fkey FOREIGN KEY (responsable_seguimiento_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_conversacion_asociada_id_fkey FOREIGN KEY (conversacion_asociada_id)
    REFERENCES mensajeria.conversaciones (id);

ALTER TABLE mensajeria.reportes_novedades
    ADD CONSTRAINT reportes_cerrado_por_id_fkey FOREIGN KEY (cerrado_por_id)
    REFERENCES personal.perfiles_militares (id);