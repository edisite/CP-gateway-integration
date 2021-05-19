#!/bin/bash
mysql -h 10.1.1.94 -u edimdw6768isat -p3disilit3SQL mdw6768_isat<<EOFMYSQL
update queue_isat_mo set flag ='0' WHERE flag ='54' or flag ='52';
update queue_isat_mt set flag ='0' WHERE flag ='54' or flag ='52';
update queue_isat_dr set flag ='0' WHERE flag ='54' or flag ='52';
EOFMYSQL