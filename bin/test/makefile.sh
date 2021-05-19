#!/bin/bash
gcc -Wall -fPIC -c hkyqueue.cpp
gcc -shared -Wl,-soname,libhkyqueue.so.1 -o libhkyqueue.so.1.0   hkyqueue.o

ln -sf /opt/apps/6768/bin/test/libhkyqueue.so.1.0 /opt/apps/6768/bin/test/libhkyqueue.so.1
ln -sf /opt/apps/6768/bin/test/libhkyqueue.so.1.0 /opt/apps/6768/bin/test/libhkyqueue.so

gcc -Wall -L/usr -L/usr/lib -L/usr/local -L/usr/include -L/opt/apps/6768/bin/test test.cpp -lhkyqueue -o test