PGDMP         -                |            emew %   12.15 (Ubuntu 12.15-0ubuntu0.20.04.1) %   12.15 (Ubuntu 12.15-0ubuntu0.20.04.1) &    "           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            #           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            $           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            %           1262    16653    emew    DATABASE     v   CREATE DATABASE emew WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';
    DROP DATABASE emew;
                docuracy    false                        3079    16654 	   uuid-ossp 	   EXTENSION     ?   CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;
    DROP EXTENSION "uuid-ossp";
                   false            &           0    0    EXTENSION "uuid-ossp"    COMMENT     W   COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';
                        false    2            �            1259    16676    ccodes    TABLE     O   CREATE TABLE public.ccodes (
    property_id uuid,
    country character(2)
);
    DROP TABLE public.ccodes;
       public         heap    docuracy    false            �            1259    16709 	   citations    TABLE     v   CREATE TABLE public.citations (
    parent_id uuid,
    label character varying,
    citation_id character varying
);
    DROP TABLE public.citations;
       public         heap    docuracy    false            �            1259    16760 
   depictions    TABLE     �   CREATE TABLE public.depictions (
    feature_id uuid,
    id character varying,
    title character varying,
    license character varying
);
    DROP TABLE public.depictions;
       public         heap    docuracy    false            �            1259    16754    descriptions    TABLE     �   CREATE TABLE public.descriptions (
    feature_id uuid,
    value character varying,
    lang character(2),
    source character varying
);
     DROP TABLE public.descriptions;
       public         heap    docuracy    false            �            1259    16665    features    TABLE     P   CREATE TABLE public.features (
    id uuid DEFAULT public.uuid_generate_v4()
);
    DROP TABLE public.features;
       public         heap    docuracy    false    2            �            1259    16728 
   geometries    TABLE     �   CREATE TABLE public.geometries (
    id uuid DEFAULT public.uuid_generate_v4(),
    feature_id uuid,
    node point,
    edge lseg,
    area polygon,
    certainty character varying
);
    DROP TABLE public.geometries;
       public         heap    docuracy    false    2            �            1259    16741    links    TABLE     q   CREATE TABLE public.links (
    feature_id uuid,
    type character varying,
    identifier character varying
);
    DROP TABLE public.links;
       public         heap    docuracy    false            �            1259    16696    periods    TABLE     i   CREATE TABLE public.periods (
    when_id uuid,
    name character varying,
    uri character varying
);
    DROP TABLE public.periods;
       public         heap    docuracy    false            �            1259    16669 
   properties    TABLE     �   CREATE TABLE public.properties (
    id uuid DEFAULT public.uuid_generate_v4(),
    feature_id uuid,
    title character varying NOT NULL
);
    DROP TABLE public.properties;
       public         heap    docuracy    false    2            �            1259    16747 	   relations    TABLE     �   CREATE TABLE public.relations (
    id uuid DEFAULT public.uuid_generate_v4(),
    feature_id uuid,
    relation_type character varying,
    relation_to character varying,
    label character varying,
    certainty character varying
);
    DROP TABLE public.relations;
       public         heap    docuracy    false    2            �            1259    16722    source_labels    TABLE     l   CREATE TABLE public.source_labels (
    type_id uuid,
    label character varying,
    lang character(2)
);
 !   DROP TABLE public.source_labels;
       public         heap    docuracy    false            �            1259    16690    starts_ends    TABLE        CREATE TABLE public.starts_ends (
    timespan_id uuid,
    is_end boolean,
    iel character(1),
    owl character varying
);
    DROP TABLE public.starts_ends;
       public         heap    docuracy    false            �            1259    16686 	   timespans    TABLE     c   CREATE TABLE public.timespans (
    id uuid DEFAULT public.uuid_generate_v4(),
    when_id uuid
);
    DROP TABLE public.timespans;
       public         heap    docuracy    false    2            �            1259    16702    toponyms    TABLE     �   CREATE TABLE public.toponyms (
    id uuid DEFAULT public.uuid_generate_v4(),
    feature_id uuid,
    toponym character varying,
    lang character(2)
);
    DROP TABLE public.toponyms;
       public         heap    docuracy    false    2            �            1259    16715    types    TABLE     �   CREATE TABLE public.types (
    id uuid DEFAULT public.uuid_generate_v4(),
    feature_id uuid,
    identifier character varying,
    label character varying
);
    DROP TABLE public.types;
       public         heap    docuracy    false    2            �            1259    16679    whens    TABLE     �   CREATE TABLE public.whens (
    id uuid DEFAULT public.uuid_generate_v4(),
    parent_id uuid,
    label character varying,
    duraction character varying
);
    DROP TABLE public.whens;
       public         heap    docuracy    false    2                      0    16676    ccodes 
   TABLE DATA           6   COPY public.ccodes (property_id, country) FROM stdin;
    public          docuracy    false    205   �$                 0    16709 	   citations 
   TABLE DATA           B   COPY public.citations (parent_id, label, citation_id) FROM stdin;
    public          docuracy    false    211   �$                 0    16760 
   depictions 
   TABLE DATA           D   COPY public.depictions (feature_id, id, title, license) FROM stdin;
    public          docuracy    false    218   �$                 0    16754    descriptions 
   TABLE DATA           G   COPY public.descriptions (feature_id, value, lang, source) FROM stdin;
    public          docuracy    false    217   �$                 0    16665    features 
   TABLE DATA           &   COPY public.features (id) FROM stdin;
    public          docuracy    false    203   %                 0    16728 
   geometries 
   TABLE DATA           Q   COPY public.geometries (id, feature_id, node, edge, area, certainty) FROM stdin;
    public          docuracy    false    214   9%                 0    16741    links 
   TABLE DATA           =   COPY public.links (feature_id, type, identifier) FROM stdin;
    public          docuracy    false    215   V%                 0    16696    periods 
   TABLE DATA           5   COPY public.periods (when_id, name, uri) FROM stdin;
    public          docuracy    false    209   s%                 0    16669 
   properties 
   TABLE DATA           ;   COPY public.properties (id, feature_id, title) FROM stdin;
    public          docuracy    false    204   �%                 0    16747 	   relations 
   TABLE DATA           a   COPY public.relations (id, feature_id, relation_type, relation_to, label, certainty) FROM stdin;
    public          docuracy    false    216   �%                 0    16722    source_labels 
   TABLE DATA           =   COPY public.source_labels (type_id, label, lang) FROM stdin;
    public          docuracy    false    213   �%                 0    16690    starts_ends 
   TABLE DATA           D   COPY public.starts_ends (timespan_id, is_end, iel, owl) FROM stdin;
    public          docuracy    false    208   �%                 0    16686 	   timespans 
   TABLE DATA           0   COPY public.timespans (id, when_id) FROM stdin;
    public          docuracy    false    207   &                 0    16702    toponyms 
   TABLE DATA           A   COPY public.toponyms (id, feature_id, toponym, lang) FROM stdin;
    public          docuracy    false    210   !&                 0    16715    types 
   TABLE DATA           B   COPY public.types (id, feature_id, identifier, label) FROM stdin;
    public          docuracy    false    212   >&                 0    16679    whens 
   TABLE DATA           @   COPY public.whens (id, parent_id, label, duraction) FROM stdin;
    public          docuracy    false    206   [&             x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �            x������ � �     