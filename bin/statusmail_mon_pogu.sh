#!/bin/bash

/opt/apps/6768/bin/EmailMon_pogu.sh > /opt/apps/6768/bin/statusmail_mon_pogu.log

sleep 5

nohup /opt/apps/6768/bin/sendEmail -f crm-koin@m-solegra.co.id -t support@m-solegra.co.id -cc sapta_mon@m-solegra.co.id -cc abdul@mobilink-media.com -cc fransiska.mulyani@pogumobile.com -cc enny.muljono@pogumobile.com -cc yogi.verdinan@pogumobile.com -cc lindamonitoring@koinsms.mobi -u " Report 6768 Pogu" -s 202.78.201.121 -o message-file=/opt/apps/6768/bin/statusmail_mon_pogu.log &
