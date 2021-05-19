#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "transmitter_9818_isat_mt.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mt transmitter for 9818 isat already running
else
    echo mt transmitter for 9818 isat - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/9818/log/isat/uilog_mt_tran_$TGL.log
    nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/9818/bin/transmitter/transmitter_9818_isat_mt.xml >> $UILOG &
fi