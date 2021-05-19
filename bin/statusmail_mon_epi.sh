#!/bin/bash

/opt/apps/6768/bin/EmailMon_epi.sh > /opt/apps/6768/bin/statusmail_mon_epi1.log


sleep 5

nohup /opt/apps/6768/bin/sendEmail -f crm-koin@m-solegra.co.id -t edi@m-solegra.co.id -u " Report 6768 Epi" -s 10.1.1.19 -o message-file=/opt/apps/6768/bin/statusmail_mon_epi1.log &
