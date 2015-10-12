-- Table: projects

-- DROP TABLE projects;

CREATE TABLE projects
(
  id serial NOT NULL,
  name character varying(255) NOT NULL,
  description text NOT NULL,
  accepted boolean NOT NULL DEFAULT false,
  created timestamp without time zone NOT NULL DEFAULT statement_timestamp(),
  CONSTRAINT "PK_projects" PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE projects
  OWNER TO postgres;

-- Index: idx_projects_created

-- DROP INDEX idx_projects_created;

CREATE INDEX idx_projects_created
  ON projects
  USING btree
  (id);

