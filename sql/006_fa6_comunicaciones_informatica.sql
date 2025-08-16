-- =====================================================
-- CARBYFAH - SCHEMA FA-6 COMUNICACIONES E INFORMÁTICA
-- ARCHIVO: 006_fa6_comunicaciones_informatica.sql
-- CONSOLIDACIÓN: SECCIÓN COMUNICACIONES + SECCIÓN INFORMÁTICA
-- ESTRUCTURA NORMALIZADA - SOLO DDL
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS fa6_comunicaciones_informatica;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- =====================================================

-- =====================================================
-- SECCIÓN 1: CATÁLOGOS COMUNICACIONES
-- =====================================================

-- 1.1 Tipos de Equipo de Comunicación
CREATE TABLE fa6_comunicaciones_informatica.tipos_equipo_comunicacion (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    categoria_principal varchar(50), -- 'RADIO_HF', 'RADIO_VHF', 'SATELITAL', 'TELEFONIA'
    rango_frecuencias varchar(100),
    alcance_maximo_km numeric(8,2),
    potencia_transmision_watts numeric(8,2),
    requiere_licencia_operacion boolean DEFAULT true,
    consume_energia_continua boolean DEFAULT true,
    consumo_energia_watts numeric(8,2),
    requiere_clima_controlado boolean DEFAULT false,
    vida_util_anos integer DEFAULT 15,
    costo_mantenimiento_anual numeric(10,2),
    modulacion_soportada varchar(100),
    canales_simultaneos integer,
    ancho_banda_khz numeric(8,3),
    sensibilidad_receptor_uv numeric(8,3),
    selectividad_db numeric(5,2),
    estabilidad_frecuencia_ppm numeric(6,3),
    rango_temperaturas varchar(50),
    humedad_relativa_max_porcentaje integer,
    altitud_operacion_max_metros integer,
    vibracion_resistencia_g numeric(4,2),
    tiempo_calentamiento_minutos integer,
    tiempo_sintonizacion_segundos integer,
    precision_frecuencia_hz integer,
    supresion_espurias_db numeric(5,2),
    consumo_standby_watts numeric(6,3),
    consumo_transmision_watts numeric(8,2),
    factor_forma varchar(50),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_equipo_comunicacion_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_equipo_comunicacion_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_equipo_comunicacion IS 'Tipos de equipos de comunicación FA-6';

-- 1.2 Estados de Equipos de Comunicación
CREATE TABLE fa6_comunicaciones_informatica.estados_equipo_comunicacion (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    permite_uso_operacional boolean DEFAULT false,
    requiere_intervencion_tecnica boolean DEFAULT false,
    tiempo_estimado_reparacion_horas integer,
    costo_estimado_reparacion numeric(10,2),
    color_estado varchar(7),
    icono_estado varchar(50),
    notifica_supervisor boolean DEFAULT false,
    requiere_justificacion boolean DEFAULT false,
    genera_alerta_automatica boolean DEFAULT false,
    requiere_reporte_incidente boolean DEFAULT false,
    permite_uso_emergencia boolean DEFAULT false,
    requiere_inspeccion_post_reparacion boolean DEFAULT true,
    dias_maximos_estado integer,
    requiere_autorizacion_cambio boolean DEFAULT false,
    impacto_mision varchar(20),
    procedimiento_escalamiento text,
    contacto_soporte_tecnico varchar(200),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_equipo_comunicacion_pkey PRIMARY KEY (id),
    CONSTRAINT estados_equipo_comunicacion_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.estados_equipo_comunicacion IS 'Estados operacionales de equipos de comunicación';

-- 1.3 Tipos de Red de Comunicación
CREATE TABLE fa6_comunicaciones_informatica.tipos_red_comunicacion (
    id serial NOT NULL,
    codigo_tipo_red varchar(20) NOT NULL,
    nombre_tipo_red varchar(100) NOT NULL,
    descripcion text,
    prioridad_operacional integer,
    nivel_seguridad_requerido integer,
    requiere_cifrado boolean DEFAULT false,
    alcance_tipico_km numeric(8,2),
    usuarios_simultaneos_max integer,
    tiempo_respuesta_max_segundos integer,
    disponibilidad_requerida_porcentaje numeric(5,2),
    costo_operacion_diario numeric(10,2),
    requiere_personal_especializado boolean DEFAULT true,
    protocolo_comunicacion_estandar varchar(100),
    interoperabilidad_requerida boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_red_comunicacion_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_red_comunicacion_codigo_key UNIQUE (codigo_tipo_red)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_red_comunicacion IS 'Tipos de redes de comunicación FA-6';

-- 1.4 Niveles de Cifrado
CREATE TABLE fa6_comunicaciones_informatica.niveles_cifrado (
    id serial NOT NULL,
    codigo_cifrado varchar(20) NOT NULL,
    nombre_cifrado varchar(100) NOT NULL,
    descripcion text,
    algoritmo_cifrado varchar(100),
    longitud_clave_bits integer,
    resistencia_criptoanalitica varchar(50),
    tiempo_cifrado_ms numeric(8,3),
    tiempo_descifrado_ms numeric(8,3),
    requiere_distribucion_claves boolean DEFAULT false,
    frecuencia_cambio_claves_dias integer,
    nivel_autorizacion_uso varchar(50),
    compatible_estandares_otan boolean DEFAULT false,
    resistencia_interferencia integer,
    sobrecarga_cpu_porcentaje numeric(5,2),
    requiere_hardware_especializado boolean DEFAULT false,
    costo_implementacion numeric(12,2),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_cifrado_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_cifrado_codigo_key UNIQUE (codigo_cifrado)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.niveles_cifrado IS 'Niveles de cifrado para comunicaciones seguras';

-- =====================================================
-- SECCIÓN 2: CATÁLOGOS INFORMÁTICA
-- =====================================================

-- 2.1 Tipos de Equipo Informático
CREATE TABLE fa6_comunicaciones_informatica.tipos_equipo_informatico (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    categoria_principal varchar(50), -- 'SERVIDOR', 'WORKSTATION', 'LAPTOP', 'ROUTER', 'SWITCH'
    consumo_energia_watts numeric(6,2),
    requiere_ups boolean DEFAULT false,
    requiere_ventilacion_especial boolean DEFAULT false,
    soporta_expansion boolean DEFAULT true,
    garantia_fabricante_meses integer DEFAULT 12,
    vida_util_estimada_anos integer DEFAULT 5,
    costo_promedio numeric(12,2),
    factor_forma varchar(50),
    conectividad_red varchar(100),
    capacidades_graficas varchar(100),
    nivel_ruido_db integer,
    temperatura_operacion_min integer,
    temperatura_operacion_max integer,
    humedad_operacion_max_porcentaje integer,
    certificaciones varchar(200),
    mongodb_especificaciones_tecnicas_id varchar(24),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_equipo_informatico_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_equipo_informatico_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_equipo_informatico IS 'Tipos de equipos informáticos FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.tipos_equipo_informatico.mongodb_especificaciones_tecnicas_id IS 'ObjectId MongoDB: especificaciones técnicas detalladas';

-- 2.2 Estados de Equipos Informáticos
CREATE TABLE fa6_comunicaciones_informatica.estados_equipo_informatico (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    color_estado varchar(7),
    permite_uso_operacional boolean DEFAULT false,
    requiere_intervencion_tecnica boolean DEFAULT false,
    afecta_productividad_usuario boolean DEFAULT true,
    tiempo_maximo_estado_horas integer,
    requiere_justificacion boolean DEFAULT false,
    genera_alerta_automatica boolean DEFAULT false,
    requiere_reporte_superior boolean DEFAULT false,
    permite_asignacion_usuario boolean DEFAULT false,
    cuenta_inventario_activo boolean DEFAULT true,
    requiere_backup_datos boolean DEFAULT false,
    protocolo_cambio_estado text,
    notificacion_automatica_usuarios boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_equipo_informatico_pkey PRIMARY KEY (id),
    CONSTRAINT estados_equipo_informatico_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.estados_equipo_informatico IS 'Estados operacionales de equipos informáticos';

-- 2.3 Tipos de Software
CREATE TABLE fa6_comunicaciones_informatica.tipos_software (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    descripcion text,
    requiere_licencia boolean DEFAULT true,
    tipo_instalacion varchar(50),
    requiere_internet boolean DEFAULT false,
    requiere_servidor_licencias boolean DEFAULT false,
    permite_instalacion_multiple boolean DEFAULT false,
    requiere_capacitacion_usuario boolean DEFAULT false,
    nivel_criticidad_operacional varchar(20),
    frecuencia_actualizacion varchar(50),
    costo_promedio_licencia numeric(10,2),
    soporte_tecnico_incluido boolean DEFAULT false,
    compatible_multiples_so boolean DEFAULT false,
    requiere_hardware_especifico boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_software_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_software_codigo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_software IS 'Tipos de software utilizados en FA-6';

-- 2.4 Tipos de Sistema Operativo
CREATE TABLE fa6_comunicaciones_informatica.tipos_sistema_operativo (
    id serial NOT NULL,
    codigo_so varchar(20) NOT NULL,
    nombre_so varchar(100) NOT NULL,
    descripcion text,
    fabricante varchar(100),
    version_actual varchar(50),
    tipo_licencia varchar(50),
    costo_licencia numeric(10,2),
    soporte_oficial_hasta date,
    requiere_activacion boolean DEFAULT true,
    requiere_antivirus boolean DEFAULT true,
    compatible_dominio boolean DEFAULT true,
    soporte_actualizaciones_automaticas boolean DEFAULT true,
    nivel_seguridad integer,
    facilidad_uso integer,
    recursos_minimos_ram_gb integer,
    recursos_minimos_disco_gb integer,
    compatible_hardware_legacy boolean DEFAULT true,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_sistema_operativo_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_sistema_operativo_codigo_key UNIQUE (codigo_so)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_sistema_operativo IS 'Sistemas operativos utilizados en FA-6';

-- 2.5 Tipos de Incidente
CREATE TABLE fa6_comunicaciones_informatica.tipos_incidente (
    id serial NOT NULL,
    codigo_incidente varchar(30) NOT NULL,
    nombre_incidente varchar(100) NOT NULL,
    descripcion text,
    categoria_incidente varchar(50), -- 'COMUNICACIONES', 'INFORMATICA', 'MIXTO'
    impacto_tipico varchar(20),
    tiempo_resolucion_promedio_horas numeric(5,2),
    requiere_tecnico_especializado boolean DEFAULT true,
    requiere_repuestos boolean DEFAULT false,
    puede_ser_remoto boolean DEFAULT false,
    requiere_respaldo_datos boolean DEFAULT false,
    frecuencia_ocurrencia varchar(20),
    costo_promedio_resolucion numeric(10,2),
    prevenir_con_mantenimiento boolean DEFAULT true,
    requiere_capacitacion_usuario boolean DEFAULT false,
    afecta_seguridad_datos boolean DEFAULT false,
    escalamiento_automatico_horas integer,
    protocolo_atencion text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_incidente_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_incidente_codigo_key UNIQUE (codigo_incidente)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.tipos_incidente IS 'Tipos de incidentes FA-6 (comunicaciones e informática)';

-- 2.6 Niveles de Severidad
CREATE TABLE fa6_comunicaciones_informatica.niveles_severidad (
    id serial NOT NULL,
    codigo_severidad varchar(20) NOT NULL,
    nombre_severidad varchar(100) NOT NULL,
    descripcion text,
    color_severidad varchar(7),
    tiempo_respuesta_max_minutos integer,
    nivel_autoridad_notificar varchar(50),
    requiere_notificacion_inmediata boolean DEFAULT false,
    requiere_reporte_incidente boolean DEFAULT false,
    afecta_operaciones_criticas boolean DEFAULT false,
    usuarios_afectados_tipico integer,
    tiempo_maximo_resolucion_horas integer,
    requiere_plan_contingencia boolean DEFAULT false,
    costo_maximo_autorizacion numeric(12,2),
    requiere_aprobacion_gasto boolean DEFAULT true,
    escalamiento_automatico_horas integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_severidad_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_severidad_codigo_key UNIQUE (codigo_severidad)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.niveles_severidad IS 'Niveles de severidad para incidentes FA-6';

-- 2.7 Estados de Incidente
CREATE TABLE fa6_comunicaciones_informatica.estados_incidente (
    id serial NOT NULL,
    codigo_estado varchar(20) NOT NULL,
    nombre_estado varchar(100) NOT NULL,
    descripcion text,
    color_estado varchar(7),
    requiere_accion_tecnica boolean DEFAULT true,
    permite_cierre boolean DEFAULT false,
    requiere_aprobacion_cierre boolean DEFAULT false,
    tiempo_maximo_estado_horas integer,
    escalamiento_automatico boolean DEFAULT false,
    requiere_validacion_usuario boolean DEFAULT false,
    genera_metricas_sla boolean DEFAULT true,
    notifica_cambio_estado boolean DEFAULT true,
    permite_reapertura boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estados_incidente_pkey PRIMARY KEY (id),
    CONSTRAINT estados_incidente_codigo_key UNIQUE (codigo_estado)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.estados_incidente IS 'Estados de incidentes FA-6';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- SECCIÓN 3: EQUIPOS DE COMUNICACIÓN (Dependiente)
-- =====================================================

-- 3.1 Equipos de Comunicación
CREATE TABLE fa6_comunicaciones_informatica.equipos_comunicacion (
    id serial NOT NULL,
    numero_serie varchar(100) NOT NULL,
    codigo_inventario varchar(50) NOT NULL,
    tipo_equipo_id integer NOT NULL,
    estado_equipo_id integer NOT NULL,
    marca_fabricante varchar(100) NOT NULL,
    modelo_equipo varchar(200) NOT NULL,
    ano_fabricacion integer,
    fecha_adquisicion date NOT NULL,
    costo_adquisicion numeric(10,2),
    ubicacion_actual_id integer NOT NULL,
    sala_ubicacion varchar(100),
    coordenadas_instalacion varchar(100),
    operador_asignado_id integer,
    operador_respaldo_id integer,
    canal_principal varchar(20),
    indicativo_llamada varchar(20),
    potencia_configurada_watts numeric(6,2),
    ultimo_mantenimiento date,
    proximo_mantenimiento date,
    horas_operacion_total integer DEFAULT 0,
    numero_transmisiones integer DEFAULT 0,
    antena_conectada varchar(200),
    manual_operacion_id integer,
    imagen_equipo_id integer,
    observaciones text,
    mongodb_configuracion_frecuencias_id varchar(24),
    mongodb_configuracion_tecnica_id varchar(24),
    mongodb_accesorios_id varchar(24),
    mongodb_historial_mantenimiento_id varchar(24),
    disponibilidad_promedio numeric(5,2),
    tiempo_respuesta_promedio_ms integer,
    fallas_acumuladas integer DEFAULT 0,
    costo_mantenimiento_acumulado numeric(12,2),
    eficiencia_energetica numeric(5,2),
    nivel_utilizacion_porcentaje numeric(5,2),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT equipos_comunicacion_pkey PRIMARY KEY (id),
    CONSTRAINT equipos_comunicacion_numero_serie_key UNIQUE (numero_serie),
    CONSTRAINT equipos_comunicacion_codigo_inventario_key UNIQUE (codigo_inventario)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.equipos_comunicacion IS 'Inventario de equipos de comunicación FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.equipos_comunicacion.mongodb_configuracion_frecuencias_id IS 'ObjectId MongoDB: configuración frecuencias asignadas';
COMMENT ON COLUMN fa6_comunicaciones_informatica.equipos_comunicacion.mongodb_configuracion_tecnica_id IS 'ObjectId MongoDB: configuración técnica actual';

-- 3.2 Redes de Comunicación  
CREATE TABLE fa6_comunicaciones_informatica.redes_comunicacion (
    id serial NOT NULL,
    codigo_red varchar(20) NOT NULL,
    nombre_red varchar(200) NOT NULL,
    descripcion_red text,
    tipo_red_id integer NOT NULL,
    estado_red varchar(20) DEFAULT 'ACTIVA',
    responsable_red_id integer NOT NULL,
    backup_responsable_id integer,
    alcance_geografico varchar(200),
    frecuencia_principal varchar(20),
    protocolo_comunicacion varchar(100),
    cifrado_habilitado boolean DEFAULT false,
    nivel_cifrado_id integer,
    cobertura_estimada_km numeric(8,2),
    numero_usuarios_max integer,
    numero_usuarios_actual integer DEFAULT 0,
    fecha_ultima_prueba date,
    proxima_prueba_programada date,
    imagen_red_id integer,
    mongodb_equipos_participantes_id varchar(24),
    mongodb_protocolos_config_id varchar(24),
    mongodb_horarios_operacion_id varchar(24),
    mongodb_procedimientos_emergencia_id varchar(24),
    mongodb_frecuencias_backup_id varchar(24),
    disponibilidad_historica numeric(5,2),
    tiempo_respuesta_promedio_ms integer,
    throughput_promedio_kbps integer,
    incidentes_acumulados integer DEFAULT 0,
    costo_operacion_mensual numeric(10,2),
    nivel_satisfaccion_usuarios numeric(3,2),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT redes_comunicacion_pkey PRIMARY KEY (id),
    CONSTRAINT redes_comunicacion_codigo_key UNIQUE (codigo_red)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.redes_comunicacion IS 'Redes de comunicación FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.redes_comunicacion.mongodb_equipos_participantes_id IS 'ObjectId MongoDB: matriz equipos participantes';

-- =====================================================
-- SECCIÓN 4: EQUIPOS INFORMÁTICOS (Dependiente)
-- =====================================================

-- 4.1 Equipos Informáticos
CREATE TABLE fa6_comunicaciones_informatica.equipos_informaticos (
    id serial NOT NULL,
    numero_serie varchar(100) NOT NULL,
    codigo_inventario varchar(50) NOT NULL,
    nombre_equipo varchar(100),
    tipo_equipo_id integer NOT NULL,
    estado_equipo_id integer NOT NULL,
    sistema_operativo_id integer,
    marca_fabricante varchar(100) NOT NULL,
    modelo_equipo varchar(200) NOT NULL,
    ano_fabricacion integer,
    fecha_adquisicion date NOT NULL,
    costo_adquisicion numeric(12,2),
    proveedor varchar(200),
    ubicacion_actual_id integer NOT NULL,
    sala_oficina varchar(100),
    coordenadas_ubicacion varchar(100),
    usuario_asignado_id integer,
    usuario_respaldo_id integer,
    fecha_asignacion date,
    responsable_tecnico_id integer,
    mongodb_especificaciones_hardware_id varchar(24),
    mongodb_configuracion_red_id varchar(24),
    mongodb_puertos_disponibles_id varchar(24),
    mongodb_perifericos_conectados_id varchar(24),
    imagen_equipo_id integer,
    manual_usuario_id integer,
    certificado_garantia_id integer,
    garantia_vencimiento date,
    ultimo_mantenimiento date,
    proximo_mantenimiento date,
    horas_uso_estimadas integer DEFAULT 0,
    mongodb_software_instalado_id varchar(24),
    mongodb_configuracion_seguridad_id varchar(24),
    mongodb_metricas_rendimiento_id varchar(24),
    mongodb_log_mantenimiento_id varchar(24),
    disponibilidad_promedio numeric(5,2),
    tiempo_respuesta_arranque_segundos integer,
    incidentes_reportados integer DEFAULT 0,
    costo_mantenimiento_acumulado numeric(12,2),
    nivel_utilizacion_promedio numeric(5,2),
    temperatura_operacion_promedio numeric(4,1),
    observaciones text,
    requiere_upgrade boolean DEFAULT false,
    es_equipo_critico boolean DEFAULT false,
    permite_uso_remoto boolean DEFAULT false,
    backup_programado boolean DEFAULT false,
    monitoreo_activo boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT equipos_informaticos_pkey PRIMARY KEY (id),
    CONSTRAINT equipos_informaticos_numero_serie_key UNIQUE (numero_serie),
    CONSTRAINT equipos_informaticos_codigo_inventario_key UNIQUE (codigo_inventario)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.equipos_informaticos IS 'Inventario de equipos informáticos FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.equipos_informaticos.mongodb_software_instalado_id IS 'ObjectId MongoDB: software instalado detallado';

-- 4.2 Licencias de Software
CREATE TABLE fa6_comunicaciones_informatica.licencias_software (
    id serial NOT NULL,
    numero_licencia varchar(100) NOT NULL,
    nombre_licencia varchar(200) NOT NULL,
    tipo_software_id integer NOT NULL,
    fabricante_software varchar(100) NOT NULL,
    version_software varchar(50),
    tipo_licencia varchar(50),
    numero_instalaciones_permitidas integer,
    numero_instalaciones_actuales integer DEFAULT 0,
    fecha_adquisicion date NOT NULL,
    fecha_activacion date,
    fecha_vencimiento date,
    costo_licencia numeric(10,2),
    proveedor_licencia varchar(200),
    numero_orden_compra varchar(50),
    clave_licencia_encriptada text,
    archivo_licencia_id integer,
    requiere_servidor_activacion boolean DEFAULT false,
    servidor_activacion varchar(200),
    permite_transferencia boolean DEFAULT false,
    mongodb_instalaciones_detalle_id varchar(24),
    mongodb_auditoria_compliance_id varchar(24),
    mongodb_metricas_uso_id varchar(24),
    ultimo_uso_registrado date,
    frecuencia_uso varchar(20),
    mongodb_usuarios_autorizados_id varchar(24),
    mongodb_equipos_autorizados_id varchar(24),
    dias_alerta_vencimiento integer DEFAULT 30,
    requiere_renovacion boolean DEFAULT false,
    costo_renovacion numeric(10,2),
    contacto_renovacion varchar(200),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT licencias_software_pkey PRIMARY KEY (id),
    CONSTRAINT licencias_software_numero_key UNIQUE (numero_licencia)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.licencias_software IS 'Licencias de software FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.licencias_software.mongodb_instalaciones_detalle_id IS 'ObjectId MongoDB: detalles de instalaciones';

-- =====================================================
-- SECCIÓN 5: GESTIÓN DE INCIDENTES (Dependiente)
-- =====================================================

-- 5.1 Incidentes FA-6
CREATE TABLE fa6_comunicaciones_informatica.incidentes_fa6 (
    id serial NOT NULL,
    numero_incidente varchar(50) NOT NULL,
    fecha_incidente timestamp with time zone NOT NULL DEFAULT now(),
    tipo_incidente_id integer NOT NULL,
    severidad_id integer NOT NULL DEFAULT 2,
    estado_incidente_id integer NOT NULL DEFAULT 1,
    categoria_sistema varchar(20) NOT NULL, -- 'COMUNICACIONES', 'INFORMATICA', 'MIXTO'
    equipo_comunicacion_afectado_id integer,
    equipo_informatico_afectado_id integer,
    red_afectada_id integer,
    software_afectado_id integer,
    ubicacion_incidente varchar(200),
    coordenadas_incidente varchar(100),
    titulo_incidente varchar(300) NOT NULL,
    descripcion_incidente text NOT NULL,
    sintomas_detallados text,
    pasos_reproducir text,
    reportado_por_id integer NOT NULL,
    fecha_reporte timestamp with time zone DEFAULT now(),
    metodo_reporte varchar(50),
    prioridad_atencion integer DEFAULT 3,
    impacto_operacional text,
    numero_usuarios_afectados integer DEFAULT 1,
    tecnico_asignado_id integer,
    fecha_asignacion timestamp with time zone,
    fecha_inicio_atencion timestamp with time zone,
    fecha_resolucion timestamp with time zone,
    tiempo_respuesta_minutos integer,
    tiempo_resolucion_minutos integer,
    diagnostico_inicial text,
    herramientas_utilizadas jsonb,
    repuestos_utilizados jsonb,
    solucion_aplicada text,
    causa_raiz varchar(200),
    acciones_preventivas text,
    costo_reparacion numeric(10,2),
    costo_repuestos numeric(10,2),
    horas_tecnico numeric(4,2),
    costo_tiempo_perdido numeric(10,2),
    mongodb_diagnostico_detallado_id varchar(24),
    mongodb_logs_error_id varchar(24),
    mongodb_archivos_evidencia_id varchar(24),
    mongodb_comunicaciones_log_id varchar(24),
    requiere_seguimiento boolean DEFAULT false,
    fecha_seguimiento date,
    satisfaccion_usuario integer,
    comentarios_usuario text,
    aprobado_cierre_por_id integer,
    fecha_cierre timestamp with time zone,
    lecciones_aprendidas text,
    mejoras_proceso text,
    requiere_capacitacion boolean DEFAULT false,
    knowledge_base_actualizada boolean DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT incidentes_fa6_pkey PRIMARY KEY (id),
    CONSTRAINT incidentes_fa6_numero_key UNIQUE (numero_incidente)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.incidentes_fa6 IS 'Incidentes de comunicaciones e informática FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.incidentes_fa6.mongodb_diagnostico_detallado_id IS 'ObjectId MongoDB: datos diagnóstico técnico detallado';

-- =====================================================
-- SECCIÓN 6: INFRAESTRUCTURA DE REDES (Dependiente)
-- =====================================================

-- 6.1 Infraestructura de Redes
CREATE TABLE fa6_comunicaciones_informatica.infraestructura_redes (
    id serial NOT NULL,
    codigo_infraestructura varchar(30) NOT NULL,
    nombre_infraestructura varchar(200) NOT NULL,
    tipo_infraestructura varchar(50), -- 'SERVIDOR', 'ROUTER', 'SWITCH', 'FIREWALL', 'ACCESS_POINT'
    ubicacion_id integer NOT NULL,
    responsable_id integer NOT NULL,
    direccion_ip varchar(15),
    mascara_subred varchar(15),
    gateway varchar(15),
    dns_primario varchar(15),
    dns_secundario varchar(15),
    vlan_id integer,
    puerto_fisico varchar(20),
    velocidad_enlace varchar(20),
    duplex_mode varchar(10),
    estado_conectividad varchar(20) DEFAULT 'ACTIVO',
    fecha_ultima_configuracion date,
    mongodb_configuracion_completa_id varchar(24),
    mongodb_reglas_firewall_id varchar(24),
    mongodb_politicas_qos_id varchar(24),
    bandwidth_asignado_mbps integer,
    trafico_promedio_mbps numeric(8,2),
    latencia_promedio_ms numeric(6,2),
    packet_loss_porcentaje numeric(5,3),
    uptime_porcentaje numeric(5,2),
    ultimo_reinicio timestamp with time zone,
    monitoring_habilitado boolean DEFAULT true,
    backup_configuracion_id integer,
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT infraestructura_redes_pkey PRIMARY KEY (id),
    CONSTRAINT infraestructura_redes_codigo_key UNIQUE (codigo_infraestructura)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.infraestructura_redes IS 'Infraestructura de redes FA-6';
COMMENT ON COLUMN fa6_comunicaciones_informatica.infraestructura_redes.mongodb_configuracion_completa_id IS 'ObjectId MongoDB: configuración completa del dispositivo';

-- =====================================================
-- SECCIÓN 7: MÉTRICAS Y DASHBOARDS (Dependiente)
-- =====================================================

-- 7.1 Métricas Tiempo Real FA-6
CREATE TABLE fa6_comunicaciones_informatica.metricas_tiempo_real_fa6 (
    id serial NOT NULL,
    timestamp_registro timestamp with time zone DEFAULT now(),
    equipos_comunicacion_operativos integer DEFAULT 0,
    equipos_informaticos_operativos integer DEFAULT 0,
    redes_comunicacion_activas integer DEFAULT 0,
    servidores_criticos_operativos integer DEFAULT 0,
    porcentaje_disponibilidad_general numeric(5,2) DEFAULT 0,
    incidentes_criticos_abiertos integer DEFAULT 0,
    incidentes_comunicaciones integer DEFAULT 0,
    incidentes_informatica integer DEFAULT 0,
    tiempo_promedio_resolucion_horas numeric(6,2),
    satisfaccion_usuarios_promedio numeric(3,2),
    costo_operacional_diario numeric(12,2),
    licencias_proximas_vencer integer DEFAULT 0,
    equipos_requieren_mantenimiento integer DEFAULT 0,
    backup_sistemas_exitosos integer DEFAULT 0,
    alertas_seguridad_activas integer DEFAULT 0,
    bandwidth_utilizado_porcentaje numeric(5,2),
    observaciones text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT metricas_tiempo_real_fa6_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE fa6_comunicaciones_informatica.metricas_tiempo_real_fa6 IS 'Métricas en tiempo real para dashboard COFAH FA-6';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Equipos Comunicación
ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_tipo_equipo_id_fkey FOREIGN KEY (tipo_equipo_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_equipo_comunicacion (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_estado_equipo_id_fkey FOREIGN KEY (estado_equipo_id)
    REFERENCES fa6_comunicaciones_informatica.estados_equipo_comunicacion (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_ubicacion_actual_id_fkey FOREIGN KEY (ubicacion_actual_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_operador_asignado_id_fkey FOREIGN KEY (operador_asignado_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_operador_respaldo_id_fkey FOREIGN KEY (operador_respaldo_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_manual_operacion_id_fkey FOREIGN KEY (manual_operacion_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_comunicacion
    ADD CONSTRAINT equipos_comunicacion_imagen_equipo_id_fkey FOREIGN KEY (imagen_equipo_id)
    REFERENCES digital_assets.archivos_digitales (id);

-- Redes Comunicación
ALTER TABLE fa6_comunicaciones_informatica.redes_comunicacion
    ADD CONSTRAINT redes_comunicacion_tipo_red_id_fkey FOREIGN KEY (tipo_red_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_red_comunicacion (id);

ALTER TABLE fa6_comunicaciones_informatica.redes_comunicacion
    ADD CONSTRAINT redes_comunicacion_responsable_red_id_fkey FOREIGN KEY (responsable_red_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.redes_comunicacion
    ADD CONSTRAINT redes_comunicacion_backup_responsable_id_fkey FOREIGN KEY (backup_responsable_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.redes_comunicacion
    ADD CONSTRAINT redes_comunicacion_nivel_cifrado_id_fkey FOREIGN KEY (nivel_cifrado_id)
    REFERENCES fa6_comunicaciones_informatica.niveles_cifrado (id);

ALTER TABLE fa6_comunicaciones_informatica.redes_comunicacion
    ADD CONSTRAINT redes_comunicacion_imagen_red_id_fkey FOREIGN KEY (imagen_red_id)
    REFERENCES digital_assets.archivos_digitales (id);

-- Equipos Informáticos
ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_tipo_equipo_id_fkey FOREIGN KEY (tipo_equipo_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_equipo_informatico (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_estado_equipo_id_fkey FOREIGN KEY (estado_equipo_id)
    REFERENCES fa6_comunicaciones_informatica.estados_equipo_informatico (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_sistema_operativo_id_fkey FOREIGN KEY (sistema_operativo_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_sistema_operativo (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_ubicacion_actual_id_fkey FOREIGN KEY (ubicacion_actual_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_usuario_asignado_id_fkey FOREIGN KEY (usuario_asignado_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_usuario_respaldo_id_fkey FOREIGN KEY (usuario_respaldo_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_responsable_tecnico_id_fkey FOREIGN KEY (responsable_tecnico_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_imagen_equipo_id_fkey FOREIGN KEY (imagen_equipo_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_manual_usuario_id_fkey FOREIGN KEY (manual_usuario_id)
    REFERENCES digital_assets.archivos_digitales (id);

ALTER TABLE fa6_comunicaciones_informatica.equipos_informaticos
    ADD CONSTRAINT equipos_informaticos_certificado_garantia_id_fkey FOREIGN KEY (certificado_garantia_id)
    REFERENCES digital_assets.archivos_digitales (id);

-- Licencias Software
ALTER TABLE fa6_comunicaciones_informatica.licencias_software
    ADD CONSTRAINT licencias_software_tipo_software_id_fkey FOREIGN KEY (tipo_software_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_software (id);

ALTER TABLE fa6_comunicaciones_informatica.licencias_software
    ADD CONSTRAINT licencias_software_archivo_licencia_id_fkey FOREIGN KEY (archivo_licencia_id)
    REFERENCES digital_assets.archivos_digitales (id);

-- Incidentes FA-6
ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_tipo_incidente_id_fkey FOREIGN KEY (tipo_incidente_id)
    REFERENCES fa6_comunicaciones_informatica.tipos_incidente (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_severidad_id_fkey FOREIGN KEY (severidad_id)
    REFERENCES fa6_comunicaciones_informatica.niveles_severidad (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_estado_incidente_id_fkey FOREIGN KEY (estado_incidente_id)
    REFERENCES fa6_comunicaciones_informatica.estados_incidente (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_equipo_comunicacion_id_fkey FOREIGN KEY (equipo_comunicacion_afectado_id)
    REFERENCES fa6_comunicaciones_informatica.equipos_comunicacion (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_equipo_informatico_id_fkey FOREIGN KEY (equipo_informatico_afectado_id)
    REFERENCES fa6_comunicaciones_informatica.equipos_informaticos (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_red_afectada_id_fkey FOREIGN KEY (red_afectada_id)
    REFERENCES fa6_comunicaciones_informatica.redes_comunicacion (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_software_afectado_id_fkey FOREIGN KEY (software_afectado_id)
    REFERENCES fa6_comunicaciones_informatica.licencias_software (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_reportado_por_id_fkey FOREIGN KEY (reportado_por_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_tecnico_asignado_id_fkey FOREIGN KEY (tecnico_asignado_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.incidentes_fa6
    ADD CONSTRAINT incidentes_fa6_aprobado_cierre_por_id_fkey FOREIGN KEY (aprobado_cierre_por_id)
    REFERENCES personal.perfiles_militares (id);

-- Infraestructura Redes
ALTER TABLE fa6_comunicaciones_informatica.infraestructura_redes
    ADD CONSTRAINT infraestructura_redes_ubicacion_id_fkey FOREIGN KEY (ubicacion_id)
    REFERENCES organizacion.estructura_militar (id);

ALTER TABLE fa6_comunicaciones_informatica.infraestructura_redes
    ADD CONSTRAINT infraestructura_redes_responsable_id_fkey FOREIGN KEY (responsable_id)
    REFERENCES personal.perfiles_militares (id);

ALTER TABLE fa6_comunicaciones_informatica.infraestructura_redes
    ADD CONSTRAINT infraestructura_redes_backup_configuracion_id_fkey FOREIGN KEY (backup_configuracion_id)
    REFERENCES digital_assets.archivos_digitales (id);