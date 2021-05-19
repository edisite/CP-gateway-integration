#!/bin/bash
dmo=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mo.xml" | grep -v "grep"`
dmt=`ps ax | grep "6768" | grep "dispatcher_6768_isat_mt.xml" | grep -v "grep"`
ddr=`ps ax | grep "6768" | grep "dispatcher_6768_isat_dr.xml" | grep -v "grep"`
tmo=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mo.xml" | grep -v "grep"`
tmt=`ps ax | grep "6768" | grep "/transmitter_6768_isat_mt.xml" | grep -v "grep"`
tdr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_dr.xml" | grep -v "grep"`
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
			nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mt.xml >> $UILOG &
			
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
			nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_dr.xml >> $UILOG &
			
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
			nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mt.xml >> $UILOG &

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
			nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_dr.xml >> $UILOG &
			
			tdrr=`ps ax | grep "6768" | grep "/transmitter_6768_isat_dr.xml" | grep -v "grep"`
			if [ "$tdrr" != "" ]; then
				ststdr="RESTART"
			else
				ststdr="OFF"
			fi	
		fi


echo "   --=APPS MONITOR=--"

echo ""
echo "GW - Disp MO		: $stsdmo"
echo "GW - Disp MT		: $stsdmt"
echo "GW - Disp DR		: $stsddr"
echo "GW - Tran MO		: $ststmo"
echo "GW - Tran MT		: $ststmt"
echo "GW - Tran DR		: $ststdr"

