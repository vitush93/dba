--
-- PostgreSQL database dump
--

-- Dumped from database version 9.4.5
-- Dumped by pg_dump version 9.4.0
-- Started on 2015-10-10 10:24:55

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 6 (class 2615 OID 16396)
-- Name: dba; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA dba;


ALTER SCHEMA dba OWNER TO postgres;

--
-- TOC entry 174 (class 3079 OID 11855)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner:
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2005 (class 0 OID 0)
-- Dependencies: 174
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner:
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = dba, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 172 (class 1259 OID 16397)
-- Name: users; Type: TABLE; Schema: dba; Owner: postgres; Tablespace:
--

CREATE TABLE users (
    id integer NOT NULL,
    username character varying(255),
    password character varying(255),
    email character varying(255)
);


ALTER TABLE users OWNER TO postgres;

--
-- TOC entry 2006 (class 0 OID 0)
-- Dependencies: 172
-- Name: COLUMN users.username; Type: COMMENT; Schema: dba; Owner: postgres
--

COMMENT ON COLUMN users.username IS '
';


--
-- TOC entry 173 (class 1259 OID 16400)
-- Name: user_id_seq; Type: SEQUENCE; Schema: dba; Owner: postgres
--

CREATE SEQUENCE user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_id_seq OWNER TO postgres;

--
-- TOC entry 2007 (class 0 OID 0)
-- Dependencies: 173
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: dba; Owner: postgres
--

ALTER SEQUENCE user_id_seq OWNED BY users.id;


--
-- TOC entry 1882 (class 2604 OID 16402)
-- Name: id; Type: DEFAULT; Schema: dba; Owner: postgres
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('user_id_seq'::regclass);


--
-- TOC entry 1884 (class 2606 OID 16410)
-- Name: PRIMARY; Type: CONSTRAINT; Schema: dba; Owner: postgres; Tablespace:
--

ALTER TABLE users
    ADD CONSTRAINT "PK_users" PRIMARY KEY(id);


--
-- TOC entry 1886 (class 2606 OID 16415)
-- Name: UNIQ_email; Type: CONSTRAINT; Schema: dba; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT "UNIQ_email" UNIQUE (email);


--
-- TOC entry 1888 (class 2606 OID 16413)
-- Name: UNIQ_username; Type: CONSTRAINT; Schema: dba; Owner: postgres; Tablespace:
--

ALTER TABLE ONLY users
    ADD CONSTRAINT "UNIQ_username" UNIQUE (username);


--
-- TOC entry 1889 (class 1259 OID 16416)
-- Name: email; Type: INDEX; Schema: dba; Owner: postgres; Tablespace:
--

CREATE INDEX email ON users USING btree (email);


--
-- TOC entry 1890 (class 1259 OID 16411)
-- Name: username; Type: INDEX; Schema: dba; Owner: postgres; Tablespace:
--

CREATE INDEX username ON users USING btree (username);


-- Completed on 2015-10-10 10:24:55

--
-- PostgreSQL database dump complete
--
