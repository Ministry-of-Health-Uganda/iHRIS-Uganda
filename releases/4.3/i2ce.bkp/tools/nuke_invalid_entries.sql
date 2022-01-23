DELETE FROM form_field WHERE form NOT IN (SELECT id FROM form);
DELETE FROM record WHERE form NOT IN (SELECT id FROM form);
DELETE FROM last_entry WHERE record NOT IN (SELECT id FROM record);
DELETE FROM entry WHERE record NOT IN (SELECT id FROM record);
