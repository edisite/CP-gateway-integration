#!/bin/bash
clear
g++ -lcurl -o transmitter_6768 transmitter_6768.cpp -I /usr/include/mysql /usr/lib/libmysqlclient_r.so.16

chmod 777 transmitter_6768
