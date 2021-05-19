#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "transmitter_6768_isat_mo.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mo transmitter for 6768 isat already running
else
    echo mo transmitter for 6768 isat - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/isat/uilog_mo_tran_$TGL.log
    nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_isat_mo.xml >> $UILOG &
fi