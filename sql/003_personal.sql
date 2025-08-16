-- =====================================================
-- CARBYFAH - SCHEMA PERSONAL FAH
-- ARCHIVO: 003_personal.sql
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS personal;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES
-- =====================================================

-- =====================================================
-- 1. DATOS PERSONALES
-- =====================================================
CREATE TABLE personal.datos_personales (
    id serial NOT NULL,
    numero_identidad varchar(20) NOT NULL,
    primer_nombre varchar(100) NOT NULL,
    segundo_nombre varchar(100),
    tercer_nombre varchar(100),
    primer_apellido varchar(100) NOT NULL,
    segundo_apellido varchar(100),
    tercer_apellido varchar(100),
    fecha_nacimiento date NOT NULL,
    
    -- LUGAR DE NACIMIENTO (Estructura Geográfica)
    pais_nacimiento_id integer,
    departamento_nacimiento_id integer,
    municipio_nacimiento_id integer,
    ciudad_nacimiento_id integer,
    lugar_nacimiento_especifico varchar(200),
    
    -- NACIONALIDAD
    nacionalidad varchar(100) DEFAULT 'Hondureña',
    
    -- RESIDENCIA ACTUAL (Estructura Geográfica)
    pais_residencia_id integer,
    departamento_residencia_id integer,
    municipio_residencia_id integer,
    ciudad_residencia_id integer,
    direccion_residencia_especifica varchar(500),
    
    -- DATOS PERSONALES
    tipo_genero_id integer,
    estado_civil varchar(50),
    telefono_personal varchar(20),
    telefono_emergencia varchar(20),
    email_personal varchar(200),
    
    -- CONTACTO DE EMERGENCIA
    contacto_emergencia_nombre varchar(200),
    contacto_emergencia_telefono varchar(20),
    contacto_emergencia_relacion varchar(100),
    
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT datos_personales_pkey PRIMARY KEY (id),
    CONSTRAINT datos_personales_numero_identidad_key UNIQUE (numero_identidad)
);

COMMENT ON TABLE personal.datos_personales IS 'Información personal básica con estructura geográfica';
COMMENT ON COLUMN personal.datos_personales.numero_identidad IS 'Número de identidad nacional (único)';
COMMENT ON COLUMN personal.datos_personales.lugar_nacimiento_especifico IS 'Detalle específico del lugar (hospital, clínica, etc.)';
COMMENT ON COLUMN personal.datos_personales.direccion_residencia_especifica IS 'Dirección específica de residencia';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 2. PERFILES MILITARES
-- =====================================================
CREATE TABLE personal.perfiles_militares (
    id serial NOT NULL,
    datos_personales_id integer NOT NULL,
    serie_militar varchar(20),
    fecha_ingreso_fah date,
    fecha_retiro_fah date,
    motivo_retiro varchar(200),
    categoria_personal_id integer,
    especialidad_id integer,
    grado_actual_id integer,
    estado_servicio varchar(20) DEFAULT 'ACTIVO',
    situacion varchar(50) DEFAULT 'DISPONIBLE',
    tiempo_servicio_anos integer,
    tiempo_servicio_meses integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT perfiles_militares_pkey PRIMARY KEY (id),
    CONSTRAINT perfiles_militares_serie_militar_key UNIQUE (serie_militar),
    CONSTRAINT perfiles_militares_datos_personales_id_key UNIQUE (datos_personales_id)
);

COMMENT ON TABLE personal.perfiles_militares IS 'Información militar específica del personal FAH';
COMMENT ON COLUMN personal.perfiles_militares.datos_personales_id IS 'Relación 1:1 con datos personales';
COMMENT ON COLUMN personal.perfiles_militares.serie_militar IS 'Número de serie militar único';
COMMENT ON COLUMN personal.perfiles_militares.tiempo_servicio_anos IS 'Años de servicio activo';
COMMENT ON COLUMN personal.perfiles_militares.estado_servicio IS 'Estado: ACTIVO, RETIRADO, SUSPENDIDO';
COMMENT ON COLUMN personal.perfiles_militares.situacion IS 'Situación: DISPONIBLE, COMISION, PERMISO, etc.';

