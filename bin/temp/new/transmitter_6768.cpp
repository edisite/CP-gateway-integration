/*
COMPILE COMMAND
	g++ -o [exe name]    [cpp name]        -lcurl -I /usr/include/mysql /usr/lib/libmysqlclient_r.so
	g++ -o mysql2clients mysql2clients.cpp -lcurl -I /usr/include/mysql /usr/lib/libmysqlclient_r.so

LOCAL VARIABLE NAMING
	i - INTEGER
	u - UNSIGNED INTEGER
	s - STRING
	c - CHAR
	t - OBJECT/CLASS/DATABASE/FILE
	is - INTEGER/BOOLEAN
	do - INTEGER/BOOLEAN

	variables with single character as it's name, usually used as temporary or
	trivial information, and it is free naming.

LOCAL CONSTANT NAMING
	Same as variable naming but using capital letter for all characters

GLOBAL PREFIX
	GLOBAL VARIABLE PREFIX "g"
	GLOBAL CONSTANT PREFIX "G"
*/
#include <sys/stat.h>		// required to create directory
#include <sys/types.h>		// required to create directory
#include <dirent.h>			// required to open and read directory content
#include <algorithm>		// required for commands: atoi, transform
#include <iostream>			// required for commands: cout
#include <sstream>			// required to cast integer to string
#include <fstream>			// required to open xml file in stream mode
#include <string.h>			// required for string manipulation - strcat, strcpy, bzero, bcopy
#include <time.h>			// required to get date and time
#include <pthread.h>		// required to create multi thread
#include <mysql.h>			// required for mysql operation
#include <mysqld_error.h>	// required for mysql operation
#include <sys/types.h>		// required by socket
#include <sys/socket.h>		// required by socket
#include <netinet/in.h>		// required by socket
#include <netdb.h>			// required by socket - gethostbyname
#include <math.h>			// used to calculate application lifespan
#include <stdlib.h>			// currently used only to call exit function
#include "myurl.h"
#include "hkyqueue.h"
#include "hkycollection.h"

using namespace std;

// GLOBAL CONSTANTS ==============================================================================================================
const bool	GDO_GET_QUEUE = true;
const bool	GDO_SEND_URL = true;
const bool	GDO_PROCESS_RESPONSE = true;
const bool	GDO_LOAD_SETTING = true;
const bool	GDO_INITIALIZE_DB = true;
const bool	GDO_START_MONITORING = true;
const bool	GDO_CLEANUP_PREVIOUS = true;

const int	GIMAXOKRESPONSE		= 10;		// maximum number of responses from sendind data, that define it was a success
const int	GIMAXRESPONSEFIELD	= 5;		// maximum number of response field need to be save into database
const int	GIMAXSQLPULLNPUSH	= 10;		// maximum number of queries to get records from database - pull records
const int	GIMAXTRYWRITE2LOG	= 5;
const int	GIMAXTRYSENDURL		= 5;

// GLOBAL VARIABLES ==============================================================================================================
pthread_mutex_t		tmutex = PTHREAD_MUTEX_INITIALIZER;	// Thread locking variable

bool	gisSockON;			// hold running status of subroutine socket_open
int		giiniportno;		// App setting variable, application communication port number

int 	giinisleep1;		// App setting variable, sleep for a while if some command need to retry
int		giinisleep2;		// App setting variable, sleep for a while longer if some condition need to idle

int		giinitimeoutpull;	// App setting variable, number of seconds to declare thread is freezing, pull
int		giinitimeoutpush;	// App setting variable, number of seconds to declare thread is freezing, push
int		giinitimeoutexec;	// App setting variable, number of seconds to declare thread is freezing, updater

int		giini1thread1log;	// App setting variable, 0 or 1, 1 mean one thread one log file

int		giiniresok_cnt;
string	gsiniresok[GIMAXOKRESPONSE];

string	gsinidb1addr;		// App setting variable, MySQL database address
string	gsinidb1name;		// App setting variable, MySQL database name
string	gsinidb1tabl;		// App setting variable, MySQL table name
string	gsinidb1user;		// App setting variable, MySQL user name
string	gsinidb1pswd;		// App setting variable, MySQL user password

string	gsinidirlog;		// App setting variable, directory to log application's errors - also to store thread's log
string	gsinilogptn;		// App setting variable, log file's name common pattern
string	gsinilogflo;		// App setting variable, log flow descriptor

string	gsinidefaulturl = ""; // default url address to transmit data which keyword is unknown or not listed

string	sStampStarted;		// monitoring, store date and time application started ???
time_t	gtmLived;			// monitoring, store amount of seconds from application started ???

hkyqueue	gquePULL;
hkyqueue	gquePUSH;
hkyqueue	gqueEXEC;

int		giinipull_sql_cnt;
int		giinipull_thread_cnt;
string	gsinipull_sql[GIMAXSQLPULLNPUSH];

int		giinipush_sql_cnt;
int		giinipush_thread_cnt;
string	gsinipush_sql[GIMAXSQLPULLNPUSH];

bool	gTerminateApplication;

MYSQL gdb1OBJ;			// mysql structure
MYSQL *gdb1CN;			// mysql connection

MYSQL gdb2OBJ;			// mysql structure
MYSQL *gdb2CN;			// mysql connection

struct thread_info
{
	pthread_t	id;				// thread id returned from pthread_create function
	string		name;			// thread name for log file naming
	string		type;			// thread queue type (pull or push)
	int			number;			// thread sequence number
	time_t		check;
};

struct thread_info gthinfoEXEC;
struct thread_info *gthinfoPULL;
struct thread_info *gthinfoPUSH;

hkycollection gcolKwTransmit;
hkycollection gcolKwResponse;

int		giiniressave2fieldcnt = 0;
bool	gbiniressaveenable = false;
string	gsiniressave2table = "";
string	gsiniressave2field[GIMAXRESPONSEFIELD];
string	gsiniressavexmltag[GIMAXRESPONSEFIELD];

bool	gbinifwdenable = false;

// FUNCTION, CONVERT TYPES #######################################################################################################
int cast_str2int(string sParam)
{
	int irc;
	try { irc = atoi(sParam.c_str()); } catch (int e) { irc = 0; }
	return irc;
}

string cast_int2str(int iParam)
{
	stringstream ss;
	ss << iParam;
	return ss.str();
}

string cast_str2lower(string sParam)
{
	std::transform(sParam.begin(), sParam.end(), sParam.begin(), ::tolower);
	return sParam;
}

string cast_str2upper(string sParam)
{
	std::transform(sParam.begin(), sParam.end(), sParam.begin(), ::toupper);
	return sParam;
}

bool cast_str2bool(string sParam)
{
	std::transform(sParam.begin(), sParam.end(), sParam.begin(), ::tolower);
	if (sParam == "yes") return true;		// to gain faster code this code must be written in order of mostly used
		if (sParam == "no") return false;	// example: 'no' is commonly used rather than '0', so evaluate 'no' first
		if (sParam == "0") return false;
		if (sParam == "") return false;
	if (sParam == "1") return true;
	if (sParam == "ok") return true;
	if (sParam == "true") return true;
	if (sParam == "-1") return true;
	return false;
}

string trimTABCRLF(string sParam)
{
	sParam.erase(remove(sParam.begin(), sParam.end(), '\t'), sParam.end());
	sParam.erase(remove(sParam.begin(), sParam.end(), '\n'), sParam.end());
	sParam.erase(remove(sParam.begin(), sParam.end(), '\r'), sParam.end());
	return sParam;
}

