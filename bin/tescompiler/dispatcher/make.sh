#!/bin/bash
clear
g++ -lcurl -o dispatcher_4263 dispatcher_4263.cpp -I /usr/include/mysql /usr/lib64/mysql/libmysqlclient_r.so
chmod 777 dispatcher_4263
