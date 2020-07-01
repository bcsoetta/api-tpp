CREATE FUNCTION `getSequence`(
	sKode_sequence VARCHAR(32),
	sTahun INT
)
RETURNS INT(11)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
 DECLARE no INT;
 SET no = (SELECT sequence FROM sequence WHERE kode_sequence = sKode_sequence AND tahun = sTahun);
 IF no IS NULL THEN
  SET no = 1;
  INSERT INTO sequence(kode_sequence, tahun, sequence) VALUES(sKode_sequence, sTahun, no);
 ELSE
  SET no = no + 1;
  UPDATE sequence SET sequence.sequence = no WHERE kode_sequence = sKode_sequence AND tahun = sTahun;
 END IF;
 RETURN no;
END