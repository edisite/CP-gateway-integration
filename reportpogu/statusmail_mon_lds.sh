#!/bin/bash

/opt/apps/6768/bin/EmailMon_lds.sh > /opt/apps/6768/bin/statusmail_mon_lds.log

sleep 5

nohup /opt/apps/6768/bin/sendEmail -f crm-koin@m-solegra.co.id -t support@m-solegra.co.id -cc sapta_mon@m-solegra.co.id -cc linda@koinsms.mobi -cc aldrian@mobilink-media.com -cc abdul@mobilink-media.com -u " Report 6768 LDS" -s 202.78.201.121 -o message-file=/opt/apps/6768/bin/statusmail_mon_lds.log &
