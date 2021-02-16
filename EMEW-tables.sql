-- Database structure based on specification of Linked Places Format v1.2 [https://github.com/LinkedPasts/linked-places]

CREATE TABLE features (
    id uuid DEFAULT uuid_generate_v4 (), -- 'feature_id' for public use in API, and for internal use
);

CREATE TABLE properties (
    id uuid DEFAULT uuid_generate_v4 (), -- 'property_id' for internal use
    feature_id uuid,
    title VARCHAR NOT NULL
);

CREATE TABLE ccodes (
    property_id uuid,
    country char(2) -- ISO 3166-1 alpha-2 [https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2]
);

CREATE TABLE whens (
    id uuid DEFAULT uuid_generate_v4 (), -- 'when_id' for internal use
    parent_id uuid, -- used for features, types, geometries, relations
    label VARCHAR,
    duraction VARCHAR
);

CREATE TABLE timespans (
    id uuid DEFAULT uuid_generate_v4 (), -- 'timespan_id' for internal use
    when_id uuid
);

CREATE TABLE starts_ends (
    timespan_id uuid,
    is_end boolean,
    iel char(1) -- "in," "earliest," or "latest"
    owl VARCHAR -- ISO 8601 expression as described by the OWL-Time ontology [https://www.w3.org/TR/owl-time/]
);

CREATE TABLE periods (
    when_id uuid,
    name VARCHAR,
    uri VARCHAR
);

CREATE TABLE toponyms (
    id uuid DEFAULT uuid_generate_v4 (), -- 'toponym_id' for internal use
    feature_id uuid,
    toponym VARCHAR,
    lang char(2)
);

CREATE TABLE citations ( 
    parent_id uuid, -- used for toponyms, relations
    label VARCHAR,
    citation_id VARCHAR
);

CREATE TABLE types (
    id uuid DEFAULT uuid_generate_v4 (), -- 'type_id' for internal use
    feature_id uuid,
    identifier VARCHAR,
    label VARCHAR
);

CREATE TABLE source_labels (
    type_id uuid,
    label VARCHAR,
    lang char(2)
);

CREATE TABLE geometries (
    id uuid DEFAULT uuid_generate_v4 (), -- 'geometry_id' for internal use
    feature_id uuid,
    node point, -- only one of point|edge|area to be set per geometry
    edge lseg,
    area polygon,
    certainty VARCHAR -- Values for the optional certainty attribute can be one of "certain", "less-certain" and "uncertain".
);

CREATE TABLE links (
    feature_id uuid,
    type VARCHAR,
    identifier VARCHAR
);

CREATE TABLE relations (
    id uuid DEFAULT uuid_generate_v4 (), -- 'relation_id' for internal use
    feature_id uuid,
    feature_id uuid,
    relation_type VARCHAR,
    relation_to VARCHAR,
    label VARCHAR,
    certainty VARCHAR -- Values for the optional certainty attribute can be one of "certain", "less-certain" and "uncertain".
);

CREATE TABLE descriptions (
    feature_id uuid,
    value VARCHAR,
    lang char(2),
    source VARCHAR
);

CREATE TABLE depictions (
    feature_id uuid,
    id VARCHAR,
    title VARCHAR,
    license VARCHAR
);
