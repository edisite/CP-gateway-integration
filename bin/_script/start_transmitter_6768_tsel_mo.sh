#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "transmitter_6768_tsel_mo.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mo transmitter for 6768 tsel already running
else
    echo mo transmitter for 6768 tsel - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/tsel/uilog_mo_tran_$TGL.log
    nohup /opt/apps/6768/bin/transmitter/transmitter_6768 /opt/apps/6768/bin/transmitter/transmitter_6768_tsel_mo.xml >> $UILOG &
fi