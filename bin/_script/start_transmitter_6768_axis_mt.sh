#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "transmitter_6768_axis_mt.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mt transmitter for 6768 axis already running
else
    echo mt transmitter for 6768 axis - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/axis/uilog_mt_tran_$TGL.log
    nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_axis_mt.xml >> $UILOG &
fi