string cast_strquote(string sParam)
{
	int i, n;
	string a;
	string sResult = "";
	n = sParam.length();
	for (i = 0; i < n; i++)
	{
		a = sParam.at(i);
		if (a == "'") { a = "''"; }
		sResult = sResult + a;
	}
	return sResult;
}

// FUNCTION, READ XML FILE AND RETURN ITS CONTENT ################################################################################
string xml_read_file(char *cFilename)
{
	ifstream tfile;
	string strLine;
	string strRet =  "";
	tfile.open(cFilename);
		if (tfile.is_open())
		{
			while (!tfile.eof())
			{
				getline(tfile, strLine);
				strRet = strRet + strLine;
			}
		}
	tfile.close();
	return strRet;
}

// FUNCTION, EXTRACT XML FIELD FOR SPECIFIED KEY #################################################################################
string xml_get_value(string sSource, string sKey)
{
	int n;
	size_t a, b;

	string sUPPER = sSource;
	transform(sUPPER.begin(), sUPPER.end(), sUPPER.begin(), ::toupper);

	transform(sKey.begin(), sKey.end(), sKey.begin(), ::toupper);
	string sSeek = "<" + sKey + ">";

	a = sUPPER.find(sSeek);
	if (a == string::npos) { return ""; }
	n = int(a) + sSeek.length();

	b = sUPPER.find("</" + sKey + ">", n);
	return (b == string::npos) ? sSource.substr(n) : sSource.substr(n, int(b) - n);
}

// SUBROUTINE, WRITE MESSAGES INTO DAILY LOG FILE ################################################################################
void log_write(string spThread, string spTrxID, string spHead, string spMsg)
{
	string spCRLF = "";
	time_t dtmRaw;
	struct tm *dtmNow;
	char cDateCurrent[11];
	char cTimeCurrent[9];
	char cName[255];
	FILE *tfileLog;
	string sPath;
	char cc[3];
	int dd;
	int i;
	string sTemp;

	// make message to write have no carriage return, line feed and tabs
	spMsg = trimTABCRLF(spMsg);
	if (spTrxID == "\n") { spMsg = spMsg + spTrxID; spTrxID = ""; }
	spMsg = spMsg + spCRLF;

	// get current date and time
	time(&dtmRaw);
	dtmNow = localtime(&dtmRaw);
	strftime(cDateCurrent, 11, "%Y-%m-%d", dtmNow);
	strftime(cTimeCurrent, 9, "%H:%M:%S", dtmNow);

	// create filename that is thread-daily based
	strftime(cName, 255, gsinilogptn.c_str(), dtmNow);
	if (giini1thread1log == 0)
	{
		sPath = gsinidirlog + cName;
	}
	else
	{
		sPath = gsinidirlog + cName + "_" + spThread;
	}

	// open log file, write the message and close it
	for (i = 0; i < GIMAXTRYWRITE2LOG; i++) // sometime error happen accessing the directory when date changed, so try it 5 times
	{
		tfileLog = fopen(sPath.c_str(), "a");
		if (tfileLog != NULL)
		{
			fprintf(tfileLog, "%s %s|%s|%-8s|%-8s|%-15s|%s\n", cDateCurrent, cTimeCurrent, gsinilogflo.c_str(), spThread.c_str(), spTrxID.c_str(), spHead.c_str(), spMsg.c_str());
			fclose(tfileLog);
			break;
		}
		else
		{
			cout << "#ERR: [" << i << "] application don't have enough permission to access directory or log file" << endl;
			cout << "#ERR: [" << i << "] " << sPath << endl;
			usleep(giinisleep1);
		}
	}
}

// FUNCTION, MAKE DATABASE CONNECTION, MYSQL #####################################################################################
int db_connect(int index)
{
	int i;
	if (index == 1)
	{
		// initialize database connection structure
		try { mysql_close(gdb1CN); } catch (int e) {}

		// try to make database connection
		for(i = 0; i < 5; i++)
		{
			gdb1CN = mysql_real_connect(&gdb1OBJ, gsinidb1addr.c_str(), gsinidb1user.c_str(), gsinidb1pswd.c_str(), gsinidb1name.c_str(), 0, NULL, 0);
			if (gdb1CN) { return 0; } else { usleep(giinisleep2); }
		}
		return -1;
	}
	else
	{
		// initialize database connection structure
		try { mysql_close(gdb2CN); } catch (int e) {}

		// try to make database connection
		for(i = 0; i < 5; i++)
		{
			gdb2CN = mysql_real_connect(&gdb2OBJ, gsinidb1addr.c_str(), gsinidb1user.c_str(), gsinidb1pswd.c_str(), gsinidb1name.c_str(), 0, NULL, 0);
			if (gdb2CN) { return 0; } else { usleep(giinisleep2); }
		}
		return -1;
	}
}

// FUNCTION, EXECUTE QUERY, MYSQL ################################################################################################
int db_query(int index, string sSQL)
{
	if (index == 1)
	{
		// if query failed, try once more
		if (mysql_query(gdb1CN, sSQL.c_str()) != 0)
		{
			// refresh database connection, if failed - terminate application
			if (db_connect(1) != 0) { return -1; }

			// try to execute query again, if failed - terminate application
			if (mysql_query(gdb1CN, sSQL.c_str()) != 0) { return -1; }
		}
	}
	else
	{
		// if query failed, try once more
		if (mysql_query(gdb2CN, sSQL.c_str()) != 0)
		{
			// refresh database connection, if failed - terminate application
			if (db_connect(2) != 0) { return -1; }

			// try to execute query again, if failed - terminate application
			if (mysql_query(gdb2CN, sSQL.c_str()) != 0) { return -1; }
		}
	}
	return 0;
}

