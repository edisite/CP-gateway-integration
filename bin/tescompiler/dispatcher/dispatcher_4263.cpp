/*
COMPILE COMMAND
	g++ -o [exe name]  [cpp name]      -lcurl -I /usr/include/mysql /usr/lib/libmysqlclient_r.so
	g++ -o files2mysql files2mysql.cpp -lcurl -I /usr/include/mysql /usr/lib/libmysqlclient_r.so
    compiling in production:
    g++ -lcurl -o files2mysql files2mysql.cpp -I /usr/include/mysql /usr/lib/libmysqlclient_r.so

LOCAL VARIABLE NAMING
	i - INTEGER
	u - UNSIGNED INTEGER
	s - STRING
	c - CHAR
	t - OBJECT/CLASS/DATABASE/FILE/STRUCT
	is - INTEGER/BOOLEAN

	variables with single character as it's name usually used as temporary or
	trivial information so that it is free naming.

LOCAL CONSTANT NAMING
	Same as variable naming but using capital letter for all characters

GLOBAL PREFIX
	GLOBAL VARIABLE PREFIX "g"
	GLOBAL CONSTANT PREFIX "G"
*/
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <stddef.h>
#include <sys/types.h>
#include <dirent.h>
#include <string>
#include <algorithm>
#include <iostream>
#include <sstream>
#include <fstream>
#include <time.h>
#include <curl/curl.h>
#include <curl/types.h>
#include <curl/easy.h>
#include <mysql.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <pthread.h>
#include <errmsg.h>
#include <math.h>

using namespace std;

// GLOBAL VARIABLES ===============================================================================================================
static string gsurl_res_buffer;					// store url response here
static char   gcurl_res_error[CURL_ERROR_SIZE];	// store url error here

unsigned long giCntDone;	// number of files processed and succeed
unsigned long giCntFail1;	// number of files processed and failed and moved into trash directory
unsigned long giCntFail2;	// number of files processed and failed and cannot moved into trash directory

bool 	gisMainON;
bool	gisSockON;

int		giiniportno;
int		giinisleep1;
int		giinisleep2;

string	gsinidirsrc;
string	gsinidirdmp;
string	gsinidirlog;
string	gsinilogptn;
string	gsinilogflo;

MYSQL	*gdb1CN;
MYSQL	*gdb2CN;
MYSQL	gdb1OBJ;
MYSQL	gdb2OBJ;
MYSQL_RES *tdbrsSTAT;		// mysql recordset - statistic table
MYSQL_ROW tdbrowSTAT;		// mysql row - statistic table

MYSQL_RES *tdbrsOUT;		// mysql recordset - storage table
MYSQL_ROW tdbrowOUT;		// mysql row - storage table

string	gsinidb1addr;
string	gsinidb1name;
string	gsinidb1user;
string	gsinidb1pswd;
string	gsinidb1tabl;
string	gsinidb1stat;

string	gsinidb2addr;
string	gsinidb2name;
string	gsinidb2user;
string	gsinidb2pswd;
string	gsinidb2tabl;

string	gsinidb1fdtm;	// records expired, field name which show date and time when the record inserted
string	gsinidb1xprd;	// records expired, integer value (0, 1, 2, 3...) indicate how many days record will be stored in database

FILE * tfileLog;

string gsStampStarted;		// monitoring, store date and time application started
time_t gtmLived;			// monitoring, store amount of seconds from application started
string gsCurrentLogPath;	// monitoring, store application log file's name that currently in use

// GLOBAL CONSTANTS ===============================================================================================================
const bool		CHECK_FILE_CONTENT = true;
const int		GIMAXCNTCERR = 100;

