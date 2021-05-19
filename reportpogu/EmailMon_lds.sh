#!/bin/bash

tgl=`date +%Y-%m-%d`
tgla=`date +%Y%m%d`
jam=`date +%k`

datadf=`df | grep "/dev/simfs" | awk -F " " '{print $5}'`

MO=`more /opt/apps/6768/log/isat/mc/MC_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "lds" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`
MOPULL=`more /opt/apps/6768/log/isat/mc/MC_PULL_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "lds" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`


PUSHNEW=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  0" | awk -F ":" '{print $1, "=" , $2}'`
PUSHR1=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  1" | awk -F ":" '{print $1, "=" , $2}'`
PUSHR2=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  2" | awk -F ":" '{print $1, "=" , $2}'`

PULLNEW=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  0" | awk -F ":" '{print $1, "=" , $2}'`
PULLR1=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  1" | awk -F ":" '{print $1, "=" , $2}'`
PULLR2=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "lds" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  2" | awk -F ":" '{print $1, "=" , $2}'`
#DC=`more /opt/apps/6768/log/xl/mt/mt_$(date "+%Y%m%d").log | grep "Retry" | awk -F "','" '{print $8}' | awk -F "'" '{print "  =  "$1}' | sort | uniq -c`

DRTTL=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "lds" | awk -F ":" '{print "=",$9}' | sort | uniq -c`

DR2=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "lds" | awk -F ":" '{print ":",$8,":",$5,":",$9,":"}' | grep ":  2 " |  sort | uniq -c | awk -F ":" '{print $1,"=",$2,":",$3}'`
DR4=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "lds" | awk -F ":" '{print ":",$8,":",$5,":",$9,":"}' | grep ":  4 " |  sort | uniq -c | awk -F ":" '{print $1,"=",$2,":",$3}'`

DATAMEMBER=`sh /opt/apps/6768/bin/6768xl_mysql_lds.sh`


echo "Report 6768 LDS"

echo "Jam :  $jam"
echo "================================="
echo "$DATAMEMBER"
echo "================================="
echo "MO :"
echo "$MO"
echo ""
echo "$MOPULL"
echo ""
echo "$MOHPD"
echo "================================="
echo "MT PULL :"
echo "$PULLNEW"
echo ""
echo "MT PUSH :"
echo "$PUSHNEW"
echo ""
echo "Retry:"
echo "$PULLR1";
echo "$PUSHR1"
echo ""


echo "================================="
echo "DR :"
echo "$DRTTL"
echo ""
echo "DR2"
echo "$DR2"
echo "DR4"
echo "$DR4"