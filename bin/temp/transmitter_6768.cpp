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
#include <iomanip>			// required for setprecision conversion from double to string
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
#include "myqueue.h"
#include "myurl.h"
#include "mycollection.h"

using namespace std;

// GLOBAL CONSTANTS ==============================================================================================================
const bool	REGION_GET_QUEUE = true;
const bool	REGION_PROCESS_RESPONSE = true;
const bool	REGION_LOAD_SETTING = true;
const bool	REGION_INITIALIZE_DB = true;
const bool	REGION_START_MONITORING = true;
const bool	REGION_CLEANUP_PREVIOUS = true;

const int	GIMAXOKRESPONSE		= 5;		// maximum number of responses from sendind data, that define it was a success
const int	GIMAXSQLPULLNPUSH	= 5;		// maximum number of queries to get records from database - pull records
const int	GIMAXTRYWRITE2LOG	= 5;		// maximum number of retry when writing log failed
const int	GIMAXTRYSENDURL		= 5;		// maximum number of retry when sending message failed
const int	GIMAXSQLEXEC		= 5;		// maximum number of queries can be executed uppon response arrival

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

int		giiniURLTimeout;	// App setting variable, timeout in seconds while accessing URL

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

string gsinidefaultmtd = "get";	// App setting variable, default transmitting address, method of transmitting
string gsinidefaulturl = "";	// App setting variable, default transmitting address, destination address for transmitting
string gsinidefaultout = "3";	// App setting variable, default transmitting address, timeout for transmitting

string	sStampStarted;		// monitoring, store date and time application started ???
time_t	gtmLived;			// monitoring, store amount of seconds from application started ???

myqueue	gquePULL;
myqueue	gquePUSH;
myqueue	gqueEXEC;

int		giinipull_sql_cnt;
int		giinipull_thread_cnt;
string	gsinipull_sql[GIMAXSQLPULLNPUSH];

int		giinipush_sql_cnt;
int		giinipush_thread_cnt;
string	gsinipush_sql[GIMAXSQLPULLNPUSH];

int		giiniexec_sql_sces_cnt;				// xml setting, number of queries to execute when success response received
string	gsiniexec_sql_sces[GIMAXSQLEXEC];	// xml setting, the queries to execute when success response received

int		giiniexec_sql_fail_cnt;				// xml setting, number of queries to execute when failed response received
string	gsiniexec_sql_fail[GIMAXSQLEXEC];	// xml setting, the queries to execute when failed response received

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

mycollection gcolKwTransmit;
mycollection gcolKwResponse;

bool	gbinifwdenable = false;
bool	gbinifwdenableonempty = false;
bool	gbinifwdenableonerror = false;
bool	gbinifwdenableonsuccess = false;

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

string cast_dbl2str(double iParam)
{
	stringstream ss;
	ss << fixed << setprecision(3) << iParam;
	return ss.str();
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
	transform(sUPPER.begin(), sUPPER.end(), sUPPER.begin(), ::tolower);

	transform(sKey.begin(), sKey.end(), sKey.begin(), ::tolower);
	string sSeek = "<" + sKey + ">";

	a = sUPPER.find(sSeek);
	if (a == string::npos) { return ""; }
	n = int(a) + sSeek.length();

	b = sUPPER.find("</" + sKey + ">", n);
	return (b == string::npos) ? sSource.substr(n) : sSource.substr(n, int(b) - n);
}

