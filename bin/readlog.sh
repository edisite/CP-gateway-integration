#!/bin/bash

DRTTLL=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "heng" | awk -F "|" '{print $4}' | awk -F ":" '{print "|",$3,"|",$6,"|",$7}' | sort | uniq -c`

echo "$DRTTLL" > /var/www/ktes/filelog1.txt



