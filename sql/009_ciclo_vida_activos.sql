-- =====================================================
-- CARBYFAH - SCHEMA CICLO DE VIDA DE ACTIVOS
-- ARCHIVO: 009_ciclo_vida_activos.sql
-- SISTEMA GESTIÓN MANTENIMIENTO, PRÉSTAMOS Y ALERTAS
-- ESTRUCTURA NORMALIZADA - SOLO DDL
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS ciclo_vida_activos;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- 1. TIPOS DE ACTIVO
-- =====================================================
CREATE TABLE ciclo_vida_activos.tipos_activo (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    categoria_principal varchar(50),
    descripcion text,
    requiere_mantenimiento_programado boolean DEFAULT true,
    vida_util_anos integer,
    requiere_seguro boolean DEFAULT false,
    requiere_licencia_operacion boolean DEFAULT false,
    valor_minimo_seguimiento numeric(10,2),
    frecuencia_inspeccion_meses integer,
    tasa_depreciacion_anual numeric(5,2),
    requiere_certificacion_tecnica boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_activo_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_activo_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE ciclo_vida_activos.tipos_activo IS 'Tipos de activos gestionados en el ciclo de vida';
COMMENT ON COLUMN ciclo_vida_activos.tipos_activo.valor_minimo_seguimiento IS 'Valor mínimo para incluir en seguimiento detallado';

-- =====================================================
-- 2. NIVELES DE CRITICIDAD
-- =====================================================
CREATE TABLE ciclo_vida_activos.niveles_criticidad (
    id serial NOT NULL,
    codigo_nivel varchar(20) NOT NULL,
    nombre_nivel varchar(100) NOT NULL,
    descripcion text,
    factor_multiplicador numeric(3,2),
    frecuencia_inspeccion_factor numeric(3,2),
    prioridad_mantenimiento integer,
    requiere_respaldo boolean DEFAULT false,
    tiempo_respuesta_horas integer,
    nivel_autorizacion_requerido varchar(50),
    color_nivel varchar(7),
    notificacion_automatica boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_criticidad_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_criticidad_codigo_key UNIQUE (codigo_nivel)
);

COMMENT ON TABLE ciclo_vida_activos.niveles_criticidad IS 'Niveles de criticidad de activos para priorización';

-- =====================================================
-- 3. ESTADOS OPERACIONALES GENERALES
-- =====================================================
CREATE TABLE ciclo_vida_activos.estados_operacionales (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_uso_operacional boolean DEFAULT false,
    requiere_autorizacion_cambio boolean DEFAULT false,
    afecta_disponibilidad boolean DEFAULT true,
    color_estado varchar(7),
    icono_estado varchar(50),
    orden_prioridad integer,
    es_estado_temporal boolean DEFAULT false,
    requiere_justificacion boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_operacionales_pkey PRIMARY KEY (id),
    CONSTRAINT estados_operacionales_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE ciclo_vida_activos.estados_operacionales IS 'Estados operacionales generales de activos';

-- =====================================================
-- 4. ESTADOS DE CONDICIÓN
-- =====================================================
CREATE TABLE ciclo_vida_activos.estados_condicion (
    id serial NOT NULL,
    codigo_condicion varchar(20) NOT NULL,
    nombre_condicion varchar(100) NOT NULL,
    descripcion text,
    porcentaje_condicion integer,
    requiere_atencion_inmediata boolean DEFAULT false,
    permite_uso_normal boolean DEFAULT true,
    requiere_inspeccion boolean DEFAULT false,
    factor_depreciacion numeric(3,2),
    color_condicion varchar(7),
    genera_alerta boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_condicion_pkey PRIMARY KEY (id),
    CONSTRAINT estados_condicion_codigo_key UNIQUE (codigo_condicion)
);

COMMENT ON TABLE ciclo_vida_activos.estados_condicion IS 'Estados de condición física de activos';

-- =====================================================
-- 5. NIVELES DE ESPECIALIZACIÓN TÉCNICA
-- =====================================================
CREATE TABLE ciclo_vida_activos.niveles_especializacion (
    id serial NOT NULL,
    codigo_especializacion varchar(20) NOT NULL,
    nombre_especializacion varchar(50) NOT NULL,
    descripcion text,
    anos_experiencia_minima integer NOT NULL,
    certificaciones_requeridas text,
    salario_promedio_mensual numeric(10,2),
    disponibilidad_personal boolean DEFAULT true,
    requiere_entrenamiento_especial boolean DEFAULT false,
    puede_autorizar_trabajos boolean DEFAULT false,
    color_ui varchar(7),
    icono_ui varchar(50),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_especializacion_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_especializacion_codigo_key UNIQUE (codigo_especializacion)
);

COMMENT ON TABLE ciclo_vida_activos.niveles_especializacion IS 'Niveles de especialización técnica para mantenimiento';
COMMENT ON COLUMN ciclo_vida_activos.niveles_especializacion.anos_experiencia_minima IS 'Años mínimos de experiencia requeridos para este nivel';

-- =====================================================
-- 6. TIPOS DE MANTENIMIENTO
-- =====================================================
CREATE TABLE ciclo_vida_activos.tipos_mantenimiento (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    requiere_autorizacion boolean DEFAULT false,
    es_programado boolean DEFAULT true,
    duracion_estimada_horas integer,
    nivel_especializacion_id integer NOT NULL,
    costo_promedio_estimado numeric(10,2),
    requiere_repuestos boolean DEFAULT true,
    requiere_herramientas_especiales boolean DEFAULT false,
    detiene_operacion boolean DEFAULT true,
    frecuencia_recomendada_meses integer,
    prioridad_ejecucion integer DEFAULT 3,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_mantenimiento_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_mantenimiento_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE ciclo_vida_activos.tipos_mantenimiento IS 'Tipos de mantenimiento aplicables a activos';
COMMENT ON COLUMN ciclo_vida_activos.tipos_mantenimiento.detiene_operacion IS 'Si el mantenimiento requiere detener la operación del activo';

-- =====================================================
-- 7. ESTADOS POST-SERVICIO
-- =====================================================
CREATE TABLE ciclo_vida_activos.estados_post_servicio (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_retorno_servicio boolean DEFAULT false,
    requiere_inspeccion_adicional boolean DEFAULT false,
    requiere_aprobacion_supervisor boolean DEFAULT false,
    dias_seguimiento integer,
    accion_recomendada text,
    color_estado varchar(7),
    genera_alerta_automatica boolean DEFAULT false,
    permite_asignacion_nueva boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_post_servicio_pkey PRIMARY KEY (id),
    CONSTRAINT estados_post_servicio_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE ciclo_vida_activos.estados_post_servicio IS 'Estados de activos después de mantenimiento';

-- =====================================================
-- 8. TIPOS DE ALERTA
-- =====================================================
CREATE TABLE ciclo_vida_activos.tipos_alerta (
    id serial NOT NULL,
    codigo_tipo varchar(30) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    prioridad_default varchar(20) DEFAULT 'MEDIA',
    requiere_escalamiento_automatico boolean DEFAULT false,
    dias_limite_resolucion integer,
    genera_notificacion boolean DEFAULT true,
    color_alerta varchar(7),
    categoria_alerta varchar(50),
    es_critica boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_alerta_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_alerta_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE ciclo_vida_activos.tipos_alerta IS 'Tipos de alertas de mantenimiento';

-- =====================================================
-- 9. ESTADOS DE ALERTA
-- =====================================================
CREATE TABLE ciclo_vida_activos.estados_alerta (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_atencion boolean DEFAULT true,
    es_estado_final boolean DEFAULT false,
    requiere_justificacion boolean DEFAULT false,
    genera_seguimiento boolean DEFAULT true,
    color_estado varchar(7),
    orden_prioridad integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_alerta_pkey PRIMARY KEY (id),
    CONSTRAINT estados_alerta_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE ciclo_vida_activos.estados_alerta IS 'Estados del flujo de vida de alertas';

-- =====================================================
-- 10. ESTADOS DE PRÉSTAMO
-- =====================================================
CREATE TABLE ciclo_vida_activos.estados_prestamo (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_entrega boolean DEFAULT false,
    requiere_autorizacion boolean DEFAULT true,
    es_estado_final boolean DEFAULT false,
    genera_penalizacion boolean DEFAULT false,
    requiere_justificacion boolean DEFAULT false,
    color_estado varchar(7),
    notifica_supervisor boolean DEFAULT false,
    dias_limite_accion integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_prestamo_pkey PRIMARY KEY (id),
    CONSTRAINT estados_prestamo_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE ciclo_vida_activos.estados_prestamo IS 'Estados del flujo de préstamos de activos';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 11. FICHAS DE ACTIVOS (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.fichas_activos (
    id serial NOT NULL,
    numero_serie varchar(100) NOT NULL,
    numero_inventario varchar(50) NOT NULL,
    tipo_activo_id integer NOT NULL,
    marca_fabricante varchar(100) NOT NULL,
    modelo_especifico varchar(200) NOT NULL,
    ano_fabricacion integer,
    fecha_adquisicion date NOT NULL,
    costo_adquisicion numeric(12,2),
    moneda_adquisicion varchar(3) DEFAULT 'HNL',
    proveedor varchar(200),
    estado_operacional_id integer NOT NULL,
    nivel_criticidad_id integer NOT NULL,
    ubicacion_actual_id integer NOT NULL,
    responsable_actual_id integer NOT NULL,
    mongodb_especificaciones_id varchar(24) NOT NULL,
    manual_operacion_id integer,
    vida_util_estimada integer,
    valor_actual_estimado numeric(12,2),
    fecha_ultima_valuacion date,
    observaciones_generales text,
    imagen_principal_id integer,
    horas_uso_total numeric(10,2) DEFAULT 0,
    kilometraje_total integer DEFAULT 0,
    ciclos_uso_total integer DEFAULT 0,
    fecha_vencimiento_garantia date,
    proveedor_garantia varchar(200),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT fichas_activos_pkey PRIMARY KEY (id),
    CONSTRAINT fichas_activos_numero_serie_key UNIQUE (numero_serie),
    CONSTRAINT fichas_activos_numero_inventario_key UNIQUE (numero_inventario)
);

COMMENT ON TABLE ciclo_vida_activos.fichas_activos IS 'Fichas maestras de activos con información completa';

-- =====================================================
-- 12. HISTORIAL DE MANTENIMIENTO (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.historial_mantenimiento (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    numero_orden_trabajo varchar(50) NOT NULL,
    fecha_inicio_servicio timestamp with time zone NOT NULL,
    fecha_fin_servicio timestamp with time zone,
    tipo_mantenimiento_id integer NOT NULL,
    tecnico_principal_id integer NOT NULL,
    mongodb_datos_mantenimiento_id varchar(24) NOT NULL,
    descripcion_trabajo text NOT NULL,
    problemas_encontrados text,
    solucion_aplicada text,
    costo_mano_obra numeric(10,2),
    costo_repuestos numeric(10,2),
    costo_total numeric(10,2),
    horas_trabajo numeric(5,2),
    estado_post_servicio_id integer NOT NULL,
    proxima_revision date,
    horas_proxima_revision numeric(10,2),
    observaciones_tecnico text,
    aprobado_por_id integer,
    certificacion_calidad boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT historial_mantenimiento_pkey PRIMARY KEY (id),
    CONSTRAINT historial_mantenimiento_numero_orden_key UNIQUE (numero_orden_trabajo)
);

COMMENT ON TABLE ciclo_vida_activos.historial_mantenimiento IS 'Historial completo de mantenimientos realizados';

-- =====================================================
-- 13. ALERTAS DE MANTENIMIENTO (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.alertas_mantenimiento (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    tipo_alerta_id integer NOT NULL,
    estado_alerta_id integer NOT NULL,
    fecha_alerta timestamp with time zone NOT NULL DEFAULT now(),
    descripcion_alerta text NOT NULL,
    responsable_alerta_id integer NOT NULL,
    fecha_limite timestamp with time zone,
    fecha_atencion timestamp with time zone,
    atendido_por_id integer,
    fecha_resolucion timestamp with time zone,
    resuelto_por_id integer,
    solucion_aplicada text,
    costo_resolucion numeric(10,2),
    escalado_a_id integer,
    observaciones_resolucion text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT alertas_mantenimiento_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE ciclo_vida_activos.alertas_mantenimiento IS 'Alertas automáticas y manuales de mantenimiento';

-- =====================================================
-- 14. PRÉSTAMOS DE ACTIVOS (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.prestamos_activos (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    numero_prestamo varchar(50) NOT NULL,
    solicitado_por_id integer NOT NULL,
    unidad_solicitante_id integer,
    autorizado_por_id integer,
    fecha_solicitud timestamp with time zone DEFAULT now(),
    fecha_autorizacion timestamp with time zone,
    fecha_entrega timestamp with time zone,
    fecha_devolucion_programada timestamp with time zone NOT NULL,
    fecha_devolucion_real timestamp with time zone,
    motivo_prestamo text NOT NULL,
    estado_prestamo_id integer NOT NULL,
    condicion_entrega_id integer,
    condicion_devolucion_id integer,
    condiciones_prestamo text,
    observaciones_entrega text,
    observaciones_devolucion text,
    kilometraje_entrega integer,
    kilometraje_devolucion integer,
    horas_entrega numeric(10,2),
    horas_devolucion numeric(10,2),
    penalizaciones numeric(10,2) DEFAULT 0,
    motivo_penalizacion text,
    seguro_prestamo boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT prestamos_activos_pkey PRIMARY KEY (id),
    CONSTRAINT prestamos_activos_numero_key UNIQUE (numero_prestamo)
);

COMMENT ON TABLE ciclo_vida_activos.prestamos_activos IS 'Gestión de préstamos de activos entre unidades';

-- =====================================================
-- 15. MOVIMIENTOS DE ACTIVOS (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.movimientos_activos (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    numero_movimiento varchar(50) NOT NULL,
    tipo_movimiento varchar(50) NOT NULL, -- 'ASIGNACION', 'TRANSFERENCIA', 'PRESTAMO', 'DEVOLUCION'
    ubicacion_origen_id integer,
    ubicacion_destino_id integer NOT NULL,
    responsable_origen_id integer,
    responsable_destino_id integer NOT NULL,
    autorizado_por_id integer NOT NULL,
    fecha_movimiento timestamp with time zone NOT NULL DEFAULT now(),
    motivo_movimiento text NOT NULL,
    condicion_activo_origen_id integer,
    condicion_activo_destino_id integer,
    costo_movimiento numeric(8,2),
    observaciones_movimiento text,
    documentos_adjuntos jsonb,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT movimientos_activos_pkey PRIMARY KEY (id),
    CONSTRAINT movimientos_activos_numero_key UNIQUE (numero_movimiento)
);

COMMENT ON TABLE ciclo_vida_activos.movimientos_activos IS 'Historial de movimientos y transferencias de activos';

-- =====================================================
-- 16. VALUACIONES DE ACTIVOS (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.valuaciones_activos (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    numero_valuacion varchar(50) NOT NULL,
    fecha_valuacion date NOT NULL,
    valuador_id integer NOT NULL,
    metodo_valuacion varchar(50) NOT NULL, -- 'COSTO_HISTORICO', 'VALOR_MERCADO', 'VALOR_REPOSICION'
    valor_anterior numeric(12,2),
    valor_nuevo numeric(12,2) NOT NULL,
    depreciacion_acumulada numeric(12,2),
    vida_util_restante_anos integer,
    factores_depreciacion text,
    condicion_fisica_id integer,
    observaciones_valuacion text,
    documento_valuacion_id integer,
    aprobado_por_id integer,
    fecha_aprobacion date,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT valuaciones_activos_pkey PRIMARY KEY (id),
    CONSTRAINT valuaciones_activos_numero_key UNIQUE (numero_valuacion)
);

COMMENT ON TABLE ciclo_vida_activos.valuaciones_activos IS 'Valuaciones periódicas de activos para control patrimonial';

-- =====================================================
-- 17. INSPECCIONES DE ACTIVOS (Dependiente)
-- =====================================================
CREATE TABLE ciclo_vida_activos.inspecciones_activos (
    id serial NOT NULL,
    activo_id integer NOT NULL,
    numero_inspeccion varchar(50) NOT NULL,
    fecha_inspeccion date NOT NULL,
    inspector_id integer NOT NULL,
    tipo_inspeccion varchar(50) NOT NULL, -- 'RUTINARIA', 'ESPECIAL', 'POST_MANTENIMIENTO', 'PRE_OPERACION'
    condicion_general_id integer NOT NULL,
    condicion_mecanica varchar(20),
    condicion_electrica varchar(20),
    condicion_estructura varchar(20),
    defectos_encontrados text,
    reparaciones_recomendadas text,
    es_operativo boolean DEFAULT true,
    requiere_mantenimiento_inmediato boolean DEFAULT false,
    fecha_proxima_inspeccion date,
    observaciones_inspector text,
    fotografia_inspeccion_id integer,
    checklist_completado jsonb,
    aprobado_por_id integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT inspecciones_activos_pkey PRIMARY KEY (id),
    CONSTRAINT inspecciones_activos_numero_key UNIQUE (numero_inspeccion)
);

COMMENT ON TABLE ciclo_vida_activos.inspecciones_activos IS 'Inspecciones técnicas de activos';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Tipos de Mantenimiento (Referencia a niveles de especialización)
ALTER TABLE ciclo_vida_activos.tipos_mantenimiento
    ADD CONSTRAINT tipos_mantenimiento_nivel_especializacion_id_fkey FOREIGN KEY (nivel_especializacion_id)
    REFERENCES ciclo_vida_activos.niveles_especializacion (id);

-- Fichas de Activos
ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_tipo_activo_id_fkey FOREIGN KEY (tipo_activo_id)
    REFERENCES ciclo_vida_activos.tipos_activo (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_estado_operacional_id_fkey FOREIGN KEY (estado_operacional_id)
    REFERENCES ciclo_vida_activos.estados_operacionales (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_nivel_criticidad_id_fkey FOREIGN KEY (nivel_criticidad_id)
    REFERENCES ciclo_vida_activos.niveles_criticidad (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_ubicacion_actual_id_fkey FOREIGN KEY (ubicacion_actual_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_responsable_actual_id_fkey FOREIGN KEY (responsable_actual_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_manual_operacion_id_fkey FOREIGN KEY (manual_operacion_id)
    REFERENCES activos_digitales.archivos_digitales (id);

ALTER TABLE ciclo_vida_activos.fichas_activos
    ADD CONSTRAINT fichas_activos_imagen_principal_id_fkey FOREIGN KEY (imagen_principal_id)
    REFERENCES activos_digitales.archivos_digitales (id);

-- Historial de Mantenimiento
ALTER TABLE ciclo_vida_activos.historial_mantenimiento
    ADD CONSTRAINT historial_mantenimiento_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.historial_mantenimiento
    ADD CONSTRAINT historial_mantenimiento_tipo_id_fkey FOREIGN KEY (tipo_mantenimiento_id)
    REFERENCES ciclo_vida_activos.tipos_mantenimiento (id);

ALTER TABLE ciclo_vida_activos.historial_mantenimiento
    ADD CONSTRAINT historial_mantenimiento_tecnico_id_fkey FOREIGN KEY (tecnico_principal_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.historial_mantenimiento
    ADD CONSTRAINT historial_mantenimiento_estado_post_id_fkey FOREIGN KEY (estado_post_servicio_id)
    REFERENCES ciclo_vida_activos.estados_post_servicio (id);

ALTER TABLE ciclo_vida_activos.historial_mantenimiento
    ADD CONSTRAINT historial_mantenimiento_aprobado_por_id_fkey FOREIGN KEY (aprobado_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Alertas de Mantenimiento
ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_tipo_alerta_id_fkey FOREIGN KEY (tipo_alerta_id)
    REFERENCES ciclo_vida_activos.tipos_alerta (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_estado_alerta_id_fkey FOREIGN KEY (estado_alerta_id)
    REFERENCES ciclo_vida_activos.estados_alerta (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_responsable_id_fkey FOREIGN KEY (responsable_alerta_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_atendido_por_id_fkey FOREIGN KEY (atendido_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_resuelto_por_id_fkey FOREIGN KEY (resuelto_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.alertas_mantenimiento
    ADD CONSTRAINT alertas_mantenimiento_escalado_a_id_fkey FOREIGN KEY (escalado_a_id)
    REFERENCES personal.perfiles_militares (id);

-- Préstamos de Activos
ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_solicitado_por_id_fkey FOREIGN KEY (solicitado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_unidad_solicitante_id_fkey FOREIGN KEY (unidad_solicitante_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_autorizado_por_id_fkey FOREIGN KEY (autorizado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_estado_prestamo_id_fkey FOREIGN KEY (estado_prestamo_id)
    REFERENCES ciclo_vida_activos.estados_prestamo (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_condicion_entrega_id_fkey FOREIGN KEY (condicion_entrega_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

ALTER TABLE ciclo_vida_activos.prestamos_activos
    ADD CONSTRAINT prestamos_activos_condicion_devolucion_id_fkey FOREIGN KEY (condicion_devolucion_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

-- Movimientos de Activos
ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_ubicacion_origen_id_fkey FOREIGN KEY (ubicacion_origen_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_ubicacion_destino_id_fkey FOREIGN KEY (ubicacion_destino_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_responsable_origen_id_fkey FOREIGN KEY (responsable_origen_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_responsable_destino_id_fkey FOREIGN KEY (responsable_destino_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_autorizado_por_id_fkey FOREIGN KEY (autorizado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_condicion_origen_id_fkey FOREIGN KEY (condicion_activo_origen_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

ALTER TABLE ciclo_vida_activos.movimientos_activos
    ADD CONSTRAINT movimientos_activos_condicion_destino_id_fkey FOREIGN KEY (condicion_activo_destino_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

-- Valuaciones de Activos
ALTER TABLE ciclo_vida_activos.valuaciones_activos
    ADD CONSTRAINT valuaciones_activos_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.valuaciones_activos
    ADD CONSTRAINT valuaciones_activos_valuador_id_fkey FOREIGN KEY (valuador_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.valuaciones_activos
    ADD CONSTRAINT valuaciones_activos_condicion_fisica_id_fkey FOREIGN KEY (condicion_fisica_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

ALTER TABLE ciclo_vida_activos.valuaciones_activos
    ADD CONSTRAINT valuaciones_activos_documento_id_fkey FOREIGN KEY (documento_valuacion_id)
    REFERENCES activos_digitales.archivos_digitales (id);

ALTER TABLE ciclo_vida_activos.valuaciones_activos
    ADD CONSTRAINT valuaciones_activos_aprobado_por_id_fkey FOREIGN KEY (aprobado_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Inspecciones de Activos
ALTER TABLE ciclo_vida_activos.inspecciones_activos
    ADD CONSTRAINT inspecciones_activos_activo_id_fkey FOREIGN KEY (activo_id)
    REFERENCES ciclo_vida_activos.fichas_activos (id);

ALTER TABLE ciclo_vida_activos.inspecciones_activos
    ADD CONSTRAINT inspecciones_activos_inspector_id_fkey FOREIGN KEY (inspector_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE ciclo_vida_activos.inspecciones_activos
    ADD CONSTRAINT inspecciones_activos_condicion_general_id_fkey FOREIGN KEY (condicion_general_id)
    REFERENCES ciclo_vida_activos.estados_condicion (id);

ALTER TABLE ciclo_vida_activos.inspecciones_activos
    ADD CONSTRAINT inspecciones_activos_fotografia_id_fkey FOREIGN KEY (fotografia_inspeccion_id)
    REFERENCES activos_digitales.archivos_digitales (id);

ALTER TABLE ciclo_vida_activos.inspecciones_activos
    ADD CONSTRAINT inspecciones_activos_aprobado_por_id_fkey FOREIGN KEY (aprobado_por_id)
    REFERENCES personal.perfiles_militares (id);