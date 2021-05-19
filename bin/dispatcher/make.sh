#!/bin/bash
clear
g++ -lcurl -o dispatcher_6768 dispatcher_6768.cpp -I /usr/include/mysql /usr/lib/libmysqlclient_r.so.16.0.0
chmod 777 dispatcher_6768