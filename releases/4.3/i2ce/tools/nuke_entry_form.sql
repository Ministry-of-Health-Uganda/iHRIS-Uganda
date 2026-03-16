DELETE FROM form_field WHERE form IN 
    ( SELECT id FROM form WHERE name = 'FORMNAME' );
DELETE FROM entry WHERE record IN 
    ( SELECT id FROM record WHERE form IN 
        ( SELECT id FROM form WHERE name = 'FORMNAME' )
    );
DELETE FROM last_entry WHERE record IN 
    ( SELECT id FROM record WHERE form IN 
        ( SELECT id FROM form WHERE name = 'FORMNAME' )
    );
DELETE FROM record WHERE form IN
    ( SELECT id FROM form WHERE name = 'FORMNAME' );
DELETE FROM form WHERE name = 'FORMNAME';

#DELETE FROM field WHERE id NOT IN 
    #( SELECT field FROM form_field );
