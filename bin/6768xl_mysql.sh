#!/bin/bash
mysql -h 10.1.1.94 -u edimc6768isat -p3disilit3SQL mc_6768_isat<<EOFMYSQL
SELECT gr.group_id as 'ID',gr.group_name as 'Group',count(gi.msisdn)as 'Total Member' FROM gr_member_in gi inner join groups gr WHERE gr.group_id = gi.group_id and gr.partner_name ='Koin' group by gr.group_id;

SELECT gr.group_id AS 'ID', gr.group_name AS 'Group', subGroup AS Sub, count( gi.msisdn ) AS 'Total' FROM gr_member_in gi INNER JOIN groups gr WHERE gr.group_id = gi.group_id AND subGroup != "0" GROUP BY gr.group_id, subGroup;

EOFMYSQL