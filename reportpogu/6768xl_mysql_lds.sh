#!/bin/bash
mysql -h 10.1.1.9 -u edi -p3disit3SQL mc_6768_isat<<EOFMYSQL
SELECT gr.group_id as 'ID',gr.partner_name as 'Partner' ,gr.group_name as 'Group',count(gi.msisdn)as 'Total Member' FROM gr_member_in gi inner join groups gr WHERE gr.group_id = gi.group_id  and gr.partner_name ='LDS' group by gr.group_id;
EOFMYSQL
