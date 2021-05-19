#!/bin/bash
mysql -h 10.1.1.94 -u edimc6768isat -p3disilit3SQL mc_6768_isat<<EOFMYSQL
SELECT gr.group_id as 'ID',gr.group_name as 'Group',count(gi.msisdn)as 'Total Member' FROM gr_member_in_queue gi inner join groups gr WHERE gr.group_id = gi.group_id group by gr.group_id;
EOFMYSQL