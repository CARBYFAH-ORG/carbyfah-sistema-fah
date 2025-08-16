
-- =====================================================
-- CARBYFAH - SCHEMA CATÁLOGOS COMPLETO
-- FASE 1: TABLAS INDEPENDIENTES (Sin Foreign Keys)
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- Crear schema
CREATE SCHEMA IF NOT EXISTS catalogos;

-- =====================================================
-- FASE 1: TABLAS INDEPENDIENTES
-- =====================================================

-- =====================================================
-- 1. TIPOS DE GÉNERO
-- =====================================================
CREATE TABLE catalogos.tipos_genero (
    id serial NOT NULL,
    codigo varchar(10) NOT NULL,
    nombre varchar(50) NOT NULL,
    abreviatura varchar(5),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_genero_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_genero_codigo_key UNIQUE (codigo)
);

COMMENT ON TABLE catalogos.tipos_genero IS 'Catálogo de tipos de género (Masculino, Femenino)';

-- =====================================================
-- 2. TIPOS DE ESTADO GENERAL
-- =====================================================
CREATE TABLE catalogos.tipos_estado_general (
    id serial NOT NULL,
    codigo varchar(20) NOT NULL,
    nombre varchar(100) NOT NULL,
    permite_operaciones boolean DEFAULT true,
    es_estado_final boolean DEFAULT false,
    requiere_justificacion boolean DEFAULT false,
    -- Resto de campos audit trail estándar
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_estado_general_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_estado_general_codigo_key UNIQUE (codigo)
);
COMMENT ON TABLE catalogos.tipos_estado_general IS 'Estados generales del sistema (Activo, Inactivo, Suspendido, etc.)';
COMMENT ON COLUMN catalogos.tipos_estado_general.permite_operaciones IS 'Indica si permite realizar operaciones en este estado';

-- =====================================================
-- 3. NIVELES DE PRIORIDAD
-- =====================================================
CREATE TABLE catalogos.niveles_prioridad (
    id serial NOT NULL,
    codigo varchar(20) NOT NULL,
    nombre varchar(100) NOT NULL,
    nivel_numerico integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_prioridad_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_prioridad_codigo_key UNIQUE (codigo)
);

COMMENT ON TABLE catalogos.niveles_prioridad IS 'Niveles de prioridad (Baja, Media, Alta, Crítica, etc.)';

-- =====================================================
-- 4. NIVELES DE SEGURIDAD
-- =====================================================
CREATE TABLE catalogos.niveles_seguridad (
    id serial NOT NULL,
    codigo varchar(20) NOT NULL,
    nombre varchar(100) NOT NULL,
    nivel_numerico integer NOT NULL,
    requiere_autorizacion boolean DEFAULT false,
    tiempo_retencion_anos integer,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT niveles_seguridad_pkey PRIMARY KEY (id),
    CONSTRAINT niveles_seguridad_codigo_key UNIQUE (codigo)
);

COMMENT ON TABLE catalogos.niveles_seguridad IS 'Niveles de seguridad de información (Público, Restringido, Confidencial, Secreto)';
COMMENT ON COLUMN catalogos.niveles_seguridad.nivel_numerico IS 'Nivel numérico para comparaciones (1=menor seguridad, 5=máxima seguridad)';

-- =====================================================
-- 5. PAÍSES
-- =====================================================
CREATE TABLE catalogos.paises (
    id serial NOT NULL,
    nombre varchar(100) NOT NULL,
    nombre_oficial varchar(200),
    codigo_iso3 char(3) NOT NULL,
    codigo_telefono varchar(10),
    moneda_oficial varchar(50),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT paises_pkey PRIMARY KEY (id),
    CONSTRAINT paises_codigo_iso3_key UNIQUE (codigo_iso3)
);

COMMENT ON TABLE catalogos.paises IS 'Catálogo de países según ISO 3166-1';

-- =====================================================
-- 6. TIPOS DE ESTRUCTURA MILITAR
-- =====================================================
CREATE TABLE catalogos.tipos_estructura_militar (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    nivel_organizacional integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_estructura_militar_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_estructura_militar_codigo_tipo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE catalogos.tipos_estructura_militar IS 'Tipos de estructura militar (Comandancia, Base, Escuadrón, etc.)';

-- =====================================================
-- 7. TIPOS DE JERARQUÍA
-- =====================================================
CREATE TABLE catalogos.tipos_jerarquia (
    id serial NOT NULL,
    codigo_tipo varchar(20) NOT NULL,
    nombre_tipo varchar(100) NOT NULL,
    nivel_autoridad integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_jerarquia_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_jerarquia_codigo_tipo_key UNIQUE (codigo_tipo)
);

COMMENT ON TABLE catalogos.tipos_jerarquia IS 'Tipos de relación jerárquica (Comando Directo, Comando Operacional, etc.)';

-- =====================================================
-- 8. CATEGORÍAS DE PERSONAL
-- =====================================================
CREATE TABLE catalogos.categorias_personal (
    id serial NOT NULL,
    codigo_categoria varchar(20) NOT NULL,
    nombre_categoria varchar(100) NOT NULL,
    orden_jerarquico integer NOT NULL,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT categorias_personal_pkey PRIMARY KEY (id),
    CONSTRAINT categorias_personal_codigo_categoria_key UNIQUE (codigo_categoria)
);

COMMENT ON TABLE catalogos.categorias_personal IS 'Categorías del personal militar (Oficial, Suboficial, Tropa, Auxiliar)';

-- =====================================================
-- 9. ESPECIALIDADES
-- =====================================================
CREATE TABLE catalogos.especialidades (
    id serial NOT NULL,
    codigo_especialidad varchar(20) NOT NULL,
    nombre_especialidad varchar(100) NOT NULL,
    insignia_url varchar(500),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT especialidades_pkey PRIMARY KEY (id),
    CONSTRAINT especialidades_codigo_especialidad_key UNIQUE (codigo_especialidad)
);

COMMENT ON TABLE catalogos.especialidades IS 'Especialidades militares (Aviación, Comunicaciones, Inteligencia, etc.)';

-- =====================================================
-- 10. TIPOS DE EVENTO
-- =====================================================
CREATE TABLE catalogos.tipos_evento (
    id serial NOT NULL,
    codigo_evento varchar(20) NOT NULL,
    nombre_evento varchar(100) NOT NULL,
    requiere_aprobacion boolean NOT NULL DEFAULT false,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT tipos_evento_pkey PRIMARY KEY (id),
    CONSTRAINT tipos_evento_codigo_evento_key UNIQUE (codigo_evento)
);

COMMENT ON TABLE catalogos.tipos_evento IS 'Tipos de eventos del sistema (Capacitación, Misión, Permiso, etc.)';

-- =====================================================
-- FASE 2: TABLAS DEPENDIENTES (Con Foreign Keys)
-- =====================================================

-- =====================================================
-- 11. GRADOS MILITARES
-- =====================================================
CREATE TABLE catalogos.grados (
    id serial NOT NULL,
    categoria_personal_id integer NOT NULL,
    codigo_grado varchar(20) NOT NULL,
    nombre_grado varchar(100) NOT NULL,
    abreviatura varchar(20),
    orden_jerarquico integer NOT NULL,
    insignia_url varchar(500),
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    created_by integer NOT NULL DEFAULT 1,
    updated_at timestamp with time zone NOT NULL DEFAULT now(),
    updated_by integer NOT NULL DEFAULT 1,
    deleted_at timestamp with time zone,
    deleted_by integer,
    is_active boolean NOT NULL DEFAULT true,
    version integer NOT NULL DEFAULT 1,
    CONSTRAINT grados_pkey PRIMARY KEY (id),
    CONSTRAINT grados_codigo_grado_key UNIQUE (codigo_grado)
);

COMMENT ON TABLE catalogos.grados IS 'Grados militares por categoría (Subteniente, Teniente, Capitán para Oficiales, etc.)';
COMMENT ON COLUMN catalogos.grados.categoria_personal_id IS 'Categoría a la que pertenece el grado (Oficial, Suboficial, etc.)';

-- =====================================================
-- FOREIGN KEYS - FASE 2
-- =====================================================

-- Grados → Categorías Personal
ALTER TABLE catalogos.grados
    ADD CONSTRAINT grados_categoria_personal_id_fkey FOREIGN KEY (categoria_personal_id)
    REFERENCES catalogos.categorias_personal (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;