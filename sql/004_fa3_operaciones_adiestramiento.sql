-- =====================================================
-- CARBYFAH - SCHEMA FA-3 OPERACIONES & ADIESTRAMIENTO
-- ARCHIVO: 004_fa3_operaciones_adiestramiento.sql
-- INTEGRACIÓN CON SCHEMAS EXISTENTES
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS fa3_operaciones;

-- =====================================================
-- SECCIÓN 1: ORGANIZACIÓN (Sub sección del FA-3)
-- =====================================================

-- 1.1 Estructuras Organizativas FA-3
CREATE TABLE fa3_operaciones.estructuras_organizativas (
    id serial NOT NULL,
    codigo_estructura varchar(20) NOT NULL,
    nombre_estructura varchar(100) NOT NULL,
    nivel_aplicacion varchar(20) NOT NULL, -- 'C-3', 'FA-3', 'S-3'
    unidad_responsable_id integer, -- FK a organizacion.estructura_militar
    tipo_estructura varchar(50),
    descripcion text,
    vigencia_inicio date DEFAULT CURRENT_DATE,
    vigencia_fin date,
    autorizada_por_id integer, -- FK a personal.perfiles_militares
    documento_autorizacion_id integer, -- FK a digital_assets.archivos_digitales
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estructuras_organizativas_pkey PRIMARY KEY (id),
    CONSTRAINT estructuras_organizativas_codigo_key UNIQUE (codigo_estructura)
);

-- 1.2 Tabla de Organización y Equipo (TOE)
CREATE TABLE fa3_operaciones.toe_configuraciones (
    id serial NOT NULL,
    codigo_toe varchar(20) NOT NULL,
    nombre_configuracion varchar(100) NOT NULL,
    estructura_organizativa_id integer NOT NULL,
    categoria_personal_id integer NOT NULL, -- FK a catalogos.categorias_personal
    grado_requerido_id integer, -- FK a catalogos.grados
    especialidad_requerida_id integer, -- FK a catalogos.especialidades
    cantidad_autorizada integer NOT NULL,
    cantidad_actual integer DEFAULT 0,
    porcentaje_ocupacion numeric(5,2) GENERATED ALWAYS AS (
        CASE WHEN cantidad_autorizada > 0 
        THEN (cantidad_actual * 100.0 / cantidad_autorizada) 
        ELSE 0 END
    ) STORED,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT toe_configuraciones_pkey PRIMARY KEY (id),
    CONSTRAINT toe_configuraciones_codigo_key UNIQUE (codigo_toe)
);

-- =====================================================
-- SECCIÓN 2: OPERACIONES (Integrado con schema operations)
-- =====================================================

-- 2.1 Coordinación Multinivel (C-3, FA-3, S-3)
CREATE TABLE fa3_operaciones.coordinacion_multinivel (
    id serial NOT NULL,
    mision_id integer NOT NULL, -- FK a operations.misiones_operacionales
    nivel_origen varchar(10) NOT NULL, -- 'C-3', 'FA-3', 'S-3'
    nivel_destino varchar(10) NOT NULL,
    orden_superior_id integer, -- Auto-referencia para cadena órdenes
    comandante_firmante_id integer NOT NULL, -- FK a personal.perfiles_militares
    fecha_orden timestamp with time zone NOT NULL DEFAULT now(),
    documento_orden_id integer, -- FK a document_automation.documentos_generados
    estado_ejecucion varchar(20) DEFAULT 'PENDIENTE',
    fecha_ejecucion timestamp with time zone,
    observaciones_ejecucion text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT coordinacion_multinivel_pkey PRIMARY KEY (id)
);

-- 2.2 Personal en Misión (Para Sala de Crisis)
CREATE TABLE fa3_operaciones.personal_mision (
    id serial NOT NULL,
    mision_id integer NOT NULL, -- FK a operations.misiones_operacionales
    perfil_militar_id integer NOT NULL, -- FK a personal.perfiles_militares
    rol_en_mision varchar(50) NOT NULL, -- 'COMANDANTE', 'CONDUCTOR', 'ESCOLTA', etc.
    fecha_asignacion timestamp with time zone DEFAULT now(),
    fecha_despliegue timestamp with time zone,
    fecha_regreso_programada timestamp with time zone,
    fecha_regreso_real timestamp with time zone,
    situacion_actual varchar(20) DEFAULT 'EN_MISION', -- 'DISPONIBLE', 'EN_MISION', 'COMPROMETIDO'
    ubicacion_actual varchar(500),
    coordenadas_gps point,
    ultimo_reporte timestamp with time zone,
    estado_salud varchar(20) DEFAULT 'NORMAL',
    pulso_cardiaco integer, -- Para futuros chalecos inteligentes
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT personal_mision_pkey PRIMARY KEY (id)
);

