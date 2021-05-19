#!/bin/bash

tgl=`date +%Y-%m-%d`
tgla=`date +%Y%m%d`
jam=`date +%k`

datadf=`df | grep "/dev/simfs" | awk -F " " '{print $5}'`

MO=`more /opt/apps/6768/log/isat/mc/MC_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "koin" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`
MOPULL=`more /opt/apps/6768/log/isat/mc/MC_PULL_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "koin" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`
#MOHP=`more /opt/apps/6768/log/isat/mc/HP$(date "+%Y%m%d").log | grep "received" | wc -l`
MOHPD=`more /opt/apps/6768/log/isat/mc/HP$(date "+%Y%m%d").log | grep "key 1" | awk -F " " '{print " : "$5}' | sort | uniq -c`

#PUSHNEW=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "/koin/6768/isat_mt.php?" | grep "delivery_method=push" | awk -F "&" '{print $11}' | awk -F "=" '{print ": ",$2}' | sort | uniq -c`
#PULL=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "/koin/6768/isat_mt.php?" | grep "delivery_method=pull" | awk -F "&" '{print $11}' | awk -F "=" '{print ": ",$2}' | sort | uniq -c`
#PUSHNEW=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "handle/6768/isat_mt.php?" | grep "delivery_method=push" |grep "age=0" | awk -F "&" '{print $4,$11}' | awk -F "=" '{print $2,$3}' | awk -F " " '{print " : ",$1," : ",$3}' | sort | uniq -c`
#PUSHR=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "handle/6768/isat_mt.php?" | grep "delivery_method=push" |grep "age=1" | awk -F "&" '{print $4,$11}' | awk -F "=" '{print $2,$3}' | awk -F " " '{print " : ",$1," : ",$3}' | sort | uniq -c`
#PULLNEW=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "handle/6768/isat_mt.php?" | grep "delivery_method=pull" |grep "age=0" | awk -F "&" '{print $4,$11}' | awk -F "=" '{print $2,$3}' | awk -F " " '{print " : ",$1," : ",$3}' | sort | uniq -c`
#PULLR=`more /home/dev/Transmitter/logTransmitter/Transmitter.log | grep "handle/6768/isat_mt.php?" | grep "delivery_method=pull" |grep "age=1" | awk -F "&" '{print $4,$11}' | awk -F "=" '{print $2,$3}' | awk -F " " '{print " : ",$1," : ",$3}' | sort | uniq -c`

PUSHNEW=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  0" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`
PUSHR=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  1" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`

PULLNEW=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  0" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`
PULLR=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  1" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`


#DC=`more /opt/apps/6768/log/xl/mt/mt_$(date "+%Y%m%d").log | grep "Retry" | awk -F "','" '{print $8}' | awk -F "'" '{print "  =  "$1}' | sort | uniq -c`

DRTTL=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "koin" | awk -F ":" '{print "=",$9}' | sort | uniq -c`

DR2=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "koin" | awk -F ":" '{print ":",$8,":",$5,":",$9,":"}' | grep ":  2 " |  sort | uniq -c | awk -F ":" '{print $1,"=",$2,":",$3}'`
DR4=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "koin" | awk -F ":" '{print ":",$8,":",$5,":",$9,":"}' | grep ":  4 " |  sort | uniq -c | awk -F ":" '{print $1,"=",$2,":",$3}'`

DATAMEMBER=`sh /opt/apps/6768/bin/6768xl_mysql.sh`
SCH=`ps ax | grep "ScheduleMessage.jar" | grep -v "grep"`
TRANS=`ps ax | grep "Transmitter.jar" | grep -v "grep"`
STR=`ps ax | grep "ScheduleMessageStory.jar" | grep -v "grep"`

dmo=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mo.xml" | grep -v "grep"`
dmt=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mt.xml" | grep -v "grep"`
ddr=`ps ax | grep "6768" | grep "dispatcher_6768_isat_dr.xml" | grep -v "grep"`
tmo=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mo.xml" | grep -v "grep"`
tmt=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt.xml" | grep -v "grep"`
tdr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_dr.xml" | grep -v "grep"`

if [ "$SCH" != "" ]; then
	stssch="ON"
else
	stssch="OFF"
fi

if [ "$TRANS" != "" ]; then
	ststrans="ON"
else
	ststrans="OFF"
fi

if [ "$STR" != "" ]; then
	stsstr="ON"
else
	stsstr="OFF"
fi

	if [ "$dmo" != "" ]; then
			stsdmo="ON"
		else
			stsdmo="OFF"
			
		fi
		
	if [ "$dmt" != "" ]; then
			stsdmt="ON"
		else
			stsdmt="OFF"
			
		fi
	if [ "$ddr" != "" ]; then
			stsddr="ON"
		else
			stsddr="OFF"
			
		fi
	if [ "$tmo" != "" ]; then
			ststmo="ON"
		else
			ststmo="OFF"
			
		fi
	if [ "$tmt" != "" ]; then
			ststmt="ON"
		else
			ststmt="OFF"
			
		fi
	if [ "$tdr" != "" ]; then
			ststdr="ON"
		else
			ststdr="OFF"
			
		fi



echo "Report 6768 INDOSAT"

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
echo "Retry"
echo "$PULLR";
echo "$PUSHR"
echo "================================="
echo "DR :"
echo "$DRTTL"
echo ""
echo "DR2"
echo "$DR2"
echo "DR4"
echo "$DR4"
echo ""
echo ""
echo ""
echo "Space Hardisk  : $datadf"

echo "   --=APPS MONITOR=--"

echo "MC TRANS	: $ststrans"
echo "MC SCH		: $stssch"
echo "MC STR		: $stsstr"
echo ""
echo "Disp MO		: $stsdmo"
echo "Disp MT		: $stsdmt"
echo "Disp DR		: $stsddr"
echo "Tran MO		: $ststmo"
echo "Tran MT		: $ststmt"
echo "Tran DR		: $ststdr"

