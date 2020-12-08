CREATE USER 'restFee'@'%' IDENTIFIED BY 'P@ssw0rd';
CREATE USER 'restFee'@'localhost' IDENTIFIED BY 'P@ssw0rd';

GRANT ALL ON restFee.* TO 'restFee'@'%';
GRANT ALL ON restFee_test.* TO 'restFee'@'%';
GRANT ALL ON restFee.* TO 'restFee'@'localhost';
GRANT ALL ON restFee_test.* TO 'restFee'@'localhost';

FLUSH PRIVILEGES;