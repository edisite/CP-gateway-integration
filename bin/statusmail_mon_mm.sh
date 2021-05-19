#!/bin/bash

/opt/apps/6768/bin/EmailMon_mm.sh > /opt/apps/6768/bin/statusmail_mon_mm.log

sleep 5

nohup /opt/apps/6768/bin/sendEmail -f crm-koin@m-solegra.co.id -t support@m-solegra.co.id -cc sapta_mon@m-solegra.co.id -cc niko@mimpiman.com -cc abdul@mobilink-media.com -cc lindamonitoring@koinsms.mobi -u " Report 6768 Mimpiman" -s 10.1.1.19 -o message-file=/opt/apps/6768/bin/statusmail_mon_mm.log &