// FUNCTION, CONVERT TYPES ########################################################################################################
int cast_str2int(string sParam)
{
	int irc;
	try { irc = atoi(sParam.c_str()); }	catch (int e) { irc = 0; }
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

string trimTABCRLF(string sParam)
{
	sParam.erase(remove(sParam.begin(), sParam.end(), '\t'), sParam.end());
	sParam.erase(remove(sParam.begin(), sParam.end(), '\n'), sParam.end());
	sParam.erase(remove(sParam.begin(), sParam.end(), '\r'), sParam.end());
	return sParam;
}

string str_quote(string sParam)
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

string str_replace(string sText, string sOld, string sNew)
{
    size_t nPosition = 0;
    int nOld = sOld.length();
    int nNew = sNew.length();
    while (true)
    {
	    nPosition = sText.find(sOld, nPosition);
	    if (nPosition == string::npos) { break; }
        sText.replace(nPosition, nOld, sNew);
        nPosition = nPosition + nNew;
    }
    return sText;
}

// SUBROUTINE, LOG FILE, WRITE INTO LOG FILE ######################################################################################
void log_write(string sTrxID, string sSubject, string sMessage)
{
	time_t dtmRaw;
	struct tm *dtmNow;
	char cDateCurrent[11];
	char cTimeCurrent[9];
	char cFilename[255];
	char cName[255];
	static char cLastDate[11];

	// get current date and time
	time(&dtmRaw);
	dtmNow = localtime(&dtmRaw);
	strftime(cDateCurrent, 11, "%Y-%m-%d", dtmNow);
	strftime(cTimeCurrent, 9, "%H:%M:%S", dtmNow);

	/*
	if last date used not equal to current date, then close existing log file handle, and then open new log file
	with current date as part as its file name, this is how to create log file with daily name in subroutine without
	opening-closing the file each time subroutine been called
	*/
	if (strcmp(cLastDate, cDateCurrent) != 0)
	{
		strcpy(cLastDate, cDateCurrent);
		if (tfileLog != NULL) { fclose(tfileLog); }
		strftime(cName, 255, gsinilogptn.c_str(), dtmNow);
		strcpy(cFilename, gsinidirlog.c_str());
		strcat(cFilename, cName);
		gsCurrentLogPath = cFilename;
		tfileLog = fopen(cFilename, "a");
	}

	// write message into log file and flush it right away, if there was Segmentation Error - a line below also a candidate who might triggered it
	if (tfileLog != NULL)
	{
		fprintf(tfileLog, "%s %s|%s|nothread|%-8s|%-15s|%s\n", cDateCurrent, cTimeCurrent, gsinilogflo.c_str(), sTrxID.c_str(), sSubject.c_str(), sMessage.c_str());
		fflush(tfileLog);
	}
}

// FUNCTION, READ XML FILE AND RETURN ITS CONTENT #################################################################################
string xml_read_file(string sFilename)
{
	int i = 0;
	ifstream objFile;
	string sLine;
	string strRet =  "";
	objFile.open(sFilename.c_str());
		if (objFile.is_open())
		{
			while(!objFile.eof())
			{
				getline(objFile, sLine);
				strRet = strRet + sLine;
			}
		}
	objFile.close();
	return strRet;
}


// FUNCTION, EXTRACT XML FIELD FOR SPECIFIED KEY ##################################################################################
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

// FUNCTION, DELETE FILE ##########################################################################################################
int file_delete(string sPath)
{
	if (remove(sPath.c_str()) == 0) { return 0; }
	return -1;
}

// FUNCTION, TRASH FILE ###########################################################################################################
string file_trash(string sPath, string sFilename)
{
	int n;
	string sPath2;
	sPath2 = gsinidirdmp + sFilename;

	n = rename(sPath.c_str(), sPath2.c_str());
	if (n == 0)
	{
		giCntFail1 = giCntFail1 + 1; return "succeed";
	}
	else
	{
		giCntFail2 = giCntFail2 + 1; return "failed";
	}
}

// SUBROUTINE, SETTING, GET VALUES ################################################################################################
void setting_get_values(string sText)
{
	string sTemp;
	giiniportno = cast_str2int(xml_get_value(sText, "app_port_num"));

	sTemp = xml_get_value(sText, "db_queue");
		gsinidb1addr = xml_get_value(sTemp, "host");
		gsinidb1name = xml_get_value(sTemp, "database");
		gsinidb1user = xml_get_value(sTemp, "user");
		gsinidb1pswd = xml_get_value(sTemp, "password");
		gsinidb1tabl = xml_get_value(sTemp, "table");
		gsinidb1stat = xml_get_value(sTemp, "statistic_table");
		gsinidb1fdtm = xml_get_value(sTemp, "expire_field");
		gsinidb1xprd = xml_get_value(sTemp, "expired_days");

	sTemp = xml_get_value(sText, "db_storage");
		gsinidb2addr = xml_get_value(sTemp, "host");
		gsinidb2name = xml_get_value(sTemp, "database");
		gsinidb2user = xml_get_value(sTemp, "user");
		gsinidb2pswd = xml_get_value(sTemp, "password");
		gsinidb2tabl = xml_get_value(sTemp, "table");

	sTemp = xml_get_value(sText, "directories");
		gsinidirsrc = xml_get_value(sTemp, "data_files");
		gsinidirdmp = xml_get_value(sTemp, "dump_files");
		gsinidirlog = xml_get_value(sTemp, "logs_files");

	sTemp = xml_get_value(sText, "sleep");
		giinisleep1 = cast_str2int(xml_get_value(sTemp, "fast"));
		giinisleep2 = cast_str2int(xml_get_value(sTemp, "long"));

	gsinilogptn = xml_get_value(sText, "log_naming_pattern");
	gsinilogflo = xml_get_value(sText, "log_flow_descriptor");
}

// SUBROUTINE, SETTING, SHOW VALUES ###############################################################################################
void setting_show_values()
{
	cout << "    mysql in address           : " << gsinidb1addr << endl;
	cout << "    mysql in database name     : " << gsinidb1name << endl;
	cout << "    mysql in table name        : " << gsinidb1tabl << endl;
	cout << "    mysql in statistic table   : " << gsinidb1stat << endl;
	cout << "    mysql in user name         : " << gsinidb1user << endl;
	cout << "    mysql in authentication    : " << gsinidb1pswd << endl;
	cout << "    mysql in expired field     : " << gsinidb1fdtm << endl;
	cout << "    mysql in expired after day : " << gsinidb1xprd << endl;
	cout << "    mysql out address          : " << gsinidb2addr << endl;
	cout << "    mysql out database name    : " << gsinidb2name << endl;
	cout << "    mysql out table name       : " << gsinidb2tabl << endl;
	cout << "    mysql out user name        : " << gsinidb2user << endl;
	cout << "    mysql out authentication   : " << gsinidb2pswd << endl;
	cout << "    directory of files         : " << gsinidirsrc << endl;
	cout << "    directory of trash         : " << gsinidirdmp << endl;
	cout << "    directory of log file      : " << gsinidirlog << endl;
	cout << "    log file naming pattern    : " << gsinilogptn << endl;
	cout << "    log flow descriptor        : " << gsinilogflo << endl;
	cout << "    sleep interval             : " << giinisleep1 << endl;
}

// SUBROUTINE, SETTING, LOG VALUES ################################################################################################
void setting_log_values(string sPathSetting)
{
	log_write("started", "init file",		sPathSetting);
	log_write("setting", "db address, in",	gsinidb1addr);
	log_write("setting", "db name, in",		gsinidb1name);
	log_write("setting", "db stat, in",		gsinidb1stat);
	log_write("setting", "db table, in",	gsinidb1tabl);
	log_write("setting", "db user, in",		gsinidb1user);
	log_write("setting", "exp field, in",	gsinidb1fdtm);
	log_write("setting", "exp days, in",	gsinidb1xprd);
	log_write("setting", "db address, out",	gsinidb2addr);
	log_write("setting", "db name, out",	gsinidb2name);
	log_write("setting", "db table, out",	gsinidb2tabl);
	log_write("setting", "db user, out",	gsinidb2user);
	log_write("setting", "dir files",		gsinidirsrc);
	log_write("setting", "dir trash",		gsinidirdmp);
	log_write("setting", "log dir",			gsinidirlog);
	log_write("setting", "log name",		gsinilogptn);
	log_write("setting", "log flow dcpt",	gsinilogflo);
	log_write("setting", "sleep ms",		cast_int2str(giinisleep1));
}

// FUNCTION, GET DATABASE CONNECTION, MYSQL #######################################################################################
int db_connect(int index)
{
	int i;

	// initialize database connection structure
	if (index == 1)
	{
		try { mysql_close(gdb1CN); } catch(int e) {}
	}
	else
	{
		try { mysql_close(gdb2CN); } catch(int e) {}
	}

	// try to make database connection
	for(i = 0; i < 5; i++)
	{
		if (index == 1)
		{
			gdb1CN = mysql_real_connect(&gdb1OBJ, gsinidb1addr.c_str(), gsinidb1user.c_str(), gsinidb1pswd.c_str(), gsinidb1name.c_str(), 0, NULL, 0);
			if (gdb1CN) { return 0; } else { usleep(giinisleep2); }
		}
		else
		{
			gdb2CN = mysql_real_connect(&gdb2OBJ, gsinidb2addr.c_str(), gsinidb2user.c_str(), gsinidb2pswd.c_str(), gsinidb2name.c_str(), 0, NULL, 0);
			if (gdb2CN) { return 0; } else { usleep(giinisleep2); }
		}
	}
	return -1;
}

// FUNCTION, EXECUTE QUERY, MYSQL ################################################################################################
int db_query(int index, string sSQL)
{
	int n;
	if (index == 1)
	{
		n = mysql_query(gdb1CN, sSQL.c_str());
	}
	else
	{
		n = mysql_query(gdb2CN, sSQL.c_str());
	}

	// if query failed, try once more
	if (n != 0)
	{
		// refresh database connection, if failed - terminate application
		n = db_connect(index);
		if (n != 0) { return -1; }

		// try to execute query again, if failed - terminate application
		if (index == 1)
		{
			n = mysql_query(gdb1CN, sSQL.c_str());
		}
		else
		{
			n = mysql_query(gdb2CN, sSQL.c_str());
		}
		if (n != 0) { return -1; }
	}
	return 0;
}

// FUNCTION, EVALUATE QUERY, MYSQL ###############################################################################################
int db_query_n_evaluate(int index, string sSQL, bool &result, string &svar1, string &svar2, string &svar3)
{
	/*
	return values
		return -1, 0, 1
		result true, false

		return	result	description
			-1	false	query failed
			 0	false	query succeed, but record is not found
			 1	false	query succeed, evaluation in query returning false condition
			 1	true	query succeed, evaluation in query returning true condition
	*/
	int n;
	string sRes = "";
	MYSQL_RES *tdbRS;		// mysql recordset
	MYSQL_ROW tdbROW;		// mysql row
	result = false;
	svar1 = "";
	svar2 = "";
	svar3 = "";

	if (index == 1)
	{
		n = mysql_query(gdb1CN, sSQL.c_str());
	}
	else
	{
		n = mysql_query(gdb2CN, sSQL.c_str());
	}

	// if query failed, try once more
	if (n != 0)
	{
		// refresh database connection, if failed - terminate application
		n = db_connect(index);
		if (n != 0) { return -1; }

		// try to execute query again, if failed - terminate application
		if (index == 1)
		{
			n = mysql_query(gdb1CN, sSQL.c_str());
		}
		else
		{
			n = mysql_query(gdb2CN, sSQL.c_str());
		}
		if (n != 0) { return -1; }
	}

	// evaluate resultset
	if (index == 1) { tdbRS = mysql_store_result(gdb1CN); } else { tdbRS = mysql_store_result(gdb2CN); }
	if (tdbROW = mysql_fetch_row(tdbRS))
	{
		n = mysql_num_fields(tdbRS);
		if (n > 1) { svar1 = (string)tdbROW[1]; }
		if (n > 2) { svar2 = (string)tdbROW[2]; }
		if (n > 3) { svar3 = (string)tdbROW[3]; }
		sRes = (string)tdbROW[0]; std::transform(sRes.begin(), sRes.end(), sRes.begin(), ::tolower);
		if (sRes == "0" || sRes == "no" || sRes == "false") { result = false; } else { result = true; }
		mysql_free_result(tdbRS);
		return 1;
	}
	else
	{
		mysql_free_result(tdbRS);
		result = false;
		return 0;
	}
}

// FUNCTION, REPLACE VARIABLE IN QUERY STRING WITH VALUES ########################################################################
string db_query_set_value(string SQL, string svar1, string svar2, string svar3)
{
	SQL = str_replace(SQL, "[var1]", svar1);
	SQL = str_replace(SQL, "[var2]", svar2);
	SQL = str_replace(SQL, "[var3]", svar3);
	return SQL;
}

// FUNCTION, CHECK IF GIVEN DIRECTORY IS FILE-WRITE-ABLE AND FILE-DELETE-ABLE ####################################################
int check_dir_acc(string sdirpath)
{
	/*
	0 - application have all access required
	-1 - application don't have write file access
	-2 - application don't have open/read file access
	-3 - application don't have delete file access
	*/
	string sFName;
	string sLine1;
	string sLine2;
	char cLine2[255];
	sLine1 = "testing directory access";

	FILE *aFile;
	sFName = sdirpath + "_temp.dat";

	// check if application have write access to the directory
	try
	{
		aFile = fopen(sFName.c_str(), "w");
			if (aFile == NULL) { return -1; }
			fprintf(aFile, "%s", sLine1.c_str());
		fclose(aFile);
	}
	catch (int e) { return -1; }

	// check if application have open/read file access to the directory
	try
	{
		aFile = fopen(sFName.c_str(), "r");
			if (aFile == NULL) { return -1; }
			fgets(cLine2, sLine1.length(), aFile);
		fclose(aFile);

		sLine2 = cLine2;
		if (sLine2.compare(sLine2) != 0) { return -2; }
	}
	catch (int e) { return -1; }

	// check if application have delete file access to the directory
	if (remove(sFName.c_str()) != 0) { return -3; }

	// all access granted
	return 0;
}

// FUNCTION, VALIDATE DIRECTORY NAMING ###########################################################################################
string check_dir_name(string sdirpath)
{
	string s1;
	string s2 = "/";
	try
	{
		s1 = sdirpath.at(sdirpath.length() - 1);
		if (s1.compare(s2) == 0) { return sdirpath; }
		else
		{
			sdirpath = sdirpath + s2;
			return sdirpath;
		}
	}
	catch (int terr) { return sdirpath; }
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
		if (gisMainON == false)
		{
			cout << "off signal received, closing application" << endl;
			log_write("ok", "socket", "off signal received, closing application");
			gisSockON = false;
			return NULL;
		}

		// initialize socket
		ihndServer = socket(AF_INET, SOCK_STREAM, 0);
		if (ihndServer < 0)
		{
			cout << "#ERR: cannot initialize port" << endl;
			log_write("error", "socket", "cannot initialize port");
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
			cout << "#ERR: cannot open port " << giiniportno << endl;
			log_write("error", "socket", "cannot open port " + cast_int2str(giiniportno));
			gisSockON = false;
			return NULL;
		}

		// keep looping until exit signaled
		log_write("succeed", "socket", "going into listen mode...");
		while (1)
		{
			if (gisMainON == false)
			{
				cout << "off signal received, closing application" << endl;
				log_write("ok", "socket", "off signal received, closing application");
				close(ihndClient);
				close(ihndServer);
				gisSockON = false;
				return NULL;
			}

			// listen to incoming connection
			listen(ihndServer, 5);
			tlenClient = sizeof(tsckClient);
			ihndClient = accept(ihndServer, (struct sockaddr *) &tsckClient, &tlenClient);
			if (ihndClient < 0)
			{
				log_write("error", "socket", "error on accepting incoming connection, rebind socket...");
				break;
			}

			// retrieve incoming message
			bzero(buffer, 256);
			n = read(ihndClient, buffer, 255);
			if (n < 0)
			{
				log_write("error", "socket", "error on reading incoming message, rebind socket...");
				break;
			}

			// if message is empty
			if (n == 0)
			{
				sReply = "keyword is empty"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("app", "sck err", "error on replying empty keyword message to client, rebind socket...");
					break;
				}
			}

			// process incoming message
			sKey = "";
			for (i = 0; i < 256; i++) { if (buffer[i] == 0 || buffer[i] == 9 || buffer[i] == 10 || buffer[i] == 13) { break; } else { sKey = sKey + buffer[i]; } }

			if (sKey.compare("commands") == 0)
			{
				sReply = "commands ping stop processed trashed_ok thrased_err thrashed_all time_start time_lived log_path";
				iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("log_path") == 0)
			{
				sReply = gsCurrentLogPath; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("ping") == 0)
			{
				sReply = "alive"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("stop") == 0)
			{
				sReply = "0|stoping"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
				}

				log_write("ok", "socket", "off signal received, closing application");
				close(ihndClient);
				close(ihndServer);
				gisSockON = false;
				return NULL;
			}
			else if (sKey.compare("processed") == 0)
			{
				sReply = cast_int2str(giCntDone); iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("trashed_ok") == 0)
			{
				sReply = cast_int2str(giCntFail1); iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("trashed_err") == 0)
			{
				sReply = cast_int2str(giCntFail2); iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("trashed_all") == 0)
			{
				sReply = cast_int2str(giCntFail1 + giCntFail2); iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else if (sKey.compare("time_start") == 0)
			{
				sReply = gsStampStarted; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
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
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
					break;
				}
			}
			else
			{
				sReply = "unknown keyword"; iReply = sReply.length();
				n = write(ihndClient, sReply.c_str(), iReply);
				if (n < 0)
				{
					log_write("error", "socket", "error on reply '" + sKey + "' message to client, rebind socket...");
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
		log_write("error", "socket", "[51] closing port failed");
		return false;
	}

	// resolve remote name
	sServer = gethostbyname("127.0.0.1");
	if (sServer == NULL) {
		log_write("error", "socket", "[52] closing port failed");
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
		log_write("error", "socket", "[53] closing port failed");
		return false;
	}

	// send message to remote connection
	n = write(ihndClient, sMsg.c_str(), iMsg);
	if (n < 0)
	{
		log_write("error", "socket", "[54] closing port failed");
		return false;
	}
	close(ihndClient);
	return true;
 }

 // MAIN SUBROUTINE, APPLICATION START HERE ######################################################################################
int main (int argc, char **argv)
{
	const char cdir1[5] = ".";	// constants, ignore this when listing directory content
	const char cdir2[5] = "..";	// constants, ignore this when listing directory content

	int i, n;					// common numeric variables
	int cntCERR = 0;
	bool isTrue = false;

	int iCNN;					// store database connection number in integer
	string sCNN;				// store database connection number in string
	string sXML;				// store content of xml file
	string sSQL;				// store temporary sql command
	string sELM;				// store XML element name
	string sEVL;				// store evaluation result from executing queue
	string sSBQ;				// store sub-query name to be written in log file
	string sTemp;				// store temporary processed string
	string sFlow;				// store application flow
	string sQUERIES;			// store all queries taken from XML
	string sVar1;
	string sVar2;
	string sVar3;

	string sCN1;				// temporary string to compare first database host with the second database host
	string sCN2;
	string sDB1;				// temporary string to compare first database name with the second database host
	string sDB2;
	string sUS1;				// temporary string to compare first database user with the second database user
	string sUS2;

	string sinTrxID;			// store transaction id for currently processed file
	string sinMSISDN;			// store msisdn

	DIR *aDir;					// directory handler, to open directory where text files stored
	FILE *tfileTmp;				// file handler, just to try to open setting file
	struct dirent *aFile;		// file handler, used to read directory contents

	char cFilename[255];		// file name when reading directory contents


	pthread_t thidSCK;			// thread id for monitoring port, used once when creating thread

	time_t dtmRaw;				// to get current date and time
	struct tm *dtmNow;			// to get current date and time
	char cTemp1[3];				// temp char buffer, store date in day format (1 - 31) only
	char cTemp2[22];			// temp char buffer, store date in standard long date format

	int ddNow = 0;				// store day date (1 - 31) to check day switch
	int ddLast = 0;				// store day date (1 - 31) to check day switch

	// check if application called with sufficient parameters, if not - terminate application
	if (argc < 2)
	{
		cout << "#ERR: parameter required (usage " << argv[0] << " [setting file])" << endl;
		return 0;
	}

	// check if application called with valid parameters, if not - terminate application
	tfileTmp = fopen(argv[1], "r");
		if (!tfileTmp)
		{
			cout << "#ERR: cannot open setting file " << argv[1] << endl;
			return 0;
		}
	fclose(tfileTmp);

	// mark application starting date and time
	time(&dtmRaw); dtmNow = localtime(&dtmRaw); strftime(cTemp2, 22, "%Y-%m-%d %H:%M:%S", dtmNow);
	gsStampStarted = cTemp2;
	gtmLived = time(NULL);

	// open application initialization file and store it's content into string variable
	cout << "application initialization file [" << argv[1] << "]" << endl;
	sXML = xml_read_file(argv[1]);
	setting_get_values(sXML);

	// check setting values - numeric and default
	n = cast_str2int(gsinidb1xprd);
	if (n == 0) { gsinidb1xprd = "7"; }
	if (giinisleep1 == 0) { giinisleep1 = 1000000; } // 1 second for default

	// check setting values - required setting
	if (gsinidb1fdtm == "")
	{
		sTemp = "#ERR: field name for date and time when record inserted is not specified, application aborted";
		log_write("error", "setting", sTemp);
		cout << sTemp << endl;
		return 0;
	}

	// check setting value - directory to store log files
	if ("check" == "check")
	{
		aDir = opendir(gsinidirlog.c_str());
			if (aDir == NULL)
			{
				cout << "#ERR: directory to store log files is not exist, application aborted" << endl;
				cout << "    " << gsinidirlog << endl;
				return 0;
			}
		closedir(aDir);
		gsinidirlog = check_dir_name(gsinidirlog);
		n = check_dir_acc(gsinidirlog);
		if (n == -1)
		{
			cout << "#ERR: directory to store log files is locked for creating file, application aborted" << endl;
			cout << "    " << gsinidirlog << endl;
			return 0;
		}
		if (n == -2)
		{
			cout << "#ERR: directory to store log files is locked for open/reading file, application aborted" << endl;
			cout << "    " << gsinidirlog << endl;
			return 0;
		}
	}

	// check setting values - directory of text files
	if ("check" == "check")
	{
		aDir = opendir(gsinidirsrc.c_str());
			if (aDir == NULL)
			{
				sTemp = "#ERR: directory of files [" + gsinidirsrc + "] is not exist, application aborted";
				log_write("error", "setting", sTemp);
				cout << sTemp << endl;
				return 0;
			}
		closedir(aDir);
		gsinidirsrc = check_dir_name(gsinidirsrc);
		n = check_dir_acc(gsinidirsrc);
		if (n == -2)
		{
			sTemp = "#ERR: directory of files [" + gsinidirsrc + "] locked for open/reading, application aborted";
			log_write("error", "setting", sTemp);
			cout << sTemp << endl;
			return 0;
		}
		if (n == -3)
		{
			sTemp = "#ERR: directory of files [" + gsinidirsrc + "] locked for deleting file, application aborted";
			log_write("error", "setting", sTemp);
			cout << sTemp << endl;
			return 0;
		}
	}

	// check setting value - directory to trash text files
	if ("check" == "check")
	{
		aDir = opendir(gsinidirdmp.c_str());
			if (aDir == NULL)
			{
				sTemp = "#ERR: directory of trash files [" + gsinidirdmp + "] is not exist, application aborted";
				log_write("error", "setting", sTemp);
				cout << sTemp << endl;
				return 0;
			}
		closedir(aDir);
		gsinidirdmp = check_dir_name(gsinidirdmp);
		n = check_dir_acc(gsinidirdmp);
		if (n == -1)
		{
			sTemp = "#ERR: directory of trash files [" + gsinidirdmp + "] is locked for creating file, application aborted";
			log_write("error", "setting", sTemp);
			cout << sTemp << endl;
			return 0;
		}
		if (n == -2)
		{
			sTemp = "#ERR: directory of trash files [" + gsinidirdmp + "] is locked for open/reading file, application aborted";
			log_write("error", "setting", sTemp);
			cout << sTemp << endl;
			return 0;
		}
	}

	// show current setting values
	setting_show_values();

	// write to log setting values
	setting_log_values(argv[1]);

	// initializa mysql database library
	mysql_init(&gdb1OBJ);
	mysql_init(&gdb2OBJ);
	mysql_library_init(0, NULL, NULL);

	// make database connection - queue database*/
	if (db_connect(1) != 0)
	{
		log_write("error", "database", "cannot make database connection [queue]");
		log_write("error", "solution", "terminate application\n");
		goto posExitApp;
	}

	// if second connection setting is the same with first connection setting, then just use 1 connection
	sCN1 = cast_str2lower(gsinidb1addr);
	sCN2 = cast_str2lower(gsinidb2addr);
	sDB1 = cast_str2lower(gsinidb1name);
	sDB2 = cast_str2lower(gsinidb2name);
	sUS1 = cast_str2lower(gsinidb1user);
	sUS2 = cast_str2lower(gsinidb2user);

	if (sCN1 == sCN2 && sDB1 == sDB2 && sUS1 == sUS2)
	{
		log_write("setting", "database", "second database is the same with first database, use 1 database connection");
		gdb2CN = gdb1CN;
	}
	else
	{
		log_write("setting", "database", "second database is not the same with first database, use 2 database connections");
		if (db_connect(2) != 0)
		{
			log_write("error", "database", "cannot make database connection [storage]");
			log_write("error", "solution", "terminate application\n");
			goto posExitApp;
		}
	}

	// create a thread for server socket
	gisMainON = true;	// this value must be set before creating thread for socket_open
	gisSockON = true;	// this value must be set before creating thread for socket_open
	if (pthread_create(&thidSCK, NULL, &socket_open, NULL) != 0)
	{
		sTemp = "failed to create monitoring thread, application aborted...";
		log_write("error", "thread", sTemp);
		cout << sTemp << endl;
		return NULL;
	}

	// forever loop start here
	giCntDone = 0;
	giCntFail1 = 0;
	giCntFail2 = 0;
	while (1)
	{
		// check if monitoring socket closed
		if (gisSockON == false)
		{
			log_write("closing", "socket", "terminate application because monitoring socket is closed");
			goto posExitApp;
		}

		// check if date changed, if date changed then delete expired records
		time(&dtmRaw); dtmNow = localtime(&dtmRaw); strftime(cTemp1, 3, "%d", dtmNow);
		ddNow = atoi(cTemp1);

		if (ddNow != ddLast)
		{
			ddLast = ddNow;

			// prepare statistic table for the new day - make sure new statistic record is unique
			sSQL = "SELECT COUNT(*) AS cnt FROM " + gsinidb1tabl + "_stat WHERE DATEDIFF(NOW(), dtm) = 0";
			if (db_query(1, sSQL) == 0)
			{
				tdbrsSTAT = mysql_store_result(gdb1CN);
					if (tdbrsSTAT)
					{
						if (tdbrowSTAT = mysql_fetch_row(tdbrsSTAT))
						{
							sTemp = (tdbrowSTAT[0] ? tdbrowSTAT[0] : "0");
							n = cast_str2int(sTemp);
						}
						else
						{
							log_write("error", "today stat", "error while counting today statistic");
							log_write("error", "solution", "terminate application\n");
							goto posExitApp;
						}
					}
					else
					{
						log_write("error", "today stat", "error while checking today statistic");
						log_write("error", "solution", "terminate application\n");
						goto posExitApp;
					}
				mysql_free_result(tdbrsSTAT);

				if (n > 0)
				{
					log_write("succeed", "today stat", "today statistic already exist");
				}
				else
				{
					for (i = 0; i < 24; i++)
					{
						sSQL = "INSERT INTO " + gsinidb1tabl + "_stat (dtm, tmh) VALUES (NOW(), " + cast_int2str(i) + ");";
						n = db_query(1, sSQL);
						if (n != 0)
						{
							log_write("error", "today stat", "failed to create today statistic record");
							log_write("error", "today stat", sSQL);
							log_write("error", "solution", "terminate application\n");
							goto posExitApp;
						}
					}
					log_write("succeed", "today stat", "today statistic record created");
				}
			}
			else
			{
				log_write("error", "today stat", "error while checking today statistic record existence");
				log_write("error", "solution", "terminate application\n");
				goto posExitApp;
			}

			// delete expired queue
			sSQL = "DELETE FROM " + gsinidb1tabl + " WHERE DATEDIFF(NOW(), " + gsinidb1fdtm + ") > " + gsinidb1xprd;
			if (db_query(1, sSQL) == 0)
			{
				n = mysql_affected_rows(gdb1CN);
				log_write("succeed", "expired", cast_int2str(n) + " expired record(s) has been deleted\n");
			}
			else
			{
				log_write("error", "expired", "failed to delete expired queue");
				log_write("error", "expired", sSQL);
				log_write("error", "solution", "this error can be ignored, continue process\n");
			}
		}

		// check if directory is empty, if it is empty - go to next cycle
		aDir = opendir(gsinidirsrc.c_str());
		if (aDir == NULL) { goto posNextCycle; }

		// start opening directory and grab files
		while ((aFile = readdir(aDir)) != NULL)
		{
			// if monitoring socket is closed - terminate application
			if (gisSockON == false)
			{
				log_write("socket", "closing", "monitoring socket is closed");
				log_write("socket", "closing", "terminate application\n");
				goto posExitApp;
			}

			// if file is invalid then just skip to next file
			strcpy(cFilename, aFile->d_name);
			if ((strcmp(cFilename, cdir1) == 0) || (strcmp(cFilename, cdir2) == 0)) { goto posNextFile; }

			// get file content and store it into variables
			sXML = xml_read_file(gsinidirsrc + cFilename);

			// check file content validity, is xml empty, is transaction id specified, is msisdn specified
			if (CHECK_FILE_CONTENT)
			{
				// invalid content - content not found - skip to next file
				if (sXML == "")
				{
					log_write("error", "filename", cFilename);
					log_write("error", "detail", "content is empty");
					log_write("error", "solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]\n");
					goto posNextFile;
				}

				// invalid content - transaction id not found - skip to next file
				sinTrxID = xml_get_value(sXML, "trxid");
				if (sinTrxID == "")
				{
					log_write("error", "filename", cFilename);
					log_write("error", "detail", "transaction id not found");
					log_write("error", "solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]\n");
					goto posNextFile;
				}

				// invalid content - msisdn not found - skip to next file
				sinMSISDN = xml_get_value(sXML, "msisdn");
				if (sinMSISDN == "")
				{
					log_write("error", "filename", cFilename);
					log_write("error", "detail", "msisdn not found");
					log_write("error", "solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]\n");
					goto posNextFile;
				}
			}

			// begin log
			log_write(sinTrxID, "begin", "");
			log_write(sinTrxID, "  filename", cFilename);

			// execute queries, queries that doesn't have condition or evaluation ------------------------------------------------
			i = 0;
			sQUERIES = xml_get_value(sXML, "queries");
			do {
				i = i + 1;
				sELM = "sql" + cast_int2str(i);
				sTemp = xml_get_value(sQUERIES, sELM);
				if (sTemp == "") { break; }

				// extract db connection number to execute query and the query itself
				sSQL = xml_get_value(sTemp, "sql");
				sCNN = xml_get_value(sTemp, "dbcn");
				iCNN = cast_str2int(sCNN);

				// only execute queries that having connection number and the query itself
				if (iCNN > 0 && sSQL != "")
				{
					// log it first before executing query
					log_write(sinTrxID, "  " + sELM, sCNN + ":" + sSQL);

					// execute query, if failed then write log then exit application
					if (db_query(iCNN, sSQL) != 0)
					{
						log_write(sinTrxID, "  error", "query failed");
						log_write(sinTrxID, "  solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]");
						log_write(sinTrxID, "finish", "error\n");
						goto posNextFile;
					}
				}
			} while (sSQL != "");

			// execute query, one query that evaluate a condition if it is a true or false ---------------------------------------
			sELM = "sql_if";
			sTemp = xml_get_value(sQUERIES, sELM);
			if (sTemp != "")
			{
				sSQL = xml_get_value(sTemp, "sql");
				sCNN = xml_get_value(sTemp, "dbcn");
				iCNN = cast_str2int(sCNN);

				// if evaluation query specified and valid
				if (iCNN > 0 && sSQL != "")
				{
					// log it first before executing query
					log_write(sinTrxID, "  " + sELM, sCNN + ":" + sSQL);

					// execute query, if failed then write log then exit application
					isTrue = false;
					n = db_query_n_evaluate(iCNN, sSQL, isTrue, sVar1, sVar2, sVar3);
					if (n < 0)
					{
						log_write(sinTrxID, "  error", "query failed");
						log_write(sinTrxID, "  solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]");
						log_write(sinTrxID, "finish", "error\n");
						goto posNextFile;
					}

					// evaluate result, is it true or false
					if (n == 0)
					{
						sEVL = "sql_if_empty";
						sSBQ = "on empty";
					}
					else
					{
						if (isTrue)
						{
							sEVL = "sql_if_exist_true";
							sSBQ = "on true";
						}
						else
						{
							sEVL = "sql_if_exist_false";
							sSBQ = "on false";
						}
					}

					// execute queries under evaluation scope
					i = 0;
					do {
						i = i + 1;
						sELM = sEVL + cast_int2str(i);
						sTemp = xml_get_value(sQUERIES, sELM);
						if (sTemp == "") { break; }

						// extract db connection number to execute query and the query itself
						sSQL = xml_get_value(sTemp, "sql");
						sCNN = xml_get_value(sTemp, "dbcn");
						iCNN = cast_str2int(sCNN);

						// only execute queries that having connection number and the query itself
						if (iCNN > 0 && sSQL != "")
						{
							// log it first before executing query
							sSQL = db_query_set_value(sSQL, sVar1, sVar2, sVar3);
							log_write(sinTrxID, "  " + sSBQ + cast_int2str(i), sCNN + ":" + sSQL);

							// execute query, if failed then write log then exit application
							if (db_query(iCNN, sSQL) != 0)
							{
								log_write(sinTrxID, "  error", "query failed");
								log_write(sinTrxID, "  solution", "move file into trash directory [" + file_trash(gsinidirsrc + cFilename, cFilename) + "]");
								log_write(sinTrxID, "finish", "error\n");
								goto posNextFile;
							}
						}
					} while (sSQL != "");
				}
			}

			// delete file -------------------------------------------------------------------------------------------------------
			if (file_delete(gsinidirsrc + cFilename) == 0)
			{
				log_write(sinTrxID, "  succeed", "file deleted");
				log_write(sinTrxID, "finish", "counted as success\n");
				cntCERR = 0;
				goto posNextFile;
			}
			else
			{
				log_write(sinTrxID, "  error", "failed to delete file");
				log_write(sinTrxID, "  solution", "terminate application, admin must delete this file manually");
				log_write(sinTrxID, "finish", "error\n");
				goto posExitApp;
			}

			// position to process next file
			posNextFile:;
		}
		closedir(aDir);

		// put cycle to a sleep for a moment if sought directory is empty
		posNextCycle:;
		usleep(giinisleep1);
	}

	// make sure everything closed
	posExitApp:;
	log_write("closing", "application", "closing application");
	gisMainON = false;
	socket_close();

	// close MySQL database connection
	mysql_close(gdb1CN);
	mysql_close(gdb2CN);
	return NULL;
}