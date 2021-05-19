#!/bin/bash

tgl=`date +%Y-%m-%d`
tgla=`date +%Y%m%d`
jam=`date +%k`

datadf=`df | grep "/dev/simfs" | awk -F " " '{print $5}'`

MO=`more /opt/apps/6768/log/isat/mc/MC_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "koin" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`
MOPULL=`more /opt/apps/6768/log/isat/mc/MC_PULL_$(date "+%Y-%m-%d").log | grep "Param MO" | grep "koin" | awk -F ":" '{print " = ",$4}' | sort | uniq -c`
MOULO=`more /opt/apps/6768/log/isat/mc/MC_ULO_$(date "+%Y-%m-%d").log | grep "Param MO" | awk -F "|" '{print "=",$6}' | sort | uniq -c`
MOOVERLIMIT=`more /opt/apps/6768/log/isat/mc/MC_PULL_$(date "+%Y-%m-%d").log | grep "OVERLIMIT" | awk -F "|" '{print $6}' | awk -F " " '{print "=",$2,$3,$4,$5}' | sort | uniq -c`
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
PUSHR1=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "push" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  2" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`

PULLNEW=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11}' | sort | uniq -c | grep ":  0" | awk -F ":" '{print $1, "=" , $3," : ",$2}'`
PULLR=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11," : "}' | sort | uniq -c | grep ":  1  :" | awk -F ":" '{print $1, "=" , $3," : ",$4, " : ",$2}'`
PULLR2=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11," : "}' | sort | uniq -c | grep ":  2  :" | awk -F ":" '{print $1, "=" , $3," : ",$4, " : ",$2}'`
PULLR3=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11," : "}' | sort | uniq -c | grep ":  3  :" | awk -F ":" '{print $1, "=" , $3," : ",$4, " : ",$2}'`
PULLR4=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "pull" | grep "koin" | awk -F ":" '{print ":",$5,":",$6,":",$11," : "}' | sort | uniq -c | grep ":  4  :" | awk -F ":" '{print $1, "=" , $3," : ",$4, " : ",$2}'`

MTTTL=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_rcv.log | grep "Param Log" | grep "koin" | wc -l`

#DC=`more /opt/apps/6768/log/xl/mt/mt_$(date "+%Y%m%d").log | grep "Retry" | awk -F "','" '{print $8}' | awk -F "'" '{print "  =  "$1}' | sort | uniq -c`

DRTTLL=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "koin" | wc -l`
DRTTL=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep "koin" | awk -F ":" '{print "=",$9}' | sort | uniq -c`

DR2=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep ": 2 ::" | grep "koin" | awk -F ":" '{print ":",$8,":",$5}'  | sort | uniq -c`
DR4=`more /opt/apps/6768/log/isat/mc/MC_DR_$(date "+%Y-%m-%d").log | grep "Param" | grep ": 4 ::" | grep "koin" | awk -F ":" '{print ":",$8,":",$5}'  | sort | uniq -c`

ACKRESP=`more /opt/apps/6768/log/isat/$(date "+%Y-%m-%d")_mt_tmt.log | grep "<PUSH><STATUS>" | awk -F "><" '{print " : ",$3}' | sort | uniq -c`


DATAMEMBER=`sh /opt/apps/6768/bin/6768xl_mysql.sh`
DATAMEMBER_queue=`sh /opt/apps/6768/bin/6768xl_mysql_queue_push.sh`

SCH=`ps ax | grep "ScheduleMessage.jar" | grep -v "grep"`
TRANS=`ps ax | grep "Transmitter.jar" | grep -v "grep"`
STR=`ps ax | grep "ScheduleMessageStory.jar" | grep -v "grep"`
DC=`ps ax | grep java | grep Downcharging.jar | grep -v "grep"`

dmo=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mo.xml" | grep -v "grep"`
dmt=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mt.xml" | grep -v "grep"`
ddr=`ps ax | grep "6768" | grep "dispatcher_6768_isat_dr.xml" | grep -v "grep"`
tmo=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mo.xml" | grep -v "grep"`
tmt=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt.xml" | grep -v "grep"`
tmtwp=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt_wap.xml" | grep -v "grep"`
tdr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_dr.xml" | grep -v "grep"`
tmb=`ps ax | grep "9818" | grep "/transmitter_9818_isat_mt.xml" | grep -v "grep"`
t9b=`ps ax | grep "9live" | grep "/transmitter_9live_isat_mt.xml" | grep -v "grep"`



