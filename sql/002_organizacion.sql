-- =====================================================
-- CARBYFAH - SCHEMA ORGANIZACIÓN MILITAR FAH
-- ARCHIVO: 002_organizacion.sql
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS organizacion;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES
-- =====================================================

-- =====================================================
-- 1. DEPARTAMENTOS (Por País)
-- =====================================================
CREATE TABLE organizacion.departamentos (
    id serial NOT NULL,
    pais_id integer NOT NULL,
    codigo_departamento varchar(10) NOT NULL,
    nombre_departamento varchar(100) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT departamentos_pkey PRIMARY KEY (id),
    CONSTRAINT departamentos_codigo_key UNIQUE (pais_id, codigo_departamento)
);

COMMENT ON TABLE organizacion.departamentos IS 'Departamentos por país (Francisco Morazán, Cortés, etc.)';

-- =====================================================
-- 2. MUNICIPIOS (Por Departamento)
-- =====================================================
CREATE TABLE organizacion.municipios (
    id serial NOT NULL,
    departamento_id integer NOT NULL,
    codigo_municipio varchar(10) NOT NULL,
    nombre_municipio varchar(100) NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT municipios_pkey PRIMARY KEY (id),
    CONSTRAINT municipios_codigo_key UNIQUE (departamento_id, codigo_municipio)
);

COMMENT ON TABLE organizacion.municipios IS 'Municipios por departamento (Tegucigalpa, San Pedro Sula, etc.)';

-- =====================================================
-- 3. CIUDADES/LOCALIDADES (Por Municipio)
-- =====================================================
CREATE TABLE organizacion.ciudades (
    id serial NOT NULL,
    municipio_id integer NOT NULL,
    codigo_ciudad varchar(10) NOT NULL,
    nombre_ciudad varchar(100) NOT NULL,
    tipo_localidad varchar(20) DEFAULT 'Ciudad',
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT ciudades_pkey PRIMARY KEY (id),
    CONSTRAINT ciudades_codigo_key UNIQUE (municipio_id, codigo_ciudad)
);

COMMENT ON TABLE organizacion.ciudades IS 'Ciudades y localidades por municipio';
COMMENT ON COLUMN organizacion.ciudades.tipo_localidad IS 'Tipo: Ciudad, Aldea, Caserio, Colonia';

-- =====================================================
-- 4. UBICACIONES GEOGRÁFICAS EXACTAS (CON MAPA)
-- =====================================================
CREATE TABLE organizacion.ubicaciones_geograficas (
    id serial NOT NULL,
    codigo_ubicacion varchar(30) NOT NULL,
    nombre_ubicacion varchar(200) NOT NULL,
    
    -- ESTRUCTURA GEOGRÁFICA CASCADA
    pais_id integer NOT NULL,
    departamento_id integer NOT NULL,
    municipio_id integer NOT NULL,
    ciudad_id integer,
    
    -- COORDENADAS EXACTAS (CLICK EN MAPA)
    latitud numeric(10, 8) NOT NULL,
    longitud numeric(11, 8) NOT NULL,
    direccion_referencia varchar(500),
    
    -- INFORMACIÓN ADICIONAL
    altitud_metros integer,
    telefono_principal varchar(20),
    telefono_emergencia varchar(20),
    
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT ubicaciones_geograficas_pkey PRIMARY KEY (id),
    CONSTRAINT ubicaciones_geograficas_codigo_ubicacion_key UNIQUE (codigo_ubicacion)
);

COMMENT ON TABLE organizacion.ubicaciones_geograficas IS 'Ubicaciones exactas con coordenadas de mapa para instalaciones FAH';
COMMENT ON COLUMN organizacion.ubicaciones_geograficas.latitud IS 'Latitud obtenida por click en mapa';
COMMENT ON COLUMN organizacion.ubicaciones_geograficas.longitud IS 'Longitud obtenida por click en mapa';
COMMENT ON COLUMN organizacion.ubicaciones_geograficas.direccion_referencia IS 'Referencia adicional: 500m norte del parque central';

-- =====================================================
-- 5. ROLES FUNCIONALES
-- =====================================================
CREATE TABLE organizacion.roles_funcionales (
    id serial NOT NULL,
    codigo_rol varchar(50) NOT NULL,
    nombre_rol varchar(200) NOT NULL,
    nivel_autoridad integer DEFAULT 1,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT roles_funcionales_pkey PRIMARY KEY (id),
    CONSTRAINT roles_funcionales_codigo_rol_key UNIQUE (codigo_rol)
);

COMMENT ON TABLE organizacion.roles_funcionales IS 'Roles funcionales dentro de la organización FAH';
COMMENT ON COLUMN organizacion.roles_funcionales.nivel_autoridad IS 'Nivel de autoridad del rol (1=bajo, 10=máximo)';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 6. ESTRUCTURA MILITAR (Dependiente)
-- =====================================================
CREATE TABLE organizacion.estructura_militar (
    id serial NOT NULL,
    codigo_unidad varchar(50) NOT NULL,
    nombre_unidad varchar(200) NOT NULL,
    tipo_estructura_id integer NOT NULL,
    ubicacion_geografica_id integer,
    unidad_padre_id integer,
    nivel_jerarquico integer NOT NULL DEFAULT 1,
    orden_horizontal integer,
    capacidad_personal integer,
    fecha_activacion date,
    fecha_desactivacion date,
    logo_url varchar(500),
    mision text,
    vision text,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT estructura_militar_pkey PRIMARY KEY (id),
    CONSTRAINT estructura_militar_codigo_unidad_key UNIQUE (codigo_unidad)
);

