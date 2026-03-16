DROP FUNCTION IF EXISTS formfield_mult_map_componentize ;
DELIMITER |
CREATE FUNCTION formfield_mult_map_componentize ( map varchar(4000), forms varchar(4000), component varchar(255)   )
RETURNS	 varchar(4000) 
DETERMINISTIC
BEGIN
	DECLARE remap , chunk, form varchar(4000);
	DECLARE curpos, len, pos INT;
	SET len = LENGTH(map);
	SET remap = '';
	SET curpos =  1;
	WHILE curpos < len DO
	      SET pos = LOCATE(',',map,curpos);
	      IF (pos = 0) THEN
	      	 SET chunk = SUBSTRING(map,curpos);
	      	 SET curpos = len;
	      ELSE 
	      	 SET chunk = SUBSTRING(map,curpos, pos - curpos);
		 SET curpos = pos+1;
	      END IF;	      
	      SET pos = LOCATE('|',chunk);
	      IF (pos > 0) THEN
	      	 SET form = SUBSTRING(chunk,1,pos - 1);
		 IF ((form = forms) OR  ( forms LIKE CONCAT(form,',%')   ) OR    ( forms LIKE CONCAT('%,',form,',%') ) OR  ( forms LIKE CONCAT('%,',form)  )) THEN
		    SET chunk = CONCAT(chunk ,'@',component);
		 END IF;
	      END IF;
	      IF (LENGTH(remap) > 1) THEN
	      	 SET remap = CONCAT(remap,',',chunk);
              ELSE
		 SET remap = chunk;
	      END IF;
	END WHILE;
	RETURN remap;
END |
DELIMITER ;


-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1,formA,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,form1,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,formB,form1','local');

-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form2,formA,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,form2,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,formB,form2','local');

-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form3,formA,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,form3,formB','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,formB,form3','local');

-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1,form2,formA','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1,formA,form2','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','formA,form1,form2','local');

-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1,form2,form3','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1,form3,form2','local');
-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form3,form1,form2','local');


-- SELECT formfield_mult_map_componentize('form1|id1,form2|id2,form3|id3','form1','local');

