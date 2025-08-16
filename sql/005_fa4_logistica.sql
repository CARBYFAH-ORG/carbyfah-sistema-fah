-- =====================================================
-- CARBYFAH - SCHEMA FA-4 LOGÍSTICA 
-- ARCHIVO: 005_fa4_logistica.sql
-- ESTRUCTURA NORMALIZADA - SOLO DDL
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS fa4_logistica;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- 1. CLASES LOGÍSTICAS
-- =====================================================
CREATE TABLE fa4_logistica.clases_logisticas (
    id serial NOT NULL,
    codigo_clase varchar(20) NOT NULL,
    nombre_clase varchar(100) NOT NULL,
    descripcion text,
    icono_clase varchar(50),
    color_ui varchar(7),
    orden_clasificacion integer NOT NULL,
    requiere_autorizacion_especial boolean DEFAULT false,
    nivel_seguridad_minimo integer DEFAULT 1,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT clases_logisticas_pkey PRIMARY KEY (id),
    CONSTRAINT clases_logisticas_codigo_key UNIQUE (codigo_clase)
);

COMMENT ON TABLE fa4_logistica.clases_logisticas IS 'Catálogo de las 11 clases logísticas + Misceláneas según estándar militar';

-- =====================================================
-- 2. TIPOS DE ALMACÉN FA-4
-- =====================================================
CREATE TABLE fa4_logistica.tipos_almacen_fa4 (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    requiere_temperatura_controlada boolean DEFAULT false,
    requiere_humedad_controlada boolean DEFAULT false,
    requiere_autorizacion_especial boolean DEFAULT false,
    capacidad_maxima_default numeric(10,2),
    unidad_medida_capacidad varchar(20),
    nivel_seguridad_requerido integer DEFAULT 1,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_almacen_fa4_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_almacen_fa4_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE fa4_logistica.tipos_almacen_fa4 IS 'Tipos de almacén específicos para FA-4';

-- =====================================================
-- 3. ESTADOS DE TRANSFERENCIA
-- =====================================================
CREATE TABLE fa4_logistica.estados_transferencia (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    es_estado_inicial boolean DEFAULT false,
    es_estado_final boolean DEFAULT false,
    requiere_autorizacion boolean DEFAULT false,
    permite_modificacion boolean DEFAULT true,
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
    CONSTRAINT estados_transferencia_pkey PRIMARY KEY (id),
    CONSTRAINT estados_transferencia_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE fa4_logistica.estados_transferencia IS 'Estados del flujo de transferencias logísticas';

-- =====================================================
-- 4. TIPOS DE MANTENIMIENTO
-- =====================================================
CREATE TABLE fa4_logistica.tipos_mantenimiento (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    es_preventivo boolean DEFAULT true,
    es_correctivo boolean DEFAULT false,
    requiere_autorizacion boolean DEFAULT false,
    duracion_estimada_horas integer,
    costo_promedio numeric(10,2),
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

COMMENT ON TABLE fa4_logistica.tipos_mantenimiento IS 'Tipos de mantenimiento para recursos logísticos';

-- =====================================================
-- 5. PROVEEDORES
-- =====================================================
CREATE TABLE fa4_logistica.proveedores (
    id serial NOT NULL,
    codigo_proveedor varchar(30) NOT NULL,
    nombre_proveedor varchar(200) NOT NULL,
    nombre_comercial varchar(200),
    tipo_proveedor varchar(50), -- 'NACIONAL', 'INTERNACIONAL', 'FMS', 'MAP'
    rtn varchar(20),
    telefono varchar(20),
    email varchar(200),
    direccion text,
    pais varchar(100),
    contacto_principal varchar(200),
    telefono_contacto varchar(20),
    email_contacto varchar(200),
    tiempo_entrega_promedio_dias integer DEFAULT 30,
    condiciones_pago varchar(100),
    moneda_transaccion varchar(10),
    calificacion_proveedor integer, -- 1-5
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,  
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT proveedores_pkey PRIMARY KEY (id),
    CONSTRAINT proveedores_codigo_key UNIQUE (codigo_proveedor)
);

COMMENT ON TABLE fa4_logistica.proveedores IS 'Catálogo de proveedores nacionales e internacionales';

-- =====================================================
-- 6. TIPOS DE COMBUSTIBLE FA-4
-- ===================================================== 
CREATE TABLE fa4_logistica.tipos_combustible_fa4 (
    id serial NOT NULL,
    codigo_combustible varchar(20) NOT NULL,
    nombre_combustible varchar(100) NOT NULL,
    descripcion text,
    tipo_vehiculo_aplicable varchar(50), -- 'AERONAVE', 'VEHICULO_TERRESTRE', 'GENERADOR'
    octanaje numeric(4,1),
    densidad_kg_litro numeric(5,3),
    punto_congelacion_celsius numeric(4,1),
    contenido_energetico_mj_kg numeric(6,2),
    es_renovable boolean DEFAULT false,
    requiere_aditivos boolean DEFAULT false,
    costo_promedio_litro numeric(8,2),
    color_combustible varchar(50),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_combustible_fa4_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_combustible_fa4_codigo_key UNIQUE (codigo_combustible)
);

COMMENT ON TABLE fa4_logistica.tipos_combustible_fa4 IS 'Tipos de combustible específicos para recursos FAH';

-- =====================================================
-- 7. NIVELES AUTORIZACIÓN TRANSFERENCIAS
-- =====================================================
CREATE TABLE fa4_logistica.niveles_autorizacion (
    id serial NOT NULL,
    codigo_nivel varchar(20) NOT NULL,
    nombre_nivel varchar(100) NOT NULL,
    descripcion text,
    valor_maximo_autorizado numeric(15,2),
    requiere_firma_digital boolean DEFAULT false,
    tiempo_maximo_autorizacion_horas integer DEFAULT 24,
    es_autoridad_maxima boolean DEFAULT false,
    orden_jerarquico integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_autorizacion_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_autorizacion_codigo_key UNIQUE (codigo_nivel)
);

COMMENT ON TABLE fa4_logistica.niveles_autorizacion IS 'Niveles de autorización para transferencias según jerarquía militar';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 8. ALMACENES FA-4 (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.almacenes_fa4 (
    id serial NOT NULL,
    codigo_almacen varchar(30) NOT NULL,
    nombre_almacen varchar(200) NOT NULL,
    tipo_almacen_id integer NOT NULL,
    clase_logistica_principal_id integer,
    estructura_militar_id integer NOT NULL,
    ubicacion_geografica_id integer,
    encargado_id integer NOT NULL,
    supervisor_id integer,
    capacidad_maxima_m3 numeric(10,2),
    capacidad_actual_m3 numeric(10,2) DEFAULT 0,
    temperatura_minima_celsius numeric(4,1),
    temperatura_maxima_celsius numeric(4,1),
    humedad_maxima_porcentaje numeric(5,2),
    horario_funcionamiento varchar(100),
    telefono_contacto varchar(20),
    coordenadas_gps varchar(100),
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT almacenes_fa4_pkey PRIMARY KEY (id),
    CONSTRAINT almacenes_fa4_codigo_key UNIQUE (codigo_almacen)
);

COMMENT ON TABLE fa4_logistica.almacenes_fa4 IS 'Almacenes físicos FA-4 con control ambiental';

-- =====================================================
-- 9. INVENTARIO POR CLASE LOGÍSTICA (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.inventario_clases (
    id serial NOT NULL,
    clase_logistica_id integer NOT NULL,
    codigo_item varchar(50) NOT NULL,
    nombre_item varchar(200) NOT NULL,
    descripcion_detallada text,
    fabricante varchar(200),
    numero_parte varchar(100),
    numero_nsn varchar(50),
    almacen_id integer NOT NULL,
    cantidad_actual integer DEFAULT 0,
    cantidad_minima integer DEFAULT 5,
    cantidad_maxima integer DEFAULT 100,
    punto_reorden integer,
    costo_unitario numeric(12,2),
    proveedor_principal_id integer,
    tiempo_entrega_dias integer DEFAULT 30,
    fecha_ultimo_ingreso date,
    fecha_vencimiento date,
    requiere_refrigeracion boolean DEFAULT false,
    temperatura_almacenamiento_min numeric(4,1),
    temperatura_almacenamiento_max numeric(4,1),
    vida_util_meses integer,
    estado_inventario varchar(20) DEFAULT 'DISPONIBLE',
    ubicacion_especifica varchar(100),
    observaciones text,
    mongodb_specs_id varchar(24),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT inventario_clases_pkey PRIMARY KEY (id),
    CONSTRAINT inventario_clases_codigo_key UNIQUE (codigo_item)
);

COMMENT ON TABLE fa4_logistica.inventario_clases IS 'Inventario específico por clase logística';

-- =====================================================
-- 10. TRANSFERENCIAS LOGÍSTICAS (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.transferencias_logisticas (
    id serial NOT NULL,
    codigo_transferencia varchar(30) NOT NULL,
    estructura_origen_id integer NOT NULL,
    estructura_destino_id integer NOT NULL,
    solicitante_id integer NOT NULL,
    autorizador_id integer,
    nivel_autorizacion_id integer NOT NULL,
    estado_transferencia_id integer NOT NULL,
    tipo_transferencia varchar(50), -- 'NORMAL', 'URGENTE', 'EMERGENCIA'
    prioridad_transferencia integer DEFAULT 3,
    fecha_solicitud date NOT NULL DEFAULT CURRENT_DATE,
    fecha_autorizacion date,
    fecha_envio date,
    fecha_recepcion date,
    costo_total_transferencia numeric(12,2),
    vehiculo_transporte varchar(50),
    conductor_id integer,
    observaciones_solicitud text,
    observaciones_autorizacion text,
    observaciones_envio text,
    observaciones_recepcion text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT transferencias_logisticas_pkey PRIMARY KEY (id),
    CONSTRAINT transferencias_logisticas_codigo_key UNIQUE (codigo_transferencia)
);

COMMENT ON TABLE fa4_logistica.transferencias_logisticas IS 'Transferencias de recursos entre unidades militares';

-- =====================================================
-- 11. DETALLE TRANSFERENCIAS (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.detalle_transferencias (
    id serial NOT NULL,
    transferencia_id integer NOT NULL,
    inventario_item_id integer NOT NULL,
    cantidad_solicitada integer NOT NULL,
    cantidad_autorizada integer,
    cantidad_enviada integer,
    cantidad_recibida integer,
    costo_unitario numeric(10,2),
    costo_total_item numeric(12,2),
    estado_item varchar(20) DEFAULT 'SOLICITADO',
    observaciones_item text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT detalle_transferencias_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa4_logistica.detalle_transferencias IS 'Detalle específico de items en cada transferencia';

-- =====================================================
-- 12. MANTENIMIENTO PROGRAMADO (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.mantenimiento_programado (
    id serial NOT NULL,
    tipo_mantenimiento_id integer NOT NULL,
    recurso_tipo varchar(20) NOT NULL, -- 'AERONAVE', 'VEHICULO', 'ARMAMENTO'
    recurso_id integer NOT NULL,
    estructura_responsable_id integer NOT NULL,
    tecnico_asignado_id integer,
    fecha_programada date NOT NULL,
    fecha_inicio timestamp with time zone,
    fecha_finalizacion timestamp with time zone,
    horas_estimadas numeric(6,2),
    horas_reales numeric(6,2),
    costo_estimado numeric(10,2),
    costo_real numeric(10,2),
    estado_mantenimiento varchar(20) DEFAULT 'PROGRAMADO',
    descripcion_trabajo text,
    repuestos_utilizados text,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT mantenimiento_programado_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa4_logistica.mantenimiento_programado IS 'Mantenimiento programado para todos los recursos FA-4';

-- =====================================================
-- 13. CANIBALIZACIÓN REPUESTOS (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.canibalizacion_repuestos (
    id serial NOT NULL,
    recurso_origen_tipo varchar(20) NOT NULL, -- 'AERONAVE', 'VEHICULO'
    recurso_origen_id integer NOT NULL,
    recurso_destino_tipo varchar(20) NOT NULL,
    recurso_destino_id integer NOT NULL,
    inventario_item_id integer NOT NULL,
    autorizado_por_id integer NOT NULL,
    tecnico_extractor_id integer NOT NULL,
    tecnico_instalador_id integer,
    fecha_extraccion date NOT NULL,
    fecha_instalacion date,
    razon_canibalizacion text NOT NULL,
    estado_repuesto_extraido varchar(20),
    funcionamiento_post_instalacion varchar(20),
    costo_mano_obra numeric(8,2),
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT canibalizacion_repuestos_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa4_logistica.canibalizacion_repuestos IS 'Registro de canibalización de repuestos entre recursos';

-- =====================================================
-- 14. ALERTAS INVENTARIO (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.alertas_inventario (
    id serial NOT NULL,
    inventario_item_id integer NOT NULL,
    tipo_alerta varchar(50) NOT NULL, -- 'STOCK_BAJO', 'VENCIMIENTO', 'TEMPERATURA'
    nivel_criticidad integer NOT NULL, -- 1-5
    fecha_alerta timestamp with time zone NOT NULL DEFAULT now(),
    fecha_resolucion timestamp with time zone,
    responsable_id integer,
    estado_alerta varchar(20) DEFAULT 'ACTIVA',
    mensaje_alerta text NOT NULL,
    accion_requerida text,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT alertas_inventario_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa4_logistica.alertas_inventario IS 'Sistema de alertas automáticas para inventario FA-4';

-- =====================================================
-- 15. MÉTRICAS TIEMPO REAL FA-4 (Dependiente)
-- =====================================================
CREATE TABLE fa4_logistica.metricas_tiempo_real_fa4 (
    id serial NOT NULL,
    timestamp_registro timestamp with time zone DEFAULT now(),
    total_aeronaves_operativas integer DEFAULT 0,
    total_vehiculos_operativos integer DEFAULT 0,
    total_armamento_operativo integer DEFAULT 0,
    porcentaje_apresto_general numeric(5,2) DEFAULT 0,
    alertas_stock_bajo integer DEFAULT 0,
    transferencias_pendientes integer DEFAULT 0,
    transferencias_en_transito integer DEFAULT 0,
    mantenimientos_programados_hoy integer DEFAULT 0,
    costo_total_inventario numeric(15,2) DEFAULT 0,
    combustible_disponible_litros numeric(12,2) DEFAULT 0,
    items_proximos_vencer integer DEFAULT 0,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT metricas_tiempo_real_fa4_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa4_logistica.metricas_tiempo_real_fa4 IS 'Métricas en tiempo real para dashboard COFAH FA-4';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Almacenes FA-4
ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_tipo_almacen_id_fkey FOREIGN KEY (tipo_almacen_id)
    REFERENCES fa4_logistica.tipos_almacen_fa4 (id);

ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_clase_logistica_id_fkey FOREIGN KEY (clase_logistica_principal_id)
    REFERENCES fa4_logistica.clases_logisticas (id);

ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_estructura_militar_id_fkey FOREIGN KEY (estructura_militar_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_ubicacion_geografica_id_fkey FOREIGN KEY (ubicacion_geografica_id)
    REFERENCES organizacion.ubicaciones_geograficas (id);

ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_encargado_id_fkey FOREIGN KEY (encargado_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa4_logistica.almacenes_fa4
    ADD CONSTRAINT almacenes_fa4_supervisor_id_fkey FOREIGN KEY (supervisor_id)
    REFERENCES personal.perfiles_militares (id);

-- Inventario Clases
ALTER TABLE fa4_logistica.inventario_clases
    ADD CONSTRAINT inventario_clases_clase_logistica_id_fkey FOREIGN KEY (clase_logistica_id)
    REFERENCES fa4_logistica.clases_logisticas (id);

ALTER TABLE fa4_logistica.inventario_clases
    ADD CONSTRAINT inventario_clases_almacen_id_fkey FOREIGN KEY (almacen_id)
    REFERENCES fa4_logistica.almacenes_fa4 (id);

ALTER TABLE fa4_logistica.inventario_clases
    ADD CONSTRAINT inventario_clases_proveedor_id_fkey FOREIGN KEY (proveedor_principal_id)
    REFERENCES fa4_logistica.proveedores (id);

-- Transferencias Logísticas  
ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_estructura_origen_id_fkey FOREIGN KEY (estructura_origen_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_estructura_destino_id_fkey FOREIGN KEY (estructura_destino_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_solicitante_id_fkey FOREIGN KEY (solicitante_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_autorizador_id_fkey FOREIGN KEY (autorizador_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_nivel_autorizacion_id_fkey FOREIGN KEY (nivel_autorizacion_id)
    REFERENCES fa4_logistica.niveles_autorizacion (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_estado_id_fkey FOREIGN KEY (estado_transferencia_id)
    REFERENCES fa4_logistica.estados_transferencia (id);

ALTER TABLE fa4_logistica.transferencias_logisticas
    ADD CONSTRAINT transferencias_conductor_id_fkey FOREIGN KEY (conductor_id)
    REFERENCES personal.perfiles_militares (id);

-- Detalle Transferencias
ALTER TABLE fa4_logistica.detalle_transferencias
    ADD CONSTRAINT detalle_transferencias_transferencia_id_fkey FOREIGN KEY (transferencia_id)
    REFERENCES fa4_logistica.transferencias_logisticas (id);

ALTER TABLE fa4_logistica.detalle_transferencias
    ADD CONSTRAINT detalle_transferencias_inventario_id_fkey FOREIGN KEY (inventario_item_id)
    REFERENCES fa4_logistica.inventario_clases (id);

-- Mantenimiento Programado
ALTER TABLE fa4_logistica.mantenimiento_programado
    ADD CONSTRAINT mantenimiento_tipo_id_fkey FOREIGN KEY (tipo_mantenimiento_id)
    REFERENCES fa4_logistica.tipos_mantenimiento (id);

ALTER TABLE fa4_logistica.mantenimiento_programado
    ADD CONSTRAINT mantenimiento_estructura_id_fkey FOREIGN KEY (estructura_responsable_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa4_logistica.mantenimiento_programado
    ADD CONSTRAINT mantenimiento_tecnico_id_fkey FOREIGN KEY (tecnico_asignado_id)
    REFERENCES personal.perfiles_militares (id);

-- Canibalización Repuestos
ALTER TABLE fa4_logistica.canibalizacion_repuestos
    ADD CONSTRAINT canibalizacion_inventario_id_fkey FOREIGN KEY (inventario_item_id)
    REFERENCES fa4_logistica.inventario_clases (id);

ALTER TABLE fa4_logistica.canibalizacion_repuestos
    ADD CONSTRAINT canibalizacion_autorizado_por_id_fkey FOREIGN KEY (autorizado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa4_logistica.canibalizacion_repuestos
    ADD CONSTRAINT canibalizacion_extractor_id_fkey FOREIGN KEY (tecnico_extractor_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa4_logistica.canibalizacion_repuestos
    ADD CONSTRAINT canibalizacion_instalador_id_fkey FOREIGN KEY (tecnico_instalador_id)
    REFERENCES personal.perfiles_militares (id);

-- Alertas Inventario
ALTER TABLE fa4_logistica.alertas_inventario
    ADD CONSTRAINT alertas_inventario_item_id_fkey FOREIGN KEY (inventario_item_id)
    REFERENCES fa4_logistica.inventario_clases (id);

ALTER TABLE fa4_logistica.alertas_inventario
    ADD CONSTRAINT alertas_responsable_id_fkey FOREIGN KEY (responsable_id)
    REFERENCES personal.perfiles_militares (id);