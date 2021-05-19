#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "transmitter_6768_hutch_dr.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo dr transmitter for 6768 hutch already running
else
    echo dr transmitter for 6768 hutch - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/hutch/uilog_dr_tran_$TGL.log
    nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_hutch_dr.xml >> $UILOG &
fi