COMMENT ON TABLE organizacion.estructura_militar IS 'Estructura militar completa con relaciones jerárquicas';
COMMENT ON COLUMN organizacion.estructura_militar.codigo_unidad IS 'Código único de la unidad (FA-1, HAM, JEMGA)';
COMMENT ON COLUMN organizacion.estructura_militar.nombre_unidad IS 'Nombre completo de la unidad';
COMMENT ON COLUMN organizacion.estructura_militar.unidad_padre_id IS 'Unidad superior en la jerarquía (auto-referencia)';
COMMENT ON COLUMN organizacion.estructura_militar.nivel_jerarquico IS 'Nivel en la jerarquía organizacional';
COMMENT ON COLUMN organizacion.estructura_militar.orden_horizontal IS 'Orden horizontal para unidades del mismo nivel (FA-1=1, FA-2=2, etc.)';
COMMENT ON COLUMN organizacion.estructura_militar.capacidad_personal IS 'Capacidad máxima de personal de la unidad';

-- =====================================================
-- 7. CARGOS ORGANIZACIONALES (Dependiente)
-- =====================================================
CREATE TABLE organizacion.cargos (
    id serial NOT NULL,
    estructura_militar_id integer NOT NULL,
    codigo_cargo varchar(50) NOT NULL,
    nombre_cargo varchar(200) NOT NULL,
    nivel_jerarquico integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT cargos_pkey PRIMARY KEY (id),
    CONSTRAINT cargos_codigo_cargo_key UNIQUE (codigo_cargo)
);

COMMENT ON TABLE organizacion.cargos IS 'Cargos disponibles en cada unidad organizacional';
COMMENT ON COLUMN organizacion.cargos.estructura_militar_id IS 'Unidad donde existe el cargo';
COMMENT ON COLUMN organizacion.cargos.nivel_jerarquico IS 'Nivel jerárquico del cargo dentro de la unidad';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Departamentos → Países
ALTER TABLE organizacion.departamentos
    ADD CONSTRAINT departamentos_pais_id_fkey FOREIGN KEY (pais_id)
    REFERENCES catalogos.paises (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Municipios → Departamentos  
ALTER TABLE organizacion.municipios
    ADD CONSTRAINT municipios_departamento_id_fkey FOREIGN KEY (departamento_id)
    REFERENCES organizacion.departamentos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Ciudades → Municipios
ALTER TABLE organizacion.ciudades
    ADD CONSTRAINT ciudades_municipio_id_fkey FOREIGN KEY (municipio_id)
    REFERENCES organizacion.municipios (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Ubicaciones → Estructura Geográfica
ALTER TABLE organizacion.ubicaciones_geograficas
    ADD CONSTRAINT ubicaciones_pais_id_fkey FOREIGN KEY (pais_id)
    REFERENCES catalogos.paises (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE organizacion.ubicaciones_geograficas
    ADD CONSTRAINT ubicaciones_departamento_id_fkey FOREIGN KEY (departamento_id)
    REFERENCES organizacion.departamentos (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE organizacion.ubicaciones_geograficas
    ADD CONSTRAINT ubicaciones_municipio_id_fkey FOREIGN KEY (municipio_id)
    REFERENCES organizacion.municipios (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE organizacion.ubicaciones_geograficas
    ADD CONSTRAINT ubicaciones_ciudad_id_fkey FOREIGN KEY (ciudad_id)
    REFERENCES organizacion.ciudades (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Estructura Militar → Tipos Estructura Militar (catálogos)
ALTER TABLE organizacion.estructura_militar
    ADD CONSTRAINT estructura_militar_tipo_estructura_id_fkey FOREIGN KEY (tipo_estructura_id)
    REFERENCES catalogos.tipos_estructura_militar (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Estructura Militar → Ubicaciones Geográficas
ALTER TABLE organizacion.estructura_militar
    ADD CONSTRAINT estructura_militar_ubicacion_geografica_id_fkey FOREIGN KEY (ubicacion_geografica_id)
    REFERENCES organizacion.ubicaciones_geograficas (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Estructura Militar → Auto-referencia (Unidad Padre)
ALTER TABLE organizacion.estructura_militar
    ADD CONSTRAINT estructura_militar_unidad_padre_id_fkey FOREIGN KEY (unidad_padre_id)
    REFERENCES organizacion.estructura_militar (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Cargos → Estructura Militar
ALTER TABLE organizacion.cargos
    ADD CONSTRAINT cargos_estructura_militar_id_fkey FOREIGN KEY (estructura_militar_id)
    REFERENCES organizacion.estructura_militar (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;