-- 2.3 Armas Asignadas a Misión
CREATE TABLE fa3_operaciones.armas_mision (
    id serial NOT NULL,
    mision_id integer NOT NULL, -- FK a operations.misiones_operacionales
    personal_portador_id integer NOT NULL, -- FK a fa3_operaciones.personal_mision
    codigo_arma varchar(50) NOT NULL,
    tipo_arma varchar(50),
    serie_arma varchar(50),
    cantidad_municion integer DEFAULT 0,
    fecha_asignacion timestamp with time zone DEFAULT now(),
    fecha_devolucion timestamp with time zone,
    estado_arma varchar(20) DEFAULT 'ASIGNADA',
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT armas_mision_pkey PRIMARY KEY (id)
);

-- 2.4 Vehículos en Misión
CREATE TABLE fa3_operaciones.vehiculos_mision (
    id serial NOT NULL,
    mision_id integer NOT NULL, -- FK a operations.misiones_operacionales
    codigo_vehiculo varchar(50) NOT NULL,
    tipo_vehiculo varchar(50),
    placa_rha varchar(20),
    conductor_id integer NOT NULL, -- FK a fa3_operaciones.personal_mision
    kilometraje_inicial integer,
    kilometraje_final integer,
    combustible_inicial numeric(8,2),
    combustible_final numeric(8,2),
    fecha_asignacion timestamp with time zone DEFAULT now(),
    fecha_regreso timestamp with time zone,
    estado_vehiculo varchar(20) DEFAULT 'EN_MISION',
    ubicacion_actual varchar(500),
    coordenadas_gps point,
    fallas_reportadas text,
    mantenimiento_requerido boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT vehiculos_mision_pkey PRIMARY KEY (id)
);

-- =====================================================
-- SECCIÓN 3: ADIESTRAMIENTO
-- =====================================================

-- 3.1 Tipos de Adiestramiento
CREATE TABLE fa3_operaciones.tipos_adiestramiento (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    categoria varchar(50), -- 'NACIONAL', 'INTERNACIONAL', 'ESPECIALIZADO'
    nivel_requerido varchar(50), -- 'BASICO', 'INTERMEDIO', 'AVANZADO'
    duracion_horas integer,
    costo_estimado numeric(10,2),
    requiere_especializacion boolean DEFAULT false,
    vigencia_certificacion_meses integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_adiestramiento_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_adiestramiento_codigo_key UNIQUE (codigo_tipo)
);

-- 3.2 Cursos de Adiestramiento
CREATE TABLE fa3_operaciones.cursos_adiestramiento (
    id serial NOT NULL,
    codigo_curso varchar(30) NOT NULL,
    nombre_curso varchar(200) NOT NULL,
    tipo_adiestramiento_id integer NOT NULL,
    institucion_proveedora varchar(200),
    pais_realizacion varchar(100),
    fecha_inicio date,
    fecha_fin date,
    cupos_disponibles integer,
    cupos_ocupados integer DEFAULT 0,
    costo_total numeric(12,2),
    responsable_coordinacion_id integer, -- FK a personal.perfiles_militares
    requisitos_ingreso text,
    beneficios_obtenidos text,
    certificacion_otorgada varchar(200),
    estado_curso varchar(20) DEFAULT 'PLANIFICADO',
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT cursos_adiestramiento_pkey PRIMARY KEY (id),
    CONSTRAINT cursos_adiestramiento_codigo_key UNIQUE (codigo_curso)
);

-- 3.3 Participantes en Cursos
CREATE TABLE fa3_operaciones.participantes_curso (
    id serial NOT NULL,
    curso_id integer NOT NULL,
    perfil_militar_id integer NOT NULL, -- FK a personal.perfiles_militares
    fecha_aplicacion date,
    puntaje_evaluacion_ingles numeric(5,2),
    puntaje_evaluacion_fisica numeric(5,2),
    puntaje_evaluacion_tecnica numeric(5,2),
    puntaje_total numeric(5,2),
    estado_aplicacion varchar(20) DEFAULT 'APLICADO',
    seleccionado boolean DEFAULT false,
    motivo_seleccion text,
    fecha_viaje date,
    fecha_regreso date,
    calificacion_final varchar(20),
    certificado_obtenido_id integer, -- FK a digital_assets.archivos_digitales
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT participantes_curso_pkey PRIMARY KEY (id)
);

