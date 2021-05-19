#!/bin/bash

results="$(mysql --user ${edi} -p${3disit3SQL} ${log} -Bse 'SELECT DISTINCT (keyword) AS keyword, mcode, referer,count(referer) AS total_url FROM koinisat WHERE date = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) and keyword ='vr' GROUP BY keyword, mcode, referer ORDER BY total_url DESC LIMIT 10')"

for rows in "${results[@]}"
do
  fieldA=`echo ${rows}| awk '{print $1}'`;
  fieldB=`echo ${rows}| awk '{print $2}'`;

  echo ${fieldA} ${fieldB};
done
EOFMYSQL