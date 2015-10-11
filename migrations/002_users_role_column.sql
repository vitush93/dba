ALTER TABLE dba.users ADD COLUMN role CHARACTER VARYING(255);

CREATE INDEX role
ON dba.users
USING HASH
(role COLLATE pg_catalog."default");