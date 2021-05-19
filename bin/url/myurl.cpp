#include <stdlib.h>
#include <iostream>
#include "myurl.h"
using namespace std;

myurl::myurl()
{
	gsurl_res_buffer = "";
	memset(&gcurl_res_error[0], 0, sizeof(gcurl_res_error));
}

myurl::~myurl()
{
	gsurl_res_buffer = "";
	memset(&gcurl_res_error[0], 0, sizeof(gcurl_res_error));
}

static int __url_writer(char *cData, size_t tSize, size_t tNMem, string *gsurl_res_buffer)
{
	int r = 0;
	if (gsurl_res_buffer != NULL)
	{
		gsurl_res_buffer->append(cData, tSize * tNMem);
		r = tSize * tNMem;
	}
	return r;
}

bool myurl::send_get(string strURL, int intSecTimeout, string &strResponse) { double d; return send_get(strURL, intSecTimeout, strResponse, d); }
bool myurl::send_get(string strURL, int intSecTimeout, string &strResponse, double &dblElapsed)
{
	CURL *tcurl;
	CURLcode tResult;
	dblElapsed = 999;
	gsurl_res_buffer = "";
	tcurl = curl_easy_init();
	if (!tcurl) { return false; }

	curl_easy_setopt(tcurl, CURLOPT_ERRORBUFFER, gcurl_res_error);
	curl_easy_setopt(tcurl, CURLOPT_URL, strURL.c_str());
	curl_easy_setopt(tcurl, CURLOPT_HEADER, 0);
	curl_easy_setopt(tcurl, CURLOPT_FOLLOWLOCATION, 1);
	curl_easy_setopt(tcurl, CURLOPT_WRITEFUNCTION, __url_writer);
	curl_easy_setopt(tcurl, CURLOPT_WRITEDATA, &gsurl_res_buffer);
	curl_easy_setopt(tcurl, CURLOPT_CONNECTTIMEOUT, intSecTimeout);
	curl_easy_setopt(tcurl, CURLOPT_TIMEOUT, intSecTimeout);
	tResult = curl_easy_perform(tcurl);
	curl_easy_cleanup(tcurl);
	if (tResult != CURLE_OK) { return false; }

	curl_easy_getinfo(tcurl, CURLINFO_TOTAL_TIME, &dblElapsed);
	strResponse = gsurl_res_buffer;
	return true;
}

bool myurl::send_post(string strURL, string strData, int intSecTimeout, string &strResponse) { double d; return send_post(strURL, strData, intSecTimeout, strResponse, d); }
bool myurl::send_post(string strURL, string strData, int intSecTimeout, string &strResponse, double &dblElapsed)
{
	CURL *tcurl;
	CURLcode tResult;
	dblElapsed = 999;
	gsurl_res_buffer = "";
	tcurl = curl_easy_init();
	if (!tcurl) { return false; }

	curl_easy_setopt(tcurl, CURLOPT_ERRORBUFFER, gcurl_res_error);
	curl_easy_setopt(tcurl, CURLOPT_URL, strURL.c_str());
	curl_easy_setopt(tcurl, CURLOPT_POST, true);
    curl_easy_setopt(tcurl, CURLOPT_POSTFIELDS, strData.c_str());
	curl_easy_setopt(tcurl, CURLOPT_HEADER, 0);
	curl_easy_setopt(tcurl, CURLOPT_FOLLOWLOCATION, 1);
	curl_easy_setopt(tcurl, CURLOPT_WRITEFUNCTION, __url_writer);
	curl_easy_setopt(tcurl, CURLOPT_WRITEDATA, &gsurl_res_buffer);
	curl_easy_setopt(tcurl, CURLOPT_CONNECTTIMEOUT, intSecTimeout);
	curl_easy_setopt(tcurl, CURLOPT_TIMEOUT, intSecTimeout);
	tResult = curl_easy_perform(tcurl);
	curl_easy_cleanup(tcurl);
	if (tResult != CURLE_OK) { return false; }

	curl_easy_getinfo(tcurl, CURLINFO_TOTAL_TIME, &dblElapsed);
	strResponse = gsurl_res_buffer;
	return true;
}