// FUNCTION, GET VARIABLE NAME EXISTING IN SQL ###################################################################################
string sql_variable2value(string psSQL, string psDNA, string psTRXID, string psMSISDN, string psSID, string psKeyword, string psOwner, string psResponse)
{
	int i, n;
	string s, ss;
	string sKey = "";
	string sRET = "";
	bool doRec = false;

	n = psSQL.length();
	for (i = 0; i < n; i++)
	{
		s = psSQL.at(i);

		// replace processing variables with values ------------------------------------------------------------------------------
		if (s == "[")
		{
			ss = psSQL.at(i + 1);
			if (ss == "[")
			{
				doRec = true;
			}
			else
			{
				sRET = sRET + s + ss;
			}
			i = i + 1;
			goto posNextChar;
		}
		if (s == "]")
		{
			ss = psSQL.at(i + 1);
			if (ss == "]")
			{
				doRec = false;
				std::transform(sKey.begin(), sKey.end(), sKey.begin(), ::tolower);
				if 		(sKey == "dna")		{ sRET = sRET + psDNA; }
				else if (sKey == "sid")		{ sRET = sRET + psSID; }
				else if (sKey == "owner")	{ sRET = sRET + psOwner; }
				else if (sKey == "trxid")	{ sRET = sRET + psTRXID; }
				else if (sKey == "msisdn")	{ sRET = sRET + psMSISDN; }
				else if (sKey == "keyword")	{ sRET = sRET + psKeyword; }
				else 						{ sRET = sRET + ""; }
				sKey = "";
			}
			else
			{
				sRET = sRET + s + ss;
			}
			i = i + 1;
			goto posNextChar;
		}

		// replace response variables with values --------------------------------------------------------------------------------
		if (s == "{")
		{
			ss = psSQL.at(i + 1);
			if (ss == "{")
			{
				doRec = true;
			}
			else
			{
				sRET = sRET + s + ss;
			}
			i = i + 1;
			goto posNextChar;
		}

		if (s == "}")
		{
			ss = psSQL.at(i + 1);
			if (ss == "}")
			{
				doRec = false;
				sRET = sRET + xml_get_value(psResponse, sKey);
				sKey = "";
			}
			else
			{
				sRET = sRET + s + ss;
			}
			i = i + 1;
			goto posNextChar;
		}

		if (doRec == true) { sKey = sKey + s; } else { sRET = sRET + s; }
		posNextChar:;
	}

	return sRET;
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
		try { mysql_close(gdb1CN); } catch(int e) {}

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
		try { mysql_close(gdb2CN); } catch(int e) {}

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
	bool isSent;
	bool isFound;
	int i, j, n, m;
	double dElapsed;
	size_t t;
	string sSQL, sSQL2, sTemp, sTempURL, sTempData, sCloseMsg;
	string sKeyword, sOwner, sMethod, sURL, sTimeout;
	string sTHR, sDNA, sTRXID, sMSISDN, sSID;
	string sDATA, sURLResponse, sStatus;
	string sFwdRcpName, sFwdMethod, sFwdAddress, sFwdTimeout, sFwdResponse;
	myurl objURL;

	struct thread_info thinfo = *(struct thread_info *)arg;

	while (gTerminateApplication == false)
	{
		// reset closing message to success
		sCloseMsg = "counted as success\n";

		// get address to send from queue, if failed (mostly because of queue is empty), sleep long, process next queue
		if (REGION_GET_QUEUE)
		{
			// no need to reset these variables since it will be reset when calling queue's get method
			// sTHR, sDNA, sTRXID, sKeyword, sURL, sMethod
			pthread_mutex_lock(&tmutex);
				if (thinfo.type == "pull")
				{
					// main data is used for forwarding response (sDNA, sTRXID, sMSISDN, sSID)
					// extra data is required if url address is given inside sTempData
					isFound = gquePULL.get(sTHR, sDNA, sTRXID, sMSISDN, sSID, sKeyword, sMethod, sTempData);
				}
				else
				{
					// main data is used for forwarding response (sDNA, sTRXID, sMSISDN, sSID)
					// extra data is required if url address is given inside sTempData
					isFound = gquePUSH.get(sTHR, sDNA, sTRXID, sMSISDN, sSID, sKeyword, sMethod, sTempData);
				}
			pthread_mutex_unlock(&tmutex);

			if (isFound == false || sDNA == "" || sTRXID == "" || sTempData == "")
			{
				usleep(giinisleep1);
				goto posNextQueue;
			}
			std::transform(sMethod.begin(), sMethod.end(), sMethod.begin(), ::tolower);
		}

		// begin log
		log_write(thinfo.name, sTRXID, "begin", "post");
		log_write(thinfo.name, sTRXID, "  trx id", sTRXID);
		log_write(thinfo.name, sTRXID, "  keyword", sKeyword);

		// first check if given data also contain partner's url address
		isFound = false;
		if (isFound == false)
		{
			try { sTemp = sTempData.substr(0, 7); } catch (int e) { sTemp = sTempData; }
			transform(sTemp.begin(), sTemp.end(), sTemp.begin(), ::tolower);

			if (sTemp == "http://" || sTemp == "https:/")
			{
				log_write(thinfo.name, sTRXID, "  url lookup", "found inside url data");
				t = sTempData.find("?"); // check if raw data contain url address
				if (t == string::npos)
				{
					sURL = sTempData;
					sDATA = "";
					isFound = true;
				}
				else // if url contain parameters then separate it into part that contain url only and parameters only
				{
					try
					{
						sURL = sTempData.substr(0, int(t));
						sDATA = sTempData.substr(int(t) + 1);
						isFound = true;
					}
					catch (int e)
					{

						log_write(thinfo.name, sTRXID, "  error", "url data delimiter found but extracting url data failed");
						log_write(thinfo.name, sTRXID, "  error", "substr(" + cast_int2str(int(t) + 1) + ")");
						log_write(thinfo.name, sTRXID, "  error", sTemp);
						log_write(thinfo.name, sTRXID, "  solution", "set url data to empty string and try to send it anyway");
						isFound = true;
					}
				}
			}
			else
			{
				log_write(thinfo.name, sTRXID, "  url lookup", "not found inside url data");
			}
		}

		// if partner's url address not found in given data, then try to get url address from xml setting
		if (isFound == false)
		{
			isFound = gcolKwTransmit.item(sKeyword, sOwner, sMethod, sTempURL, sTimeout);
			if (isFound == true)
			{
				log_write(thinfo.name, sTRXID, "  url lookup", "found by keyword");
				sURL = sTempURL;
				sDATA = sTempData;
			}
			else
			{
				log_write(thinfo.name, sTRXID, "  url lookup", "not found by keyword");
			}
		}

		// if partner's url address still not found from given keyword, then use default url address
		if (isFound == false)
		{
			isFound = true;
			sMethod = gsinidefaultmtd;
			sTimeout = gsinidefaultout;
			sURL = gsinidefaulturl;
			sDATA = sTempData;
			log_write(thinfo.name, sTRXID, "  url lookup", "set to default");
		}

		// if partner's url address found then log it first before sending it
		log_write(thinfo.name, sTRXID, "  url address", sURL);
		log_write(thinfo.name, sTRXID, "  url data", sDATA);

		// try to send the message
		n = cast_str2int(sTimeout);
		for (i = 0; i < GIMAXTRYSENDURL; i++)
		{
			isSent = objURL.send(sMethod, sURL, sDATA, n, sURLResponse, dElapsed);
			if (isSent == true)
			{
				log_write(thinfo.name, sTRXID, "  send " + sMethod, "success, attempt " + cast_int2str(i + 1) + " of " + cast_int2str(GIMAXTRYSENDURL) + " (" + cast_dbl2str(dElapsed) + " sec)");
				log_write(thinfo.name, sTRXID, "  response", sURLResponse);
				break;
			}
			else
			{
				log_write(thinfo.name, sTRXID, "  send " + sMethod, "failed, attempt " + cast_int2str(i + 1) + " of " + cast_int2str(GIMAXTRYSENDURL) + " (" + cast_dbl2str(dElapsed) + " sec)");
				log_write(thinfo.name, sTRXID, "  response", sURLResponse);
				usleep(giinisleep2);
			}
		}

		// check if sending the message is failed
		if (isSent == false)
		{
			log_write(thinfo.name, sTRXID, "  error", "[51] cannot open specified url");
			if (sURLResponse != "")
			{
				log_write(thinfo.name, sTRXID, "  error", sURLResponse);
			}
			log_write(thinfo.name, sTRXID, "  solution", "url will be send again when date changed");
			sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 52, flagdesc = 'cannot open specified url' WHERE dna = '" + sDNA + "' LIMIT 1";
			gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL);
			gqueEXEC.put(thinfo.name, sTRXID, "finish", "counted as error\n");
			usleep(giinisleep2);
			goto posNextQueue;
		}

		// process response ######################################################################################################
		if (REGION_PROCESS_RESPONSE)
		{
			// if response is empty
			m = 0;
			if (sURLResponse == "")
			{
				sCloseMsg = "counted as error\n";
				log_write(thinfo.name, sTRXID, "  error", "[53] response is empty");
				log_write(thinfo.name, sTRXID, "  solution", "url will be send again when date changed");
				sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 53, flagdesc = 'response is empty' WHERE dna = '" + sDNA + "' LIMIT 1";
				gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL);

				// check and execute queries if exist upon receiving delivery report without success identifier
				for (i = 0; i < GIMAXSQLEXEC; i++)
				{
					if (gsiniexec_sql_fail[i] != "")
					{
						sSQL = sql_variable2value(gsiniexec_sql_fail[i], sDNA, sTRXID, sMSISDN, sSID, sKeyword, sOwner, sURLResponse);
						if (sSQL != "") { gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL); }
					}
				}
			}
			// if response is not empty > declare response is success or failed > on success delete queue
			else
			{
				// check if response for success identifier
				n = 0;
				sStatus = trimTABCRLF(sURLResponse);
				std::transform(sStatus.begin(), sStatus.end(), sStatus.begin(), ::tolower);
				sTemp = "`" + sStatus + "`";
				for (i = 0; i < giiniresok_cnt; i++) { if (sTemp.find(gsiniresok[i + 1]) != string::npos) { n = 1; break; } }

				// success identifier not found
				if (n == 0)
				{
					sCloseMsg = "counted as error\n";
					log_write(thinfo.name, sTRXID, "  error", "[54] response is invalid [" + sStatus + "]");
					log_write(thinfo.name, sTRXID, "  solution", "manually reset error to process this record again");
					sSQL = "UPDATE " + gsinidb1tabl + " SET flag = 54, flagdesc = 'response is invalid [" + cast_strquote(sStatus) + "]' WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL);

					// check and execute queries if exist upon receiving delivery report without success identifier
					for (i = 0; i < GIMAXSQLEXEC; i++)
					{
						if (gsiniexec_sql_fail[i] != "")
						{
							sSQL = sql_variable2value(gsiniexec_sql_fail[i], sDNA, sTRXID, sMSISDN, sSID, sKeyword, sOwner, sURLResponse);
							if (sSQL != "") { gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL); }
						}
					}
				}
				// success identifier found
				else
				{
					log_write(thinfo.name, sTRXID, "  success", "response is success");
					sSQL = "DELETE FROM " + gsinidb1tabl + " WHERE dna = '" + sDNA + "' LIMIT 1";
					gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL);

					// check and execute queries if exist upon receiving delivery report with success identifier
					for (i = 0; i < GIMAXSQLEXEC; i++)
					{
						if (gsiniexec_sql_sces[i] != "")
						{
							sSQL = sql_variable2value(gsiniexec_sql_sces[i], sDNA, sTRXID, sMSISDN, sSID, sKeyword, sOwner, sURLResponse);
							if (sSQL != "") { gqueEXEC.put(thinfo.name, sTRXID, "  execute", sSQL); }
						}
					}
				}
			}

			// check if response must be forwarded
			if (gbinifwdenableonempty || gbinifwdenableonerror || gbinifwdenableonsuccess)
			{
				gcolKwResponse.item(sKeyword, sFwdRcpName, sFwdMethod, sFwdAddress, sFwdTimeout);

				// initial value for forwading
				sURL = sFwdAddress;
				sDATA = "&trxid=" + sTRXID + "&msisdn=" + sMSISDN + "&serviceid=" + sSID + "&raw=" + curl_easy_escape(NULL, sURLResponse.c_str(), 0);

				// if forward address contain parameters then separate it into part that contain url only and parameters only
				t = sFwdAddress.find("?");
				if (t != string::npos)
				{
					sURL = sFwdAddress.substr(0, int(t));
					sDATA = sFwdAddress.substr(int(t) + 1) + sDATA;
				}

				log_write(thinfo.name, sTRXID, "  fwd to", sURL);
				log_write(thinfo.name, sTRXID, "  fwd data", sURL);

				isSent = false;
				sFwdResponse = "";
				n = cast_str2int(sFwdTimeout);
				for (i = 0; i < GIMAXTRYSENDURL; i++)
				{
					isSent = objURL.send(sMethod, sURL, sDATA, n, sFwdResponse, dElapsed);
					if (isSent == true)
					{
						log_write(thinfo.name, sTRXID, "  fwd " + sMethod, "success, attempt " + cast_int2str(i + 1) + " of " + cast_int2str(GIMAXTRYSENDURL) + " (" + cast_dbl2str(dElapsed) + " sec)");
						log_write(thinfo.name, sTRXID, "  fwd response", sFwdResponse);
						break;
					}
					else
					{
						log_write(thinfo.name, sTRXID, "  fwd " + sMethod, "failed, attempt " + cast_int2str(i + 1) + " of " + cast_int2str(GIMAXTRYSENDURL) + " (" + cast_dbl2str(dElapsed) + " sec)");
						log_write(thinfo.name, sTRXID, "  fwd response", sFwdResponse);
						usleep(giinisleep2);
					}
				}
				if (isSent == false)
				{
					log_write(thinfo.name, sTRXID, "  fwd error", "[55] cannot forward response");
					log_write(thinfo.name, sTRXID, "  fwd solution", "admin must manually forward url above");
				}
			}
			gqueEXEC.put(thinfo.name, sTRXID, "finish", sCloseMsg);
		}

		// position to skip current queue and process next queue
		posNextQueue:;
		if (thinfo.type == "pull") { time(&gthinfoPULL[thinfo.number].check); } else { time(&gthinfoPUSH[thinfo.number].check); }
	}
	pthread_exit(0);
}