-- =====================================================
-- SECCIÓN 4: SALA DE CRISIS - DASHBOARDS
-- =====================================================

-- 4.1 Configuración Pantallas Crisis
CREATE TABLE fa3_operaciones.pantallas_crisis (
    id serial NOT NULL,
    codigo_pantalla varchar(20) NOT NULL,
    nombre_pantalla varchar(100) NOT NULL,
    departamento_responsable varchar(10), -- 'FA-1', 'FA-2', 'FA-3', etc.
    tipo_datos varchar(50), -- 'MISIONES', 'PERSONAL', 'RECURSOS'
    configuracion_json jsonb, -- Configuración específica pantalla
    tiempo_actualizacion_segundos integer DEFAULT 30,
    es_pantalla_principal boolean DEFAULT false,
    orden_display integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT pantallas_crisis_pkey PRIMARY KEY (id),
    CONSTRAINT pantallas_crisis_codigo_key UNIQUE (codigo_pantalla)
);

-- 4.2 Métricas Tiempo Real FA-3
CREATE TABLE fa3_operaciones.metricas_tiempo_real (
    id serial NOT NULL,
    timestamp_registro timestamp with time zone DEFAULT now(),
    total_misiones_activas integer DEFAULT 0,
    total_personal_desplegado integer DEFAULT 0,
    total_vehiculos_mision integer DEFAULT 0,
    total_aeronaves_operativas integer DEFAULT 0,
    porcentaje_apresto_operacional numeric(5,2) DEFAULT 0,
    alertas_activas integer DEFAULT 0,
    personal_comprometido integer DEFAULT 0,
    mision_mayor_prioridad varchar(100),
    tiempo_promedio_respuesta_minutos integer,
    combustible_disponible_porcentaje numeric(5,2),
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT metricas_tiempo_real_pkey PRIMARY KEY (id)
);

-- =====================================================
-- FOREIGN KEYS
-- =====================================================

-- Estructuras Organizativas
ALTER TABLE fa3_operaciones.estructuras_organizativas
    ADD CONSTRAINT estructuras_organizativas_unidad_responsable_id_fkey 
    FOREIGN KEY (unidad_responsable_id) REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa3_operaciones.estructuras_organizativas
    ADD CONSTRAINT estructuras_organizativas_autorizada_por_id_fkey 
    FOREIGN KEY (autorizada_por_id) REFERENCES personal.perfiles_militares (id);

-- TOE Configuraciones
ALTER TABLE fa3_operaciones.toe_configuraciones
    ADD CONSTRAINT toe_configuraciones_estructura_organizativa_id_fkey 
    FOREIGN KEY (estructura_organizativa_id) REFERENCES fa3_operaciones.estructuras_organizativas (id);

ALTER TABLE fa3_operaciones.toe_configuraciones
    ADD CONSTRAINT toe_configuraciones_categoria_personal_id_fkey 
    FOREIGN KEY (categoria_personal_id) REFERENCES catalogos.categorias_personal (id);

-- Coordinación Multinivel
ALTER TABLE fa3_operaciones.coordinacion_multinivel
    ADD CONSTRAINT coordinacion_multinivel_mision_id_fkey 
    FOREIGN KEY (mision_id) REFERENCES operations.misiones_operacionales (id);

ALTER TABLE fa3_operaciones.coordinacion_multinivel
    ADD CONSTRAINT coordinacion_multinivel_comandante_firmante_id_fkey 
    FOREIGN KEY (comandante_firmante_id) REFERENCES personal.perfiles_militares (id);

-- Personal en Misión
ALTER TABLE fa3_operaciones.personal_mision
    ADD CONSTRAINT personal_mision_mision_id_fkey 
    FOREIGN KEY (mision_id) REFERENCES operations.misiones_operacionales (id);

ALTER TABLE fa3_operaciones.personal_mision
    ADD CONSTRAINT personal_mision_perfil_militar_id_fkey 
    FOREIGN KEY (perfil_militar_id) REFERENCES personal.perfiles_militares (id);

-- Armas Misión
ALTER TABLE fa3_operaciones.armas_mision
    ADD CONSTRAINT armas_mision_mision_id_fkey 
    FOREIGN KEY (mision_id) REFERENCES operations.misiones_operacionales (id);

ALTER TABLE fa3_operaciones.armas_mision
    ADD CONSTRAINT armas_mision_personal_portador_id_fkey 
    FOREIGN KEY (personal_portador_id) REFERENCES fa3_operaciones.personal_mision (id);

-- Vehículos Misión
ALTER TABLE fa3_operaciones.vehiculos_mision
    ADD CONSTRAINT vehiculos_mision_mision_id_fkey 
    FOREIGN KEY (mision_id) REFERENCES operations.misiones_operacionales (id);

ALTER TABLE fa3_operaciones.vehiculos_mision
    ADD CONSTRAINT vehiculos_mision_conductor_id_fkey 
    FOREIGN KEY (conductor_id) REFERENCES fa3_operaciones.personal_mision (id);

-- Cursos Adiestramiento
ALTER TABLE fa3_operaciones.cursos_adiestramiento
    ADD CONSTRAINT cursos_adiestramiento_tipo_adiestramiento_id_fkey 
    FOREIGN KEY (tipo_adiestramiento_id) REFERENCES fa3_operaciones.tipos_adiestramiento (id);

-- Participantes Curso
ALTER TABLE fa3_operaciones.participantes_curso
    ADD CONSTRAINT participantes_curso_curso_id_fkey 
    FOREIGN KEY (curso_id) REFERENCES fa3_operaciones.cursos_adiestramiento (id);

ALTER TABLE fa3_operaciones.participantes_curso
    ADD CONSTRAINT participantes_curso_perfil_militar_id_fkey 
    FOREIGN KEY (perfil_militar_id) REFERENCES personal.perfiles_militares (id);

-- =====================================================
-- ÍNDICES PARA PERFORMANCE
-- =====================================================

CREATE INDEX idx_personal_mision_situacion ON fa3_operaciones.personal_mision(situacion_actual);
CREATE INDEX idx_personal_mision_coordenadas ON fa3_operaciones.personal_mision USING gist(coordenadas_gps);
CREATE INDEX idx_vehiculos_mision_estado ON fa3_operaciones.vehiculos_mision(estado_vehiculo);
CREATE INDEX idx_vehiculos_mision_coordenadas ON fa3_operaciones.vehiculos_mision USING gist(coordenadas_gps);
CREATE INDEX idx_metricas_tiempo_real_timestamp ON fa3_operaciones.metricas_tiempo_real(timestamp_registro DESC);

-- =====================================================
-- VISTAS PARA SALA DE CRISIS
-- =====================================================

-- Vista para Dashboard Sala de Crisis FA-3
CREATE VIEW fa3_operaciones.vista_crisis_fa3 AS
SELECT 
    m.id as mision_id,
    m.codigo_mision,
    m.nombre_mision,
    m.coordenadas_gps,
    em.nombre_estado as estado_mision,
    COUNT(pm.id) as total_personal,
    COUNT(vm.id) as total_vehiculos,
    COUNT(am.id) as total_armas,
    pm_comandante.perfil_militar_id as comandante_id,
    dp.primer_nombre || ' ' || dp.primer_apellido as comandante_nombre,
    g.nombre_grado as comandante_grado,
    MAX(pm.ultimo_reporte) as ultimo_reporte
FROM operations.misiones_operacionales m
LEFT JOIN operations.estados_mision em ON m.estado_mision_id = em.id
LEFT JOIN fa3_operaciones.personal_mision pm ON m.id = pm.mision_id
LEFT JOIN fa3_operaciones.vehiculos_mision vm ON m.id = vm.mision_id  
LEFT JOIN fa3_operaciones.armas_mision am ON m.id = am.mision_id
LEFT JOIN fa3_operaciones.personal_mision pm_comandante ON m.id = pm_comandante.mision_id AND pm_comandante.rol_en_mision = 'COMANDANTE'
LEFT JOIN personal.perfiles_militares pml ON pm_comandante.perfil_militar_id = pml.id
LEFT JOIN personal.datos_personales dp ON pml.datos_personales_id = dp.id
LEFT JOIN catalogos.grados g ON pml.grado_actual_id = g.id
WHERE m.is_active = true AND em.es_estado_final = false
GROUP BY m.id, m.codigo_mision, m.nombre_mision, m.coordenadas_gps, em.nombre_estado, 
         pm_comandante.perfil_militar_id, dp.primer_nombre, dp.primer_apellido, g.nombre_grado;