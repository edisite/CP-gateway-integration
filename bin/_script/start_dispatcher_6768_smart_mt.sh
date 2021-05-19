#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "dispatcher_6768_smart_mt.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mt dispatcher for 6768 smart already running
else
    echo mt dispatcher for 6768 smart - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/smart/error_mt_disp_$TGL.log
    nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_smart_mt.xml >> $UILOG &
fi