-- =====================================================
-- 3. ASIGNACIONES ACTUALES
-- =====================================================
CREATE TABLE personal.asignaciones_actuales (
    id serial NOT NULL,
    perfil_militar_id integer NOT NULL,
    estructura_militar_id integer,
    cargo_id integer,
    fecha_inicio_asignacion date NOT NULL DEFAULT CURRENT_DATE,
    fecha_fin_asignacion date,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT asignaciones_actuales_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE personal.asignaciones_actuales IS 'Asignaciones actuales de personal a unidades y cargos';
COMMENT ON COLUMN personal.asignaciones_actuales.perfil_militar_id IS 'Personal militar asignado';
COMMENT ON COLUMN personal.asignaciones_actuales.estructura_militar_id IS 'Unidad donde está asignado';
COMMENT ON COLUMN personal.asignaciones_actuales.cargo_id IS 'Cargo específico que ocupa';
COMMENT ON COLUMN personal.asignaciones_actuales.fecha_inicio_asignacion IS 'Cuando inició en esta asignación';
COMMENT ON COLUMN personal.asignaciones_actuales.fecha_fin_asignacion IS 'Cuando termina (null = indefinida)';

-- =====================================================
-- 4. USUARIOS SISTEMA
-- =====================================================
CREATE TABLE personal.usuarios_sistema (
    id serial NOT NULL,
    perfil_militar_id integer NOT NULL,
    username varchar(100) NOT NULL,
    email_institucional varchar(200) NOT NULL,
    password_hash varchar(500) NOT NULL,
    ultimo_acceso timestamp with time zone,
    intentos_fallidos integer DEFAULT 0,
    cuenta_bloqueada boolean DEFAULT false,
    fecha_bloqueo timestamp with time zone,
    motivo_bloqueo varchar(500),
    requiere_cambio_password boolean DEFAULT true,
    fecha_cambio_password timestamp with time zone,
    token_recuperacion varchar(255),
    fecha_expiracion_token timestamp with time zone,
    configuraciones_usuario jsonb,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT usuarios_sistema_pkey PRIMARY KEY (id),
    CONSTRAINT usuarios_sistema_username_key UNIQUE (username),
    CONSTRAINT usuarios_sistema_email_institucional_key UNIQUE (email_institucional),
    CONSTRAINT usuarios_sistema_perfil_militar_id_key UNIQUE (perfil_militar_id)
);

COMMENT ON TABLE personal.usuarios_sistema IS 'Usuarios del sistema CARBYFAH';
COMMENT ON COLUMN personal.usuarios_sistema.perfil_militar_id IS 'Relación 1:1 con perfil militar';
COMMENT ON COLUMN personal.usuarios_sistema.configuraciones_usuario IS 'Configuraciones personalizadas del usuario en formato JSON';
COMMENT ON COLUMN personal.usuarios_sistema.token_recuperacion IS 'Token para recuperación de contraseña';

-- =====================================================
-- 5. HISTORIALES DE CARGOS
-- =====================================================
CREATE TABLE personal.historiales_cargos (
    id serial NOT NULL,
    perfil_militar_id integer NOT NULL,
    cargo_id integer NOT NULL,
    estructura_militar_id integer NOT NULL,
    fecha_inicio date NOT NULL,
    fecha_fin date,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT historiales_cargos_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE personal.historiales_cargos IS 'Historial completo de cargos ocupados por el personal';
COMMENT ON COLUMN personal.historiales_cargos.perfil_militar_id IS 'Militar que ocupó el cargo';
COMMENT ON COLUMN personal.historiales_cargos.cargo_id IS 'Cargo que ocupó';
COMMENT ON COLUMN personal.historiales_cargos.estructura_militar_id IS 'Unidad donde ocupó el cargo';
COMMENT ON COLUMN personal.historiales_cargos.fecha_inicio IS 'Cuando inició en el cargo';
COMMENT ON COLUMN personal.historiales_cargos.fecha_fin IS 'Cuando terminó en el cargo (null = actual)';

-- =====================================================
-- 6. ASIGNACIÓN DE ROLES
-- =====================================================
CREATE TABLE personal.asignacion_roles (
    id serial NOT NULL,
    perfil_militar_id integer NOT NULL,
    rol_funcional_id integer NOT NULL,
    fecha_asignacion date NOT NULL DEFAULT CURRENT_DATE,
    fecha_expiracion date,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT asignacion_roles_pkey PRIMARY KEY (id)
);

COMMENT ON TABLE personal.asignacion_roles IS 'Asignación de roles funcionales a personal militar';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Datos Personales → Estructura Geográfica (Nacimiento)
ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_pais_nacimiento_id_fkey FOREIGN KEY (pais_nacimiento_id)
    REFERENCES catalogos.paises (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_departamento_nacimiento_id_fkey FOREIGN KEY (departamento_nacimiento_id)
    REFERENCES organizacion.departamentos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_municipio_nacimiento_id_fkey FOREIGN KEY (municipio_nacimiento_id)
    REFERENCES organizacion.municipios (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_ciudad_nacimiento_id_fkey FOREIGN KEY (ciudad_nacimiento_id)
    REFERENCES organizacion.ciudades (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Datos Personales → Estructura Geográfica (Residencia)
ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_pais_residencia_id_fkey FOREIGN KEY (pais_residencia_id)
    REFERENCES catalogos.paises (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_departamento_residencia_id_fkey FOREIGN KEY (departamento_residencia_id)
    REFERENCES organizacion.departamentos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_municipio_residencia_id_fkey FOREIGN KEY (municipio_residencia_id)
    REFERENCES organizacion.municipios (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_ciudad_residencia_id_fkey FOREIGN KEY (ciudad_residencia_id)
    REFERENCES organizacion.ciudades (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Datos Personales → Catálogos
ALTER TABLE personal.datos_personales
    ADD CONSTRAINT datos_personales_tipo_genero_id_fkey FOREIGN KEY (tipo_genero_id)
    REFERENCES catalogos.tipos_genero (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Perfiles Militares → Datos Personales (1:1)
ALTER TABLE personal.perfiles_militares
    ADD CONSTRAINT perfiles_militares_datos_personales_id_fkey FOREIGN KEY (datos_personales_id)
    REFERENCES personal.datos_personales (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Perfiles Militares → Catálogos
ALTER TABLE personal.perfiles_militares
    ADD CONSTRAINT perfiles_militares_categoria_personal_id_fkey FOREIGN KEY (categoria_personal_id)
    REFERENCES catalogos.categorias_personal (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.perfiles_militares
    ADD CONSTRAINT perfiles_militares_especialidad_id_fkey FOREIGN KEY (especialidad_id)
    REFERENCES catalogos.especialidades (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.perfiles_militares
    ADD CONSTRAINT perfiles_militares_grado_actual_id_fkey FOREIGN KEY (grado_actual_id)
    REFERENCES catalogos.grados (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Asignaciones Actuales → Personal y Organización
ALTER TABLE personal.asignaciones_actuales
    ADD CONSTRAINT asignaciones_actuales_perfil_militar_id_fkey FOREIGN KEY (perfil_militar_id)
    REFERENCES personal.perfiles_militares (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.asignaciones_actuales
    ADD CONSTRAINT asignaciones_actuales_estructura_militar_id_fkey FOREIGN KEY (estructura_militar_id)
    REFERENCES organizacion.estructura_militar (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.asignaciones_actuales
    ADD CONSTRAINT asignaciones_actuales_cargo_id_fkey FOREIGN KEY (cargo_id)
    REFERENCES organizacion.cargos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Usuarios Sistema → Perfiles Militares (1:1)
ALTER TABLE personal.usuarios_sistema
    ADD CONSTRAINT usuarios_sistema_perfil_militar_id_fkey FOREIGN KEY (perfil_militar_id)
    REFERENCES personal.perfiles_militares (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Historiales Cargos → Personal y Organización
ALTER TABLE personal.historiales_cargos
    ADD CONSTRAINT historiales_cargos_perfil_militar_id_fkey FOREIGN KEY (perfil_militar_id)
    REFERENCES personal.perfiles_militares (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.historiales_cargos
    ADD CONSTRAINT historiales_cargos_cargo_id_fkey FOREIGN KEY (cargo_id)
    REFERENCES organizacion.cargos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.historiales_cargos
    ADD CONSTRAINT historiales_cargos_estructura_militar_id_fkey FOREIGN KEY (estructura_militar_id)
    REFERENCES organizacion.estructura_militar (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Asignación Roles → Personal y Organización
ALTER TABLE personal.asignacion_roles
    ADD CONSTRAINT asignacion_roles_perfil_militar_id_fkey FOREIGN KEY (perfil_militar_id)
    REFERENCES personal.perfiles_militares (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE personal.asignacion_roles
    ADD CONSTRAINT asignacion_roles_rol_funcional_id_fkey FOREIGN KEY (rol_funcional_id)
    REFERENCES organizacion.roles_funcionales (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;