// THREADING, TRANSMITTER ########################################################################################################
void *thread_transmitter(void *arg)
{
	int i, j, n, m;
	bool isSuccess;
	size_t t;
	string sSQL, sTmp, s1, s2, s3;
	string sTHR, sDNA, sTRXID, sMSISDN, sSID, sKeyword, sMTD, sURL;
	string sDATA, sURLResponse, sStatus, sSTS, sFwdResponse;
	string sFwdRcpName, sFwdMethod, sFwdAddress;
	myurl objURL;
	struct thread_info thinfo = *(struct thread_info *)arg;

	while (gTerminateApplication == false)
	{
		// get address to send from queue, if failed (mostly because of queue is empty), sleep long, process next queue
		if (GDO_GET_QUEUE)
		{
			// no need to reset these variables since it will be reset when calling queue's get method
			// sTHR, sDNA, sTRXID, sKeyword, sURL, sMTD
			pthread_mutex_lock(&tmutex);
				if (thinfo.type == "pull")
				{
					isSuccess = gquePULL.get(sTHR, sDNA, sTRXID, sMSISDN, sSID, sKeyword, sMTD, sURL);
				}
				else
				{
					isSuccess = gquePUSH.get(sTHR, sDNA, sTRXID, sMSISDN, sSID, sKeyword, sMTD, sURL);
				}
			pthread_mutex_unlock(&tmutex);

			if (isSuccess == true || sDNA == "" || sTRXID == "" || sURL == "")
			{
				usleep(giinisleep1);
				goto posNextQueue;
			}
			sMTD = cast_str2lower(sMTD);
		}

		gcolKwTransmit.item(sKeyword, s1, sMTD, s3);

		// send url to partner, if failed (mostly because of response time out), sleep long, process next queue
		if (GDO_SEND_URL)
		{
			// SEND WITH HTTP POST METHOD ########################################################################################
			if (sMTD == "post" || sMTD == "1")
			{
				log_write(thinfo.name, sTRXID, "begin", "post");
				log_write(thinfo.name, sTRXID, "  trx id", sTRXID);
				log_write(thinfo.name, sTRXID, "  keyword", sKeyword);
				try { sTmp = sURL.substr(0, 7); } catch (int e) { sTmp = ""; }
				transform(sTmp.begin(), sTmp.end(), sTmp.begin(), ::tolower);

				// if url address found
				if (sTmp == "http://")
				{
					t = sURL.find("?"); // check if url contain url's parameters
					if (t == string::npos) // if url does not contain parameters then do nothing since the url part already contain url only
					{
						// do nothing
					}
					else // if url contain parameters then separate it into part that contain url only and parameters only
					{
						sTmp = sURL;
						sURL = sTmp.substr(0, int(t));

						try
						{
							sDATA = sTmp.substr(int(t) + 1);
						}
						catch (int e)
						{
							log_write(thinfo.name, sTRXID, "  error", "parameters delimiter found but extracting parameters failed");
							log_write(thinfo.name, sTRXID, "  error", "substr(" + cast_int2str(int(t) + 1) + ")");
							log_write(thinfo.name, sTRXID, "  error", sTmp);
							log_write(thinfo.name, sTRXID, "  solution", "set parameters to empty string and try to send it anyway");
						}
					}
				}
				else // if url address not found
				{
					log_write(thinfo.name, sTRXID, "  post", "url not found");
					sDATA = sURL; // then treat url received from database as data, and set url address from xml configuration based on keyword

					log_write(thinfo.name, sTRXID, "  post", "get url from keyword [" + sKeyword + "]");
					gcolKwTransmit.item(sKeyword, s1, s2, s3);		// get url address based on MO keyword
					log_write(thinfo.name, sTRXID, "  post", "url found by keyword [" + s1 + "][" + s2 + "][" + s3+ "]");

					if (s3 == "") // if url address not found then set to default url
					{
						log_write(thinfo.name, sTRXID, "  post", "getting default url");
						sTmp = gsinidefaulturl;
					}
					sURL = s3;
					log_write(thinfo.name, sTRXID, "  post", "url [" + sURL + "]");
				}

				log_write(thinfo.name, sTRXID, "  send post", sURL);
				log_write(thinfo.name, sTRXID, "  post data", sDATA);
				for (i = 0; i < GIMAXTRYSENDURL; i++)
				{
					n = objURL.post(sURL, sDATA, sURLResponse);
					if (n == 0 && sURLResponse != "") { break; }
					usleep(giinisleep2);
				}
				if (n == 0)
				{
					log_write(thinfo.name, sTRXID, "  response", sURLResponse);
				}
				else
				{
					log_write(thinfo.name, sTRXID, "  error", "[51] cannot open specified url");
					if (sURLResponse != "")
					{
						log_write(thinfo.name, sTRXID, "  error", sURLResponse);
					}
					log_write(thinfo.name, sTRXID, "  solution", "url will be send again when date changed");
					sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 51, flagdesc = 'cannot open specified url' WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, "counted as error", sTRXID, sSQL);
					usleep(giinisleep2);
					goto posNextQueue;
				}
			}
			else // SEND WITH HTTP GET METHOD ####################################################################################
			{
				log_write(thinfo.name, sTRXID, "begin", "get");
				log_write(thinfo.name, sTRXID, "  trx id", sTRXID);
				log_write(thinfo.name, sTRXID, "  keyword", sKeyword);

				try { sTmp = sURL.substr(0, 7); } catch (int e) { sTmp = ""; }
				transform(sTmp.begin(), sTmp.end(), sTmp.begin(), ::tolower);
				if (sTmp == "http://")
				{
					// if given url is already a complete url then do nothing
				}
				else
				{
					// if given url is not complete then complete it using defined url based on MO keyword
					gcolKwTransmit.item(sKeyword, s1, s2, s3);	// get url address based on MO keyword
					if (s3 == "") { s3 = gsinidefaulturl; }		// if url address not found then set to default url
					sURL = s3 + sURL;							// set a complete url with its parameters
				}

				log_write(thinfo.name, sTRXID, "  send get", sURL);
				for (i = 0; i < GIMAXTRYSENDURL; i++)
				{
					n = objURL.open(sURL, sURLResponse);
					if (n == 0 && sURLResponse != "") { break; }
					usleep(giinisleep2);
				}
				if (n == 0)
				{
					log_write(thinfo.name, sTRXID, "  response", sURLResponse);
				}
				else
				{
					log_write(thinfo.name, sTRXID, "  error", "[52] cannot open specified url");
					log_write(thinfo.name, sTRXID, "  solution", "url will be send again when date changed");
					sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 52, flagdesc = 'cannot open specified url' WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, "counted as error", sTRXID, sSQL);
					usleep(giinisleep2);
					goto posNextQueue;
				}
			}
		}

		// process response
		if (GDO_PROCESS_RESPONSE)
		{
			// if response is empty
			if (sURLResponse == "")
			{
				log_write(thinfo.name, sTRXID, "  error", "[53] response is empty");
				log_write(thinfo.name, sTRXID, "  solution", "url will be send again when date changed");
				sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 53, flagdesc = 'response is empty' WHERE dna = '" + sDNA + "' LIMIT 1";
				gqueEXEC.put(thinfo.name, "counted as error", sTRXID, sSQL);
			}
			// if response is not empty
			else
			{
				n = 0;
				sStatus = trimTABCRLF(cast_str2upper(sURLResponse));
				sSTS = "`" + sStatus + "`";
				for (i = 0; i < giiniresok_cnt; i++) { if (sSTS.find(gsiniresok[i + 1]) != string::npos) { n = 1; break; } }

				// response is not empty but does not contain success identifier
				if (n == 0)
				{
					log_write(thinfo.name, sTRXID, "  error", "[54] response is invalid [" + sStatus + "]");
					log_write(thinfo.name, sTRXID, "  solution", "manually reset error to process this record again");
					sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 54, flagdesc = 'response is invalid [" + cast_strquote(sStatus) + "]' WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, "counted as error", sTRXID, sSQL);
				}
				else
				{
					log_write(thinfo.name, sTRXID, "  success", "response is success");
					sSQL = "DELETE FROM " + gsinidb1tabl + " WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, "counted as success", sTRXID, sSQL);

					// if response is success, check if there are response element that must be saved into database
					if (gbiniressaveenable)
					{
						s1 = "";
						for (i = 0; i < giiniressave2fieldcnt; i++)
						{
							j = i + 1;
							s1 = s1 + gsiniressave2field[j] + " = '" + xml_get_value(sURLResponse, gsiniressavexmltag[j]) + "'";
							if (j < giiniressave2fieldcnt) { s1 = s1 + ","; }
						}
						sSQL = "UPDATE " + gsiniressave2table + " SET " + s1 + " WHERE dna = '" + sDNA + "' LIMIT 1";
						gqueEXEC.put(thinfo.name, "counted as success", sTRXID, sSQL);
						log_write(thinfo.name, sTRXID, "  save response", sSQL);
					}
				}

				// update thread info about last
				if (thinfo.type == "pull") { time(&gthinfoPULL[thinfo.number].check); } else { time(&gthinfoPUSH[thinfo.number].check); }

				// check if response must be forwarded
				if (gbinifwdenable)
				{
					gcolKwResponse.item(sKeyword, sFwdRcpName, sFwdMethod, sFwdAddress);

					if (sFwdMethod == "post")
					{
						sURL = sFwdAddress + "?trxid=" + sTRXID + "&msisdn=" + sMSISDN + "&serviceid=" + sSID + "&raw=" + curl_easy_escape(NULL, sURLResponse.c_str(), 0);
					}
					else
					{
						t = sURL.find("?");
						if (t == string::npos)  // in case parameters not found then forward it using default parameters only
						{
							sURL = sFwdAddress + "?trxid=" + sTRXID + "&msisdn=" + sMSISDN + "&serviceid=" + sSID + "&raw=" + curl_easy_escape(NULL, sURLResponse.c_str(), 0);
						}
						else	// if parameters found then forward those parameters along with default parameters
						{
							sURL = sFwdAddress + "?trxid=" + sTRXID + "&msisdn=" + sMSISDN + "&serviceid=" + sSID + "&raw=" + curl_easy_escape(NULL, sURLResponse.c_str(), 0);
						}
					}
					sFwdResponse = "";
					log_write(thinfo.name, sTRXID, "  fwd response", sURL);
					for (i = 0; i < GIMAXTRYSENDURL; i++)
					{
						m = objURL.open(sURL, sFwdResponse);
						if (m == 0 && sFwdResponse != "") { break; }
						usleep(giinisleep2);
					}
					if (m == 0)
					{
						log_write(thinfo.name, sTRXID, "  response fwd", sFwdResponse);
					}
					else
					{
						log_write(thinfo.name, sTRXID, "error", "[55] cannot forward response as dr");
						log_write(thinfo.name, sTRXID, "solution", "admin must manually forward url below");
						log_write(thinfo.name, sTRXID, "url", sURL);
						sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 55, flagdesc = 'cannot forward response as dr' WHERE dna = '" + sDNA + "' LIMIT 1";
						gqueEXEC.put(thinfo.name, "error", sTRXID, sSQL);
						usleep(giinisleep2);
						goto posNextQueue;
					}
				}
			}
		}

		// position to skip current queue and process next queue
		posNextQueue:;
	}
	pthread_exit(0);
}

