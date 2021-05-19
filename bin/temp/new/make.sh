#!/bin/bash
clear
g++ -lcurl -o transmitter_6768 transmitter_6768.cpp -I /usr/include/mysql /usr/lib64/mysql/libmysqlclient_r.so
chmod 777 transmitter_6768