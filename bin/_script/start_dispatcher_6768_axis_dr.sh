#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "dispatcher_6768_axis_dr.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo dr dispatcher for 6768 axis already running
else
    echo dr dispatcher for 6768 axis - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/axis/uilog_dr_disp_$TGL.log
    nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_axis_dr.xml >> $UILOG &
fi