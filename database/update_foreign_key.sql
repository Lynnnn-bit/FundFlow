ALTER TABLE feedback
DROP FOREIGN KEY feedback_ibfk_1;

ALTER TABLE feedback
ADD CONSTRAINT feedback_ibfk_1
FOREIGN KEY (id_consultation) REFERENCES consultation(id_consultation)
ON DELETE CASCADE;