if [ "$SCH" != "" ]; then
	stssch="ON"
else
	sh /home/dev/ScheduleMessage/start.sh
	
	SCHR=`ps ax | grep "ScheduleMessage.jar" | grep -v "grep"`
	if [ "$SCHR" != "" ]; then
		stssch="RESTART"
	else
		stssch="OFF"
	fi
	
fi

if [ "$TRANS" != "" ]; then
	ststrans="ON"
else	
	sh /home/dev/Transmitter/start.sh
	
	TRANSR=`ps ax | grep "Transmitter.jar" | grep -v "grep"`
	
	if [ "$TRANSR" != "" ]; then
		ststrans="RESTART"
	else
		ststrans="OFF"
	fi

fi

if [ "$STR" != "" ]; then
	stsstr="ON"
else
	sh /home/dev/ScheduleMessageStory/start.sh
	
	STRR=`ps ax | grep "ScheduleMessageStory.jar" | grep -v "grep"`
	if [ "$STRR" != "" ]; then
		stsstr="RESTART"
	else
		stsstr="OFF"
	fi
fi

if [ "$DC" != "" ]; then
	stsdc="ON"
else
	sh /home/dev/downcharging/start.sh
	
	SDC=`ps ax | grep java | grep Downcharging.jar | grep -v "grep"`
	if [ "$SDC" != "" ]; then
		stsdc="RESTART"
	else
		stsdc="OFF"
	fi
