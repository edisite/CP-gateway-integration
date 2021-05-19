#include <algorithm>		// required for commands: atoi, transform
#include <iostream>			// required for commands: cout
#include <iomanip>			// required for stringstream setprecision
#include <sstream>			// required to cast integer to string
#include <string.h>			// required for string manipulation - strcat, strcpy, bzero, bcopy
#include <time.h>			// required to get date and time
#include <math.h>			// used to calculate application lifespan
#include "myurl.h"

using namespace std;
string cast_dbl2str(double iParam)
{
	stringstream ss;
	ss << fixed << setprecision(3) << iParam;
	return ss.str();
}


void test()
{
	time_t dtmRaw;
	struct tm *dtmNow;
	char cDateCurrent[11];
	char cTimeCurrent[9];

	// get current date and time
	time(&dtmRaw);
	dtmNow = localtime(&dtmRaw);
	strftime(cDateCurrent, 11, "%Y-%m-%d", dtmNow);
	strftime(cTimeCurrent, 9, "%H:%M:%S", dtmNow);


	int n;
	myurl objURL;
	string sURL;
	string sURLResponse;
	
	sURL = "http://202.43.169.58:84/dummy.php";
	sURL = "http://www.javdream.com";
	
	double dd;	
	n = objURL.send_get(sURL, 1, sURLResponse, dd);

	string s = cast_dbl2str(dd);
	
	
	cout << cDateCurrent << " " << cTimeCurrent << " (" << n << ") ::" << s << ":: [" << sURLResponse << "]" << endl;
}

int main (int argc, char **argv)
{
	int i = 0;
	
	while (1)
	{
		i = i + 1;
		test();
		
	}
}