// THREADING, UPDATER ############################################################################################################
void *thread_updater(void *arg)
{
	string sTHR, sTRXID, sSubject, sSQL;
	while (true)
	{
		sTHR = "";
		sTRXID = "";
		sSubject = "";
		sSQL = "";

		// get item from queue
		if (gqueEXEC.get(sTHR, sTRXID, sSubject, sSQL) == false)
		{
			if (gTerminateApplication == true) { break; }
			usleep(giinisleep2);
			goto posNextQueue;
		}

		// check if retrieved item does not contain query but act as closing log
		std::transform(sSubject.begin(), sSubject.end(), sSubject.begin(), ::tolower);
		if (sSubject == "finish")
		{
			log_write(sTHR, sTRXID, "finish", sSQL);
			goto posNextQueue;
		}

		// log query that about going to be executed
		log_write(sTHR, sTRXID, sSubject, sSQL);

		// execute query retrieved from queue
		if (db_query(1, sSQL) == 0)
		{
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

posExitThread:;
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
	string s = "";
	string ss = "";
	string sOwner = "";
	string sKeyword = "";
	string sURL = "";
	string sMTD = "";
	string sTemp = "";
	string sTOUT = "";
	int Q = 0;

	// transform raw xml keyword string into upper case to avoid case-sensitive problems
	transform(psKeywords.begin(), psKeywords.end(), psKeywords.begin(), ::tolower);

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
				sTOUT = xml_get_value(sTemp, "timeout");
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLTransmit, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
					sTOUT = xml_get_value(sTemp, "timeout");
				}
				gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL, sTOUT);

				sTemp = xml_get_value(psURLResponse, sOwner);
				sMTD = xml_get_value(sTemp, "method");
				sURL = xml_get_value(sTemp, "address");
				sTOUT = xml_get_value(sTemp, "timeout");
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLResponse, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
					sTOUT = xml_get_value(sTemp, "timeout");
				}
				gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL, sTOUT);
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
				sKeyword = "";
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
						sTOUT = xml_get_value(sTemp, "timeout");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLTransmit, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
							sTOUT = xml_get_value(sTemp, "timeout");
						}
						gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL, sTOUT);

						sTemp = xml_get_value(psURLResponse, sOwner);
						sMTD = xml_get_value(sTemp, "method");
						sURL = xml_get_value(sTemp, "address");
						sTOUT = xml_get_value(sTemp, "timeout");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLResponse, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
							sTOUT = xml_get_value(sTemp, "timeout");
						}
						gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL, sTOUT);

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

	sTemp1 = xml_get_value(sText, "log_per_thread");
	std::transform(sTemp1.begin(), sTemp1.end(), sTemp1.begin(), ::tolower);
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

	// get queries that should be executed upon receiving response with success identifier ---------------------------------------
	sTemp1 = xml_get_value(sText, "response");
	sTemp2 = xml_get_value(sTemp1, "execute_on_success");
		giiniexec_sql_sces_cnt = 0;
		for (i = 0; i < GIMAXSQLPULLNPUSH; i++)
		{
			sSQL = xml_get_value(sTemp1, "query" + cast_int2str(i + 1));
			if (sSQL == "") { break; }
			giiniexec_sql_sces_cnt = giiniexec_sql_sces_cnt + 1;
			gsiniexec_sql_sces[giiniexec_sql_sces_cnt] = sSQL;
		}

	// get queries that should be executed upon receiving response with fail identifier ------------------------------------------
	sTemp1 = xml_get_value(sText, "response");
	sTemp2 = xml_get_value(sTemp1, "execute_on_fail");
		giiniexec_sql_fail_cnt = 0;
		for (i = 0; i < GIMAXSQLPULLNPUSH; i++)
		{
			sSQL = xml_get_value(sTemp1, "query" + cast_int2str(i + 1));
			if (sSQL == "") { break; }
			giiniexec_sql_fail_cnt = giiniexec_sql_fail_cnt + 1;
			gsiniexec_sql_sces[giiniexec_sql_fail_cnt] = sSQL;
		}

	//---------------------------------------------------------
	sTemp1 = xml_get_value(sText, "owner_keyword");
	sTemp2 = xml_get_value(sText, "transmitter");
	sTemp3 = xml_get_value(sText, "response");
	sTemp3 = cast_str2bool(xml_get_value(sTemp3, "forward"));
		gbinifwdenable = cast_str2bool(xml_get_value(sTemp3, "enabled"));

	sTemp3 = xml_get_value(sText, "response");
	sTemp3 = xml_get_value(sTemp3, "recipient");
		setting_get_keywords(sTemp1, sTemp2, sTemp3);


	// get transmitter default destination ---------------------------------------------------------------------------------------
	sTemp1 = xml_get_value(sText, "transmitter");
	sTemp2 = xml_get_value(sTemp1, "default");
		gsinidefaultmtd = xml_get_value(sTemp2, "method");
		gsinidefaulturl = xml_get_value(sTemp2, "address");
		gsinidefaultout = xml_get_value(sTemp2, "timeout");

	// get values for response setting - get transmitting success identifier -----------------------------------------------------
	sTemp1 = xml_get_value(sText, "response");
		giiniresok_cnt = 0;
		for (i = 0; i < GIMAXOKRESPONSE; i++)
		{
			sTemp2 = xml_get_value(sTemp1, "success_identifier" + cast_int2str(i + 1));
			if (sTemp2 == "") { break; }
			giiniresok_cnt = giiniresok_cnt + 1;
			std::transform(sTemp2.begin(), sTemp2.end(), sTemp2.begin(), ::tolower);
			gsiniresok[giiniresok_cnt] = sTemp2;
		}
}