fi



	if [ "$dmo" != "" ]; then
			stsdmo="ON"
		else		
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_mo_disp_$TGL.log
			nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mo.xml >> $UILOG &
			dmor=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mo.xml" | grep -v "grep"`
			if [ "$dmor" != "" ]; then
				stsdmo="RESTART"
			else
				stsdmo="OFF"
			fi
				
		fi
		
	if [ "$dmt" != "" ]; then
			stsdmt="ON"
		else
			
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_mt_disp_$TGL.log
			#nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mt.xml >> $UILOG &
			
			dmtr=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mt.xml" | grep -v "grep"`
			if [ "$dmtr" != "" ]; then
				stsdmt="RESTART"
			else
				stsdmt="OFF"
			fi
		fi
	if [ "$ddr" != "" ]; then
			stsddr="ON"
		else
			
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_dr_disp_$TGL.log
			#nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_dr.xml >> $UILOG &
			
			ddrr=`ps ax | grep "6768" | grep "dispatcher_6768_isat_dr.xml" | grep -v "grep"`
			if [ "$ddrr" != "" ]; then
				stsddr="RESTART"
			else
				stsddr="OFF"
			fi
		fi
	if [ "$tmo" != "" ]; then
			ststmo="ON"
		else

			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_mo_tran_$TGL.log
			nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mo.xml >> $UILOG &
			
			tmor=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mo.xml" | grep -v "grep"`
			if [ "$tmor" != "" ]; then
				ststmo="RESTART"
			else
				ststmo="OFF"
			fi
			
		fi
	if [ "$tmt" != "" ]; then
			ststmt="ON"
		else

			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_mt_tran_$TGL.log
			#nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mt.xml >> $UILOG &

			tmtr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt.xml" | grep -v "grep"`
			
			if [ "$tmtr" != "" ]; then
				ststmt="RESTART"
			else
				ststmt="OFF"
			fi			
		fi
	if [ "$tdr" != "" ]; then
			ststdr="ON"
		else
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_dr_tran_$TGL.log
			#nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_dr.xml >> $UILOG &
			
			tdrr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_dr.xml" | grep -v "grep"`
			if [ "$tdrr" != "" ]; then
				ststdr="RESTART"
			else
				ststdr="OFF"
			fi	
		fi
	if [ "$tmb" != "" ]; then
			ststmb="ON"
		else
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/9818/log/isat/gw_apps_mt_tran_$TGL.log
			nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/9818/bin/transmitter/transmitter_9818_isat_mt.xml >> $UILOG &
			
			tdrr=`ps ax | grep "9818" | grep "/transmitter_9818_isat_mt.xml" | grep -v "grep"`
			if [ "$tdrr" != "" ]; then
				ststmb="RESTART"
			else
				ststmb="OFF"
			fi	
		fi
	if [ "$t9b" != "" ]; then
			stst9b="ON"
		else
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/9live/log/isat/gw_apps_mt_tran_$TGL.log
			nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/9live/bin/transmitter/transmitter_9live_isat_mt.xml >> $UILOG &
			
			tdrr=`ps ax | grep "9live" | grep "/transmitter_9live_isat_dr.xml" | grep -v "grep"`
			if [ "$tdrr" != "" ]; then
				stst9b="RESTART"
			else
				stst9b="OFF"
			fi	
		fi
	if [ "$tmtwp" != "" ]; then
			ststwp="ON"
		else
			TGL=`date +%Y-%m-%d_%H-%M-%S`
			UILOG=/opt/apps/6768/log/isat/gw_apps_mt_tran_$TGL.log
			#nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mt_wap.xml >> $UILOG &
			
			tdrr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt_wap.xml" | grep -v "grep"`
			if [ "$tdrr" != "" ]; then
				ststwp="RESTART"
			else
				ststwp="OFF || hidupkan di _script/start_transmitter_6768_isat_mt_wap.sh"
			fi	
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
echo ""
echo "$MOULO"
echo ""
echo "MO OVERLIMIT : "
echo "$MOOVERLIMIT"
echo ""

echo "================================="
echo "Total MT Hari ini : $MTTTL"
echo ""
echo "MT PULL :"
echo "$PULLNEW"
echo ""
echo "MT PUSH :"
echo "$PUSHNEW"
echo ""
echo "Retry PULL"
echo "$PULLR"
echo "$PULLR2"
echo "$PULLR3"
echo "$PULLR4"
echo "Retry PUSH Pertama"
echo "$PUSHR"
echo "Retry PUSH Kedua"
echo "$PUSHR1"
echo "================================="
echo "DR TOTAL : $DRTTLL"
echo "DR Detail :"
echo "$DRTTL"
echo ""
echo "DR2"
echo "$DR2"
echo "DR4"
echo "$DR4"
echo ""
echo ""
echo ""
echo "========================================="
echo "Jumlah Antrian Member yang akan di push :"
echo "Khusus Push Story ya :"
echo "$DATAMEMBER_queue"
echo ""
echo "========================================="
echo ""
echo "Response status ACK dari Indosat"
echo "$ACKRESP"
echo ""
echo "Space Hardisk  : $datadf"

echo "   --=APPS MONITOR=--"

echo "MC TRANS		: $ststrans"
echo "MC SCH			: $stssch"
echo "MC STR			: $stsstr"
echo "MC Downcharging		: $stsdc"
echo ""
echo "GW - Disp MO		: $stsdmo"
echo "GW - Disp MT		: $stsdmt"
echo "GW - Disp DR		: $stsddr"
echo "GW - Tran MO		: $ststmo"
echo "GW - Tran MT		: $ststmt"
echo "GW - Tran DR		: $ststdr"
echo ""
echo ""


echo "APPS 6768 Wappush"
#echo "GW - Tran MT 		: $ststwp"
echo ""
echo "APPS 9live"
echo "GW - Tran MT 		: $stst9b"
echo ""