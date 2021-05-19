#!/bin/bash
isEmpty=`ps ax | grep -v "grep" | grep "dispatcher_6768_esia_mt.xml" | awk '{print $1;}'`
if [ "$isEmpty" != "" ]; then
    echo mt dispatcher for 6768 esia already running
else
    echo mt dispatcher for 6768 esia - starting...
    TGL=`date +%Y-%m-%d_%H-%M-%S`
    UILOG=/opt/apps/6768/log/esia/uilog_mt_disp_$TGL.log
    nohup /opt/apps/6768/bin/dispatcher/dispatcher_6768 /opt/apps/6768/bin/dispatcher/dispatcher_6768_esia_mt.xml >> $UILOG &
fi