// SUBROUTINE, SETTING, SHOW VALUES ##############################################################################################
void setting_show_values()
{
	int i;
	string sVal1, sVal2, sVal3, sVal4, sVal5;

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
	while (1)
	{
		if (gcolKwTransmit.read(sVal1, sVal2, sVal3, sVal4, sVal5) == true)
		{
			i = i + 1;
			cout << "    " << i << ": " << sVal1 << ": " << sVal2 << ": " << sVal3 << ": " << sVal4 << ": " << sVal5 << endl;
		}
		if (gcolKwTransmit.next() == false) { break; }
	}

	// show keywords mapping
	cout << endl;
	cout << "keywords mapping - forwarding response" << endl;
	cout << "number; keyword; owner; method; url" << endl;
	cout << "===================================================================" << endl;
	i = 0;
	gcolKwResponse.first();
	while (1)
	{
		if (gcolKwResponse.read(sVal1, sVal2, sVal3, sVal4, sVal5) == true)
		{
			i = i + 1;
			cout << "    " << i << ": " << sVal1 << ": " << sVal2 << ": " << sVal3 << ": " << sVal4 << ": " << sVal5 << endl;
		}
		if (gcolKwResponse.next() == false) { break; }
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
	if (REGION_LOAD_SETTING)
	{
		// setting - open file setting and get its content
		cout << endl;
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
	if (REGION_INITIALIZE_DB)
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
	if (REGION_START_MONITORING)
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
	if (REGION_CLEANUP_PREVIOUS)
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
					log_write("app", "queue pull", "finish", "counted as error\n");
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
								log_write("app", "queue pull", "finish", "counted as error\n");
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
					log_write("app", "queue push", "finish", "counted as error\n");
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
								log_write("app", "queue push", "finish", "counted as error\n");
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

		// check threads status - transmitters - push
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