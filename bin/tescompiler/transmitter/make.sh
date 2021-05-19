#!/bin/bash
clear
g++ -lcurl -o transmitter_4263 transmitter_4263.cpp -I /usr/include/mysql /usr/lib64/mysql/libmysqlclient_r.so
chmod 777 transmitter_4263