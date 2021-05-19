#!/bin/bash

/opt/apps/6768/bin/EmailMon.sh > /opt/apps/6768/bin/statusmail_mon.log

sleep 5

nohup /opt/apps/6768/bin/sendEmail -f crm-koin@m-solegra.co.id -t support@m-solegra.co.id -cc sapta_mon@m-solegra.co.id -cc abdul@mobilink-media.com -cc aldrian@mobilink-media.com -cc lindamonitoring@koinsms.mobi -cc ipul@sisintegrasi.com -cc judyna@m-solegra.co.id -u " Report 6768 isat" -s 10.1.1.19 -o message-file=/opt/apps/6768/bin/statusmail_mon.log &
