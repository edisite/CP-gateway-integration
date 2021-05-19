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
			stsdmo="OFF"
			
			    TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_mo_disp_$TGL.log
				nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mo.xml >> $UILOG &
			
		fi
		
	if [ "$dmt" != "" ]; then
			stsdmt="ON"
		else
			stsdmt="OFF"
			
			    TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_mt_disp_$TGL.log
				nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mt.xml >> $UILOG &
			
		fi
		
	if [ "$ddr" != "" ]; then
			stsddr="ON"
		else
			stsddr="OFF"
			
			    TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_dr_disp_$TGL.log
				nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_dr.xml >> $UILOG &
						
		fi
		
	if [ "$tmo" != "" ]; then
			ststmo="ON"
		else
			ststmo="OFF"
				TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_mo_tran_$TGL.log
				nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mo.xml >> $UILOG &
		fi
	if [ "$tmt" != "" ]; then
			ststmt="ON"
		else
			ststmt="OFF"
			
				TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_mt_tran_$TGL.log
				nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mt.xml >> $UILOG &
			
		fi
		
	if [ "$tdr" != "" ]; then
			ststdr="ON"
		else
			ststdr="OFF"
			
			    TGL=`date +%Y-%m-%d_%H-%M-%S`
				UILOG=/opt/apps/6768/log/isat/gw_dr_tran_$TGL.log
				nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_dr.xml >> $UILOG &
		fi