// THREADING, UPDATER ############################################################################################################
void *thread_updater(void *arg)
{
	bool isSuccess;
	string sTHR, sLOG, sSQL, sTRXID;
	string s4, s5, s6, s7, s8, s9, s10;
	
	while (gTerminateApplication == false)
	{
		// get queue
		isSuccess = gqueEXEC.get(sTHR, sLOG, sTRXID, sSQL, s5, s6, s7, s8, s9, s10);
		cout << sTHR << "|" << sLOG << "|" << sTRXID << "|" << sSQL << "|" << s5 << "|" << s6 << "|" << s7 << "|" << s8 << "|" << s9 << "|" << s10 << endl;
		if (isSuccess == false) { usleep(giinisleep2); goto posNextQueue; }
		log_write(sTHR, sTRXID, "  execute", sTHR + "|" + sLOG + "|" + sTRXID + "|" + sSQL);

		// execute query retrieved from queue
		if (db_query(1, sSQL) == 0)
		{
			log_write(sTHR, sTRXID, "finish", sLOG);
			time(&gthinfoEXEC.check);
		}
		else // if query failed then terminate application
		{
			log_write(sTHR, sTRXID, "  error", "query failed");
			log_write(sTHR, sTRXID, "  error", "[" + cast_int2str(mysql_errno(gdb1CN)) + "] " + mysql_error(gdb1CN));
			log_write(sTHR, sTRXID, "  solution", "terminate application");
			log_write(sTHR, sTRXID, "finish", "counted as error\n");
			gTerminateApplication = true;
			pthread_exit(0);
		}

		// position to skip current queue and process next queue
		posNextQueue:;
	}
	pthread_exit(0);
}

// SOCKET SUBROUTINE #############################################################################################################
void *socket_open(void *arg)
{
	int i, n;
	int ihndServer;
	int ihndClient;
	char buffer[256];

	string sKey;
	string sReply;
	int iReply;

	socklen_t tlenClient;
	struct sockaddr_in tsckServer;
	struct sockaddr_in tsckClient;

	// try to open the socket
	while (1)
	{
		// exit point
		if (gisSockON == false)
		{
			cout << "off signal received, closing application" << endl;
			log_write("app", "monitor", "success", "off signal received, closing application");
			return NULL;
		}

		// initialize socket
		ihndServer = socket(AF_INET, SOCK_STREAM, 0);
		if (ihndServer < 0)
		{
			cout << "#ERR: cannot initialize monitoring port " << giiniportno << endl;
			log_write("app", "monitor", "error", "cannot initialize monitoring port " + cast_int2str(giiniportno));
			gisSockON = false;
			return NULL;
		}

		// initialize server socket
		bzero((char *)&tsckServer, sizeof(tsckServer));
		tsckServer.sin_family = AF_INET;
		tsckServer.sin_addr.s_addr = INADDR_ANY;
		tsckServer.sin_port = htons(giiniportno);

		// try to bind the server socket
		if (bind(ihndServer, (struct sockaddr *) &tsckServer, sizeof(tsckServer)) < 0)
		{
			cout << "#ERR: cannot open monitoring port " << giiniportno << endl;
			log_write("app", "monitor", "error", "cannot open monitoring port " + cast_int2str(giiniportno));
			gisSockON = false;
			return NULL;
		}

		// keep looping until exit signaled
		log_write("app", "monitor", "success", "monitoring part opened, going to listen mode...");
		while (1)
		{
			if (gisSockON == false)
			{
				cout << "off signal received, closing application" << endl;
				log_write("app", "monitor", "success", "off signal received, closing application");
				close(ihndClient);
				close(ihndServer);
				return NULL;
			}

			// listen to incoming connection
			listen(ihndServer, 5);
			tlenClient = sizeof(tsckClient);
			ihndClient = accept(ihndServer, (struct sockaddr *) &tsckClient, &tlenClient);
			if (ihndClient < 0)
			{
				log_write("app", "monitor", "error", "error on accepting incoming connection, rebind socket...");
				break;
			}

			// retrieve incoming message
			bzero(buffer, 256);
			n = read(ihndClient, buffer, 255);
			if (n < 0)
			{
				log_write("app", "monitor", "error", "error on reading incoming message, rebind socket...");
				break;
			}

			// if message is empty
			if (n == 0)
			{
				sReply = "keyword is empty"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on replying empty keyword message to client, rebind socket...");
					break;
				}
			}

			// process incoming message
			sKey = "";
			for (i = 0; i < 256; i++) { if (buffer[i] == 0 || buffer[i] == 9 || buffer[i] == 10 || buffer[i] == 13) { break; } else { sKey = sKey + buffer[i]; } }

			if (sKey.compare("commands") == 0)
			{
				sReply = "commands ping stop time_start time_lived log_path processed thcount thstates thlocks thqueries thtasks";
				iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("log_path") == 0)
			{
				sReply = gsinidirlog; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("ping") == 0)
			{
				sReply = "alive"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("stop") == 0)
			{
				sReply = "0|stoping"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
				}
				log_write("app", "monitor", "success", "off signal received, closing application");
				close(ihndClient);
				close(ihndServer);
				gTerminateApplication = true;
				gisSockON = false;
				return NULL;
			}
			else if (sKey.compare("time_start") == 0)
			{
				sReply = sStampStarted; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("time_lived") == 0)
			{
				int dd, hh, mm, ss, delta;
				delta = time(NULL) - gtmLived;
				ss = fmod(delta, 60); delta = floor(delta / 60);
				mm = fmod(delta, 60); delta = floor(delta / 60);
				hh = fmod(delta, 24); delta = floor(delta / 24);
				dd = delta;

				sReply = cast_int2str(dd) + "d " + cast_int2str(hh) + "h " + cast_int2str(mm) + "m " + cast_int2str(ss) + "s";
				iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else
			{
				sReply = "unknown keyword [" + sKey + "]"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "monitor", "error", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			close(ihndClient);
		}

		// close socket
		close(ihndClient);
		close(ihndServer);
	}
}

bool socket_close()
{
	int n;
	int ihndClient;
	string sMsg = "stop";
	size_t iMsg = 4;
	struct hostent *sServer;
	struct sockaddr_in tsckRemote;

	// initialize socket
	ihndClient = socket(AF_INET, SOCK_STREAM, 0);
	if (ihndClient < 0)
	{
		log_write("app", "monitor", "error", "[61] closing port failed");
		return false;
	}

	// resolve remote name
	sServer = gethostbyname("127.0.0.1");
	if (sServer == NULL) {
		log_write("app", "monitor", "error", "[62] closing port failed");
		return false;
	}

	// initialize remote socket
	bzero((char *) &tsckRemote, sizeof(tsckRemote));
	tsckRemote.sin_family = AF_INET;
	bcopy((char *)sServer->h_addr, (char *)&tsckRemote.sin_addr.s_addr, sServer->h_length);
	tsckRemote.sin_port = htons(giiniportno);

	// make remote connection
	if (connect(ihndClient, (struct sockaddr *) &tsckRemote, sizeof(tsckRemote)) < 0)
	{
		log_write("app", "monitor", "error", "[63] closing port failed");
		return false;
	}

	// send message to remote connection
	n = write(ihndClient, sMsg.c_str(), iMsg);
	if (n < 0)
	{
		log_write("app", "monitor", "error", "[64] closing port failed");
		return false;
	}
	close(ihndClient);
	return true;
}

// SUBROUTINE, SETTING, GET KEYWORDS - OWNERS MAPPING ############################################################################
void setting_get_keywords(string psKeywords, string psURLTransmit, string psURLResponse)
{
	int i;
	int n;
	int iMode; // 0: do nothing, 1: record owner, 2: record keyword
	string s;
	string ss;
	string sOwner;
	string sKeyword;
	string sURL;
	string sMTD;
	string sTemp;

	// transform raw xml keyword string into upper case to avoid case-sensitive problems
	transform(psKeywords.begin(), psKeywords.end(), psKeywords.begin(), ::toupper);

	// set initial recording mode to doing nothing
	iMode = 0;

	// count the length of raw xml keyword string
	n = psKeywords.length();

	// start spliting and recording process
	for (i = 0; i < n; i++)
	{
		// when closing tag found
		if (psKeywords.substr(i, 2) == "</")
		{
			// check currently in reading value mode
			if (iMode == 1)
			{
				// this will happen when reading owner without keyword, example: <owner></owner>
				// in this case do not add empty keyword into keyword's collection, just do nothing
			}
			else if (iMode == 2)
			{
				// this will happen when reading last keyword listed, example: <owner>a,b,c,LAST</owner>
				// so add keyword LAST into keyword's collection
				sTemp = xml_get_value(psURLTransmit, sOwner);
				sMTD = xml_get_value(sTemp, "method");
				sURL = xml_get_value(sTemp, "address");
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLTransmit, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
				}
				gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL);

				sTemp = xml_get_value(psURLResponse, sOwner);
				sMTD = xml_get_value(sTemp, "method");
				sURL = xml_get_value(sTemp, "address");
				gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL);
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLResponse, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
				}

				sKeyword = "";
			}

			// reset reading mode into 0 which make next reading not processed
			iMode = 0;
		}
		else
		{
			s = psKeywords.substr(i, 1);
			if (s == " ") // invalid characters and will not be processed
			{
				// do nothing
			}
			else if (s == "<") // if opening tag found, switch to recording element name
			{
				iMode = 1;
				sOwner = "";
			}
			else if (s == ">")
			{
				if (iMode == 0) // if closing tag found while not in recording mode, ignore it
				{
					// do nothing
				}
				else if (iMode == 1) // if closing tag found while in recording element name, then switch it to record element value
				{
					iMode = 2;
				}
			}
			else
			{
				if (iMode == 0)
				{
					// do nothing
				}
				else if (iMode == 1)
				{
					// record owner
					sOwner = sOwner + s;
				}
				else if (iMode == 2)
				{
					if (s == ",")
					{
						// keyword separator found, add keyword into keyword's collection
						sTemp = xml_get_value(psURLTransmit, sOwner);
						sMTD = xml_get_value(sTemp, "method");
						sURL = xml_get_value(sTemp, "address");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLTransmit, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
						}
						gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL);

						sTemp = xml_get_value(psURLResponse, sOwner);
						sMTD = xml_get_value(sTemp, "method");
						sURL = xml_get_value(sTemp, "address");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLResponse, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
						}
						gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL);

						sKeyword = "";
					}
					else
					{
						// record keyword
						sKeyword = sKeyword + s;
					}
				}
				else {} // do nothing
			}
		}
	}
}

// SUBROUTINE, SETTING, GET VALUES ###############################################################################################
void setting_get_values(string sText)
{
	int i, n;
	string sSQL;
	string sTemp1;
	string sTemp2;
	string sTemp3;

	giiniportno	= cast_str2int(xml_get_value(sText, "app_port_num"));

	giinitimeoutexec = cast_str2int(xml_get_value(sText, "thread_timeout_updater"));
	giinitimeoutpull = cast_str2int(xml_get_value(sText, "thread_timeout_pull"));
	giinitimeoutpush = cast_str2int(xml_get_value(sText, "thread_timeout_push"));

	sTemp1 = cast_str2lower(xml_get_value(sText, "log_per_thread"));
	giini1thread1log = (sTemp1 == "0" || sTemp1 == "no" || sTemp1 == "false") ? 0 : 1;

	gsinilogptn	= xml_get_value(sText, "log_naming_pattern");
	gsinilogflo = xml_get_value(sText, "log_flow_descriptor");

	sTemp1 = xml_get_value(sText, "db_queue");
		gsinidb1addr = xml_get_value(sTemp1, "host");
		gsinidb1name = xml_get_value(sTemp1, "database");
		gsinidb1user = xml_get_value(sTemp1, "user");
		gsinidb1pswd = xml_get_value(sTemp1, "password");
		gsinidb1tabl = xml_get_value(sTemp1, "table");

	sTemp1 = xml_get_value(sText, "directories");
		gsinidirlog = xml_get_value(sText, "logs_files");
		if (gsinidirlog[gsinidirlog.length() - 1] != '/') { gsinidirlog = gsinidirlog + "/"; }

	sTemp1 = xml_get_value(sText, "sleep");
		giinisleep1 = cast_str2int(xml_get_value(sText, "fast"));
		giinisleep2 = cast_str2int(xml_get_value(sText, "long"));

	sTemp1 = xml_get_value(sText, "queue_pull");
		giinipull_thread_cnt = cast_str2int(xml_get_value(sTemp1, "transmitter_count"));
		giinipull_sql_cnt = 0;
		for (i = 0; i < GIMAXSQLPULLNPUSH; i++)
		{
			sSQL = xml_get_value(sTemp1, "query" + cast_int2str(i + 1));
			if (sSQL == "") { break; }
			giinipull_sql_cnt = giinipull_sql_cnt + 1;
			gsinipull_sql[giinipull_sql_cnt] = sSQL;
		}

	sTemp1 = xml_get_value(sText, "queue_push");
		giinipush_thread_cnt = cast_str2int(xml_get_value(sTemp1, "transmitter_count"));
		giinipush_sql_cnt = 0;
		for (i = 0; i < GIMAXSQLPULLNPUSH; i++)
		{
			sSQL = xml_get_value(sTemp1, "query" + cast_int2str(i + 1));
			if (sSQL == "") { break; }
			giinipush_sql_cnt = giinipush_sql_cnt + 1;
			gsinipush_sql[giinipush_sql_cnt] = sSQL;
		}

	sTemp1 = xml_get_value(sText, "owner_keyword");
	sTemp2 = xml_get_value(sText, "transmitter");
	sTemp3 = xml_get_value(sText, "response");
	sTemp3 = cast_str2bool(xml_get_value(sTemp3, "forward"));
		gbinifwdenable = cast_str2bool(xml_get_value(sTemp3, "enabled"));

	sTemp3 = xml_get_value(sText, "response");
	sTemp3 = xml_get_value(sTemp3, "recipient");
		setting_get_keywords(sTemp1, sTemp2, sTemp3);
		gsinidefaulturl = xml_get_value(sTemp1, "default");

	// get values for response setting - get transmitting success identifier -----------------------------------------------------
	sTemp1 = xml_get_value(sText, "response");
		giiniresok_cnt = 0;
		for (i = 0; i < GIMAXOKRESPONSE; i++)
		{
			sSQL = xml_get_value(sTemp1, "success_identifier" + cast_int2str(i + 1));
			if (sSQL == "") { break; }
			giiniresok_cnt = giiniresok_cnt + 1;
			gsiniresok[giiniresok_cnt] = cast_str2upper(sSQL);
		}

	// get setting to save response into database (database would be the same database where queue table existed)
	sTemp1 = xml_get_value(sText, "response");
	sTemp1 = xml_get_value(sTemp1, "save_to_db");
		gbiniressaveenable = cast_str2bool(xml_get_value(sTemp1, "enabled"));
		gsiniressave2table = xml_get_value(sTemp1, "into_table");
		giiniressave2fieldcnt = 0;
		for (i = 0; i < GIMAXRESPONSEFIELD; i++)
		{
			sTemp2 = xml_get_value(sTemp1, "into_field" + cast_int2str(i + 1));
			if (sTemp2 == "") { break; }
			giiniressave2fieldcnt = giiniressave2fieldcnt + 1;
			gsiniressave2field[giiniressave2fieldcnt] = sTemp2;
			gsiniressavexmltag[giiniressave2fieldcnt] = xml_get_value(sTemp1, "from_xml_field" + cast_int2str(i + 1));
		}
}

// SUBROUTINE, SETTING, SHOW VALUES ##############################################################################################
void setting_show_values()
{
	int i;
	string sVal1, sVal2, sVal3, sVal4;

	cout << "    queue db, host      : " << gsinidb1addr << endl;
	cout << "    queue db, database  : " << gsinidb1name << endl;
	cout << "    queue db, user      : " << gsinidb1user << endl;
	cout << "    queue db, table     : " << gsinidb1tabl << endl;
	cout << endl;

	cout << "    log files directory : " << gsinidirlog << endl;
	cout << "    log naming pattern  : " << gsinilogptn << endl;
	cout << "    log flow descriptor : " << gsinilogflo << endl;
	cout << "    sleep fast          : " << giinisleep1 << endl;
	cout << "    sleep long          : " << giinisleep2 << endl << endl;

	// show keywords mapping
	cout << "keywords mapping - transmitter" << endl;
	cout << "number; keyword; owner; method; url" << endl;
	cout << "===================================================================" << endl;
	i = 0;
	gcolKwTransmit.first();
	while (gcolKwTransmit.next() == true)
	{
		i = i + 1;
		gcolKwTransmit.read(sVal1, sVal2, sVal3, sVal4);
		cout << "    " << i << ": " << sVal1 << ": " << sVal2 << ": " << sVal3 << ": " << sVal4 << endl;
	}

	// show keywords mapping
	cout << endl;
	cout << "keywords mapping - forwarding response" << endl;
	cout << "number; keyword; owner; method; url" << endl;
	cout << "===================================================================" << endl;
	i = 0;
	gcolKwResponse.first();
	while (gcolKwResponse.next() == true)
	{
		i = i + 1;
		gcolKwResponse.read(sVal1, sVal2, sVal3, sVal4);
		cout << "    " << i << ": " << sVal1 << ": " << sVal2 << ": " << sVal3 << ": " << sVal4 << endl;
	}

	// show setting on whether to save response into database or not
	cout << endl;
	cout << "save response into database" << endl;
	cout << "===================================================================" << endl;
	cout << "enabled           : " << gbiniressaveenable << endl;
	cout << "database name     : " << gsinidb1name << endl;
	cout << "table name        : " << ((gsiniressave2table == "") ? "[not specified]" : gsiniressave2table) << endl;
	cout << "field count       : " << giiniressave2fieldcnt << endl;
	for (i = 0; i < giiniressave2fieldcnt; i++)
	{
		cout << "from XML to field : " << gsiniressavexmltag[i + 1] << " --> " << gsiniressave2field[i + 1] << endl;
	}
}

// SUBROUTINE, SETTING, LOG VALUES ###############################################################################################
void setting_log_values(string sPathSetting)
{
	int i;
	log_write("app", "started", "init file",		sPathSetting);
	log_write("app", "setting", "queue, host",		gsinidb1addr);
	log_write("app", "setting", "queue, database",	gsinidb1name);
	log_write("app", "setting", "queue, user",		gsinidb1user);
	log_write("app", "setting", "queue, table",		gsinidb1tabl);

	log_write("app", "setting", "log directory",	gsinidirlog);
	log_write("app", "setting", "log pattern",		gsinilogptn);
	log_write("app", "setting", "log flow dcpt",	gsinilogflo);

	log_write("app", "setting", "sleep fast",		cast_int2str(giinisleep1));
	log_write("app", "setting", "sleep long",		cast_int2str(giinisleep2));
}

// MAIN SUBROUTINE, APPLICATION START HERE #######################################################################################
int main (int argc, char **argv)
{
	int i, n;
	string sSQL, sXML, sDNAS, sTemp;
	string sVal0, sVal1, sVal2, sVal3, sVal4, sVal5, sVal6, sVal7;

	MYSQL_RES *tdbRS;		// mysql recordset
	MYSQL_ROW tdbROW;		// mysql row

	pthread_t thidMON;		// thread handler for socket monitoring
	time_t tt1; struct tm *tt2; char cc[3]; int dd;	int iDayNow = 0;	// today's date variables
	gTerminateApplication = false;

	// application setting
	if (GDO_LOAD_SETTING)
	{
		// setting - open file setting and get its content
		cout << endl;
		cout << "Application custom libraries" << endl;
		cout << "===================================================================" << endl;

		cout << "Queuing Library:" << endl;
		sTemp = gqueEXEC.version();
		cout << sTemp << endl << endl;

		cout << "Collection Library:" << endl;
		sTemp = gcolKwResponse.version();
		cout << sTemp << endl << endl;

		// setting - open file setting and get its content
		cout << "Application initialization file [" << argv[1] << "]" << endl;
		cout << "===================================================================" << endl;
		sXML = xml_read_file(argv[1]);
		setting_get_values(sXML);

		// setting - show current setting values
		setting_show_values();

		// setting - write to log setting values
		setting_log_values(argv[1]);
	}

	// mysql database
	if (GDO_INITIALIZE_DB)
	{
		// database - check if this application compiled with mysql thread-safe library
		if (!mysql_thread_safe())
		{
			for (i = 0; i < 100; i++) { cout << endl; }
			cout << "#ERR: this application should be compiled with thread-safe library!" << endl << endl;
			log_write("app", "mysql", "error", "this application should be compiled with thread-safe library!");
			goto posExitApp;
		}

		// database - initialize mysql C API library
		if (mysql_library_init(0, NULL, NULL))
		{
			cout << "#ERR: could not initialize mysql library!" << endl << endl;
			log_write("app", "mysql", "error", "could not initialize mysql library!");
			goto posExitApp;
		}

		// open database
		mysql_init(&gdb1OBJ);
		if (db_connect(1) != 0)
		{
			cout << "#ERR: could not establish database connection [1]!" << endl << endl;
			log_write("app", "mysql", "error", "could not establish database connection [1]!");
			goto posExitApp;
		}

		mysql_init(&gdb2OBJ);
		if (db_connect(2) != 0)
		{
			cout << "#ERR: could not establish database connection [2]!" << endl << endl;
			log_write("app", "mysql", "error", "could not establish database connection [2]!");
			goto posExitApp;
		}
	}

	// create a new thread for monitoring
	if (GDO_START_MONITORING)
	{
		gisSockON = true;	// this value must be set before creating thread for socket_open
		n = pthread_create(&thidMON, NULL, &socket_open, NULL);
		if (n != 0)
		{
			sTemp = "failed to create monitoring thread, application aborted...";
			log_write("app", "create th mon.", "error", sTemp);
			cout << sTemp << endl;
			goto posExitApp;
		}
	}

	// clean up 'in process' flag in case previous run was ended abruptly
	if (GDO_CLEANUP_PREVIOUS)
	{
		sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 0, flagdesc = '' WHERE flag = 1";
		if (db_query(1, sSQL) == 0)
		{
			n = mysql_affected_rows(gdb1CN);
			log_write("app", "clean up", "success", "execute clean up succeed, " + cast_int2str(n) + " record(s) flag reset for next process");
		}
		else
		{
			log_write("app", "clean up", "error", "execute clean up failed, continue without reseting flag from previous run");
			log_write("app", "clean up", "error", "terminate application");
			goto posExitApp;
		}
	}

	// initialize thread information
	gthinfoPULL = new struct thread_info[giinipull_thread_cnt];
	gthinfoPUSH = new struct thread_info[giinipush_thread_cnt];

	gthinfoEXEC.number = -1; time(&gthinfoEXEC.check); gthinfoEXEC.check = gthinfoEXEC.check - giinitimeoutexec - 10;
	for (i = 0; i < giinipull_thread_cnt; i++) { gthinfoPULL[i].number = -1; time(&gthinfoPULL[i].check); gthinfoPULL[i].check = gthinfoPULL[i].check - giinitimeoutpull - 10; }
	for (i = 0; i < giinipush_thread_cnt; i++) { gthinfoPUSH[i].number = -1; time(&gthinfoPUSH[i].check); gthinfoPUSH[i].check = gthinfoPUSH[i].check - giinitimeoutpush - 10; }

	// main loop - fill queues and maintain thread activities
	time(&tt1); tt2 = localtime(&tt1); strftime(cc, 3, "%d", tt2); dd = atoi(cc); iDayNow = dd;
	while (gTerminateApplication == false)
	{
		// if date changed then reset errors for those errors that possible for another retry
		time(&tt1); tt2 = localtime(&tt1); strftime(cc, 3, "%d", tt2); dd = atoi(cc);
		if (dd != iDayNow)
		{
			iDayNow = dd;
			sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 0, flagdesc = '' WHERE flag > 50 AND flag < 60";
			if (db_query(1, sSQL) == 0)
			{
				n = mysql_affected_rows(gdb1CN);
				log_write("app", "date changed", "success", cast_int2str(n) + " error(s) reset for another retry");
			}
			else
			{
				log_write("app", "date changed", "error", sSQL);
				log_write("app", "date changed", "error", "query failed");
				log_write("app", "date changed", "error", "[" + cast_int2str(mysql_errno(gdb1CN)) + "] " + mysql_error(gdb1CN));
				log_write("app", "date changed", "solution", "terminate application");
				goto posExitApp;
			}
		}

		// fill queue if queue is empty, pull
		if (gquePULL.count() < 1)
		{
			log_write("app", "queue pull", "begin", "");
			for (i = 0; i < giinipull_sql_cnt; i++)
			{
				sSQL = gsinipull_sql[i + 1];
				log_write("app", "queue pull", "  query " + cast_int2str(i + 1), sSQL);
				if (db_query(1, sSQL) != 0)
				{
					log_write("app", "queue pull", "  error", "query failed");
					log_write("app", "queue pull", "  error", "[" + cast_int2str(mysql_errno(gdb1CN)) + "] " + mysql_error(gdb1CN));
					log_write("app", "queue pull", "  solution", "terminate application");
					log_write("app", "queue pull", "finish", "");
					goto posExitApp;
				}
				else
				{
					tdbRS = mysql_store_result(gdb1CN);
						n = 0;
						sDNAS = "";
						while (tdbROW = mysql_fetch_row(tdbRS))
						{
							n = n + 1;
							sDNAS = sDNAS + (string)tdbROW[0] + ",";
						}

						if (n > 0)
						{
							sDNAS = sDNAS.substr(0, sDNAS.length() - 1);
							sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 1, flagdesc = NOW() WHERE dna IN (" + sDNAS + ") LIMIT " + cast_int2str(n);
							if (db_query(1, sSQL) == 0)
							{
								log_write("app", "queue pull", "  query " + cast_int2str(i + 1), cast_int2str(n) + " marked record(s)");

								n = 0;
								mysql_data_seek(tdbRS, 0);
								while (tdbROW = mysql_fetch_row(tdbRS))
								{
									n = n + 1;
									sVal0 = "app";
									sVal1 = tdbROW[0] ? tdbROW[0] : "";
									sVal2 = tdbROW[1] ? tdbROW[1] : "";
									sVal3 = tdbROW[2] ? tdbROW[2] : "";
									sVal4 = tdbROW[3] ? tdbROW[3] : "";
									sVal5 = tdbROW[4] ? tdbROW[4] : "";
									sVal6 = tdbROW[5] ? tdbROW[5] : "";
									sVal7 = tdbROW[6] ? tdbROW[6] : "";
									gquePULL.put(sVal0, sVal1, sVal2, sVal3, sVal4, sVal5, sVal6, sVal7);
								}
							}
							else
							{
								log_write("app", "queue pull", "  error", "query for marking recordset failed");
								log_write("app", "queue pull", "  solution", "terminate application");
								log_write("app", "queue pull", "finish", "");
								goto posExitApp;
							}
						}
					try { mysql_free_result(tdbRS); } catch (int e) {}
					log_write("app", "queue pull", "  query " + cast_int2str(i + 1), cast_int2str(n) + " put into buffer");
				}
			}
			log_write("app", "queue pull", "finish", "");
		}

		// fill queue if queue is empty, push
		if (gquePUSH.count() < 1)
		{
			log_write("app", "queue push", "begin", "");
			for (i = 0; i < giinipush_sql_cnt; i++)
			{
				sSQL = gsinipush_sql[i + 1];
				log_write("app", "queue push", "  query " + cast_int2str(i + 1), sSQL);
				if (db_query(1, sSQL) != 0)
				{
					log_write("app", "queue push", "  error", "query failed");
					log_write("app", "queue push", "  error", "[" + cast_int2str(mysql_errno(gdb1CN)) + "] " + mysql_error(gdb1CN));
					log_write("app", "queue push", "  solution", "terminate application");
					log_write("app", "queue push", "finish", "");
					goto posExitApp;
				}
				else
				{
					tdbRS = mysql_store_result(gdb1CN);
						n = 0;
						sDNAS = "";
						while (tdbROW = mysql_fetch_row(tdbRS))
						{
							n = n + 1;
							sDNAS = sDNAS + (string)tdbROW[0] + ",";
						}

						if (n > 0)
						{
							sDNAS = sDNAS.substr(0, sDNAS.length() - 1);
							sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 1, flagdesc = NOW() WHERE dna IN (" + sDNAS + ") LIMIT " + cast_int2str(n);
							if (db_query(1, sSQL) == 0)
							{
								log_write("app", "queue push", "  query " + cast_int2str(i + 1), cast_int2str(n) + " marked record(s)");

								n = 0;
								mysql_data_seek(tdbRS, 0);
								while (tdbROW = mysql_fetch_row(tdbRS))
								{
									n = n + 1;
									sVal0 = "app";
									sVal1 = tdbROW[0] ? tdbROW[0] : "";
									sVal2 = tdbROW[1] ? tdbROW[1] : "";
									sVal3 = tdbROW[2] ? tdbROW[2] : "";
									sVal4 = tdbROW[3] ? tdbROW[3] : "";
									sVal5 = tdbROW[4] ? tdbROW[4] : "";
									sVal6 = tdbROW[5] ? tdbROW[5] : "";
									sVal7 = tdbROW[6] ? tdbROW[6] : "";
									gquePUSH.put(sVal0, sVal1, sVal2, sVal3, sVal4, sVal5, sVal6, sVal7);
								}
							}
							else
							{
								log_write("app", "queue push", "  error", "query for marking recordset failed");
								log_write("app", "queue push", "  solution", "terminate application");
								log_write("app", "queue push", "finish", "");
								goto posExitApp;
							}
						}
					try { mysql_free_result(tdbRS); } catch (int e) {}
					log_write("app", "queue push", "  query " + cast_int2str(i + 1), cast_int2str(n) + " put into buffer");
				}
			}
			log_write("app", "queue push", "finish", "");
		}

		// start 1st thread - updater
		if (difftime(time(NULL), gthinfoEXEC.check) > giinitimeoutexec)
		{
			if (gthinfoEXEC.number != -1)
			{
				log_write("app", "thread upd.", "timeout", "closing thread [updater] " + gthinfoEXEC.name);
				cout << "thread timeout, closing thread [updater] " << gthinfoEXEC.name << endl;
				pthread_cancel(gthinfoEXEC.id);
			}

			gthinfoEXEC.name = "updater_1";
			gthinfoEXEC.type = "updater";
			gthinfoEXEC.number = 1;
			time(&gthinfoEXEC.check);

			n = pthread_create(&gthinfoEXEC.id, NULL, &thread_updater, NULL);
			if (n == 0)
			{
				cout << "starting thread " << gthinfoEXEC.name << " [success]" << endl;
				log_write("app", "create th upd.", "success", gthinfoEXEC.name);
			}
			else
			{
				cout << "starting thread " << gthinfoEXEC.name << " [failed]" << endl;
				log_write("app", "create th upd.", "error", gthinfoEXEC.name + ", terminate application");
				goto posExitApp;
			}
		}

		// start 2nd thread - transmitters - pull
		for (i = 0; i < giinipull_thread_cnt; i++)
		{
			if (difftime(time(NULL), gthinfoPULL[i].check) > giinitimeoutpull)
			{
				// terminate previous thread first
				if (gthinfoPULL[i].number != -1)
				{
					log_write("app", "thread pull", "timeout", "closing thread [pull] " + gthinfoPULL[i].name);
					cout << "thread timeout, closing thread [pull] " << gthinfoPULL[i].name << endl;
					pthread_cancel(gthinfoPULL[i].id);
				}

				// create new thread
				gthinfoPULL[i].name = "pull_" + cast_int2str(i);
				gthinfoPULL[i].type = "pull";
				gthinfoPULL[i].number = i;
				time(&gthinfoPULL[i].check);

				n = pthread_create(&gthinfoPULL[i].id, NULL, &thread_transmitter, &gthinfoPULL[i]);
				if (n == 0)
				{
					cout << "starting thread " << gthinfoPULL[i].name << " [success]" << endl;
					log_write("app", "create th pull", "success", gthinfoPULL[i].name);
				}
				else
				{
					cout << "starting thread " << gthinfoPULL[i].name << " [failed]" << endl;
					log_write("app", "create th pull", "error", gthinfoPULL[i].name + ", terminate application");
					goto posExitApp;
				}
			}
		}

		// start 3rd thread - transmitters - push
		for (i = 0; i < giinipush_thread_cnt; i++)
		{
			if (difftime(time(NULL), gthinfoPUSH[i].check) > giinitimeoutpush)
			{
				// terminate previous thread first
				if (gthinfoPUSH[i].number != -1)
				{
					log_write("app", "thread push", "timeout", "closing thread [push] " + gthinfoPUSH[i].name);
					cout << "thread timeout, closing thread [push] " << gthinfoPUSH[i].name << endl;
					pthread_cancel(gthinfoPUSH[i].id);
				}

				// create new thread
				gthinfoPUSH[i].name = "push_" + cast_int2str(i);
				gthinfoPUSH[i].type = "push";
				gthinfoPUSH[i].number = i;
				time(&gthinfoPUSH[i].check);

				n = pthread_create(&gthinfoPUSH[i].id, NULL, &thread_transmitter, &gthinfoPUSH[i]);
				if (n == 0)
				{
					cout << "starting thread " << gthinfoPUSH[i].name << " [success]" << endl;
					log_write("app", "create th push", "success", gthinfoPUSH[i].name);
				}
				else
				{
					cout << "starting thread " << gthinfoPUSH[i].name << " [failed]" << endl;
					log_write("app", "create th push", "error", gthinfoPUSH[i].name + ", terminate application");
					goto posExitApp;
				}
			}
		}
		// sleep
		usleep(giinisleep1);
	}
	goto posExitApp;

	// a position where execution can jump to this place for application termination
	posExitApp:;
	gTerminateApplication = true;

	try { socket_close(); } catch (int e) {}
	try { mysql_close(gdb1CN); } catch (int e) {}
	try { mysql_close(gdb2CN); } catch (int e) {}
	try { mysql_library_end(); } catch (int e) {}
	try { memset(gthinfoPULL, 0, sizeof(gthinfoPULL)); } catch (int e) {}
	try { memset(gthinfoPUSH, 0, sizeof(gthinfoPUSH)); } catch (int e) {}
}