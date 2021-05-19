#!/bin/bash

results="$(mysql -h 10.1.1.9 -u edi -p3disit3SQL log -Bse 'SELECT DISTINCT (keyword) AS keyword, mcode, referer,count(referer) AS total_url FROM koinisat WHERE date = DATE_FORMAT( NOW( ) , '%Y-%m-%d' ) and keyword ='vr' GROUP BY keyword, mcode, referer ORDER BY total_url DESC LIMIT 10';)"

cnt=$results[@]

for (( i=0 ; i<$cnt ; i++ ))
do
    echo "Record No. $i: $results[$i]"

    fieldA=$results[0];
    fieldB=${results[1]};
    fieldC=${results[2]};
    fieldD=${results[3]};

done

echo $fieldA $fieldB $fieldC $fieldD
EOFMYSQL