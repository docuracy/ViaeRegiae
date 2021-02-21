-- Database structure based on specification of Linked Places Format v1.2 [https://github.com/LinkedPasts/linked-places]

CREATE TABLE features (
    id uuid DEFAULT uuid_generate_v4 (), -- 'feature_id' for internal use, and prepended with "EMEW-" for public use in API 
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    title VARCHAR NOT NULL -- feature->properties->title
);

CREATE TABLE ccodes ( -- feature->properties->ccodes
    feature_id uuid UNIQUE,
    country char(2) -- ISO 3166-1 alpha-2 [https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2]
);

CREATE TABLE whens ( -- used for features, types, geometries, relations
    id uuid DEFAULT uuid_generate_v4 (), -- 'when_id' for internal use
    parent_id uuid,
    timespans jsonb,
    periods jsonb,
    label VARCHAR,
    duration VARCHAR
);

CREATE TABLE names (
    id uuid DEFAULT uuid_generate_v4 (), -- 'name_id' for internal use
    feature_id uuid,
    name jsonb
);

CREATE TABLE types (
    id uuid DEFAULT uuid_generate_v4 (), -- 'type_id' for internal use
    feature_id uuid,
    type jsonb
);

CREATE TABLE geometries (
    id uuid DEFAULT uuid_generate_v4 (), -- 'geometry_id' for internal use
    feature_id uuid,
    private boolean,
    node point, -- only one of point|edge|area to be set per geometry
    edge lseg,
    area polygon,
    certainty VARCHAR -- Values for the optional certainty attribute can be one of "certain", "less-certain" and "uncertain".
);

CREATE TABLE links (
    id uuid DEFAULT uuid_generate_v4 (), -- 'link_id' for internal use
    feature_id uuid,
    link jsonb
);

CREATE TABLE relations (
    id uuid DEFAULT uuid_generate_v4 (), -- 'relation_id' for internal use
    feature_id uuid,
    relation jsonb
);

CREATE TABLE descriptions (
    id uuid DEFAULT uuid_generate_v4 (), -- 'description_id' for internal use
    feature_id uuid,
    description jsonb
);

CREATE TABLE depictions (
    id uuid DEFAULT uuid_generate_v4 (), -- 'depiction_id' for internal use
    feature_id uuid,
    depiction jsonb
);

CREATE TABLE licences { -- To record restrictions on the use and publication of reference datasets
    parent_id uuid, 
    display_text VARCHAR, 
    licence VARCHAR -- Wikidata Q-number? 
} 
