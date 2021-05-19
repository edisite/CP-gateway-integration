#include <iostream>			// required for commands: cout
#include <sstream>			// required to cast integer to string
#include <fstream>			// required to open xml file in stream mode
#include <string.h>			// required for string manipulation - strcat, strcpy, bzero, bcopy
#include <stdlib.h>
#include "hkyqueue.h"

using namespace std;

hkyqueue gque;
hkyqueue gque2;

int main (int argc, char **argv)
{
	int i = 0;
	string s1, s2, s3, s4, s5, s6, s7, s8, s9, s10;
	string ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9, ss10;

	ss1 = "one";
	ss2 = "two";
	ss3 = "three";
	ss4 = "four";
	ss5 = "five";
	ss6 = "six";
	ss7 = "seven";
	ss8 = "eight";
	ss9 = "nine";
	ss10 = "ten";

	//--------------------------------------------------------------------------------------------------------------------------
	//procedural test
	cout << "PROCEDUREAL TEST" << "------------------------------------------------------------------" << endl;
	i = 0;
	{
		i = i + 1;
		cout << i << endl;
		gque.put(ss1);
		gque.put(ss1, ss2);
		gque.put(ss1, ss2, ss3);
		gque.put(ss1, ss2, ss3, ss4);
		gque.put(ss1, ss2, ss3, ss4, ss5);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9, ss10);
	}

	i = 0;
	while(gque.get(s1, s2, s3, s4, s5, s6, s7, s8, s9, s10))
	{
		i = i + 1;
		cout << i << ": " << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << ", " << s9 << ", " << s10 << endl;
	}

	//--------------------------------------------------------------------------------------------------------------------------
	// double queue test
	cout << "DOUBLE QUEUE TEST" << "------------------------------------------------------------------" << endl;
	i = 0;
	{
		i = i + 1;
		cout << i << endl;
		gque.put(ss1);
		gque2.put(ss1);
		gque.put(ss1, ss2);
		gque2.put(ss1, ss2);
		gque.put(ss1, ss2, ss3);
		gque2.put(ss1, ss2, ss3);
		gque.put(ss1, ss2, ss3, ss4);
		gque2.put(ss1, ss2, ss3, ss4);
		gque.put(ss1, ss2, ss3, ss4, ss5);
		gque2.put(ss1, ss2, ss3, ss4, ss5);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6);
		gque2.put(ss1, ss2, ss3, ss4, ss5, ss6);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7);
		gque2.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8);
		gque2.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9);
		gque2.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9);
		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9, ss10);
		gque2.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9, ss10);
	}

	
	cout << "DOUBLE QUEUE TEST - FIRST QUEUE" << "------------------------------------------------------------------" << endl;
	i = 0;
	while(gque.get(s1, s2, s3, s4, s5, s6, s7, s8, s9, s10))
	{
		i = i + 1;
		cout << i << ": " << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << ", " << s9 << ", " << s10 << endl;
	}

	cout << "DOUBLE QUEUE TEST - SECOND QUEUE" << "------------------------------------------------------------------" << endl;
	i = 0;
	while(gque2.get(s1, s2, s3, s4, s5, s6, s7, s8, s9, s10))
	{
		i = i + 1;
		cout << i << ": " << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << ", " << s9 << ", " << s10 << endl;
	}



	//--------------------------------------------------------------------------------------------------------------------------
	//while (1) // memory leak test
	cout << "MEMORY LEAK TEST" << "------------------------------------------------------------------" << endl;
	{
		i = i + 1;
		cout << i << endl;
		gque.put(ss1);
		gque.get(s1);
		cout << s1 << endl;

		gque.put(ss1, ss2);
		gque.get(s1, s2);
		cout << s1 << ", " << s2 << endl;

		gque.put(ss1, ss2, ss3);
		gque.get(s1, s2, s3);
		cout << s1 << ", " << s2 << ", " << s3 << endl;

		gque.put(ss1, ss2, ss3, ss4);
		gque.get(s1, s2, s3, s4);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5);
		gque.get(s1, s2, s3, s4, s5);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5, ss6);
		gque.get(s1, s2, s3, s4, s5, s6);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7);
		gque.get(s1, s2, s3, s4, s5, s6, s7);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8);
		gque.get(s1, s2, s3, s4, s5, s6, s7, s8);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9);
		gque.get(s1, s2, s3, s4, s5, s6, s7, s8, s9);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << ", " << s9 << endl;

		gque.put(ss1, ss2, ss3, ss4, ss5, ss6, ss7, ss8, ss9, ss10);
		gque.get(s1, s2, s3, s4, s5, s6, s7, s8, s9, s10);
		cout << s1 << ", " << s2 << ", " << s3 << ", " << s4 << ", " << s5 << ", " << s6 << ", " << s7 << ", " << s8 << ", " << s9 << ", " << s10 << endl;
	}
	

}