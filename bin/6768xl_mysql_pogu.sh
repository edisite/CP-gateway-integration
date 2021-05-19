#!/bin/bash
mysql -h 10.1.1.94 -u edimc6768isat -p3disilit3SQL mc_6768_isat<<EOFMYSQL
SELECT gr.group_id as 'ID',gr.partner_name as 'Partner' ,gr.group_name as 'Group',count(gi.msisdn)as 'Total Member' FROM gr_member_in gi inner join groups gr WHERE gr.group_id = gi.group_id  and gr.partner_name ='Pogu' group by gr.group_id;
SELECT gr.group_id as 'ID',gr.group_name as 'Group',k.key_nm as 'Sub',count(gi.msisdn)as 'Total Member' FROM gr_member_in gi, groups gr,key2 k WHERE gr.group_id = gi.group_id and gi.order_id = k.order_id and gr.partner_name ='Pogu' group by k.order_id, gr.group_id;

EOFMYSQL