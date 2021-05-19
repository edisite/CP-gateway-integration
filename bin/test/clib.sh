#!/bin/bash
clear
rm $1.o
rm $1.a
g++ -c $1.cpp
ar rvs $1.a $1.o