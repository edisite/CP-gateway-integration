#!/bin/bash
jam=`date +%k`

DATAMEMBER=`sh /opt/apps/6768/bin/6768xl_mysql_epi.sh`

echo "$DATAMEMBER" > /opt/apps/6768/bin/statusmail_mon_epi.log

mo=`more /opt/apps/6768/bin/statusmail_mon_epi.log | awk -F "|" '{print $2," | ",$1}'`

echo "Report 6768 Epi"
echo ""
echo "Jam :  $jam"
echo "----------------------------"

echo "The Best 10 HIT URL Keyword VR :"
echo ""
echo "$mo"