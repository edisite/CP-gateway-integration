#!/bin/bash
mysql -h 10.1.1.94 -u edimc6768isat -p3disilit3SQL log<<EOFMYSQL
SELECT referer as url,"|", count(referer) AS total FROM koinisat WHERE date = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) and keyword ='vr' GROUP BY keyword, mcode, referer ORDER BY total DESC LIMIT 10;
EOFMYSQL