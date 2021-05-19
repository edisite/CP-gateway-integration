#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "dispatcher_6768_isat_mo.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mo dispatcher for 6768 isat already running
else
    echo mo dispatcher for 6768 isat - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/isat/uilog_mo_disp_$TGL.log
    nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_isat_mo.xml >> $UILOG &
fi
