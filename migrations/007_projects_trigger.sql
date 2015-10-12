CREATE FUNCTION check_projects()
RETURNS trigger AS '
BEGIN
--   SELECT COUNT(*) INTO cc FROM projects WHERE accepted=1 AND users_id=NEW.users_id;
  IF EXISTS(SELECT id FROM projects WHERE accepted = 1 AND users_id = NEW.users_id) AND NEW.accepted = 1 THEN
    RAISE EXCEPTION 'multiple accepted projects!';
  END IF;
  RETURN NEW;
END' LANGUAGE 'plpgsql'