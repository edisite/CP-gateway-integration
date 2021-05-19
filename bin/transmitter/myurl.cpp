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

static int url_writer(char *cData, size_t tSize, size_t tNMem, string *gsurl_res_buffer)
{
	int r = 0;
	if (gsurl_res_buffer != NULL)
	{
		gsurl_res_buffer->append(cData, tSize * tNMem);
		r = tSize * tNMem;
	}
	return r;
}

int myurl::open(string strURL, string &strResponse)
{
	CURL *tcurl;
	CURLcode tResult;
	gsurl_res_buffer = "";
	tcurl = curl_easy_init();
	if (!tcurl) { return -1; }

	curl_easy_setopt(tcurl, CURLOPT_ERRORBUFFER, gcurl_res_error);
	curl_easy_setopt(tcurl, CURLOPT_URL, strURL.c_str());
	curl_easy_setopt(tcurl, CURLOPT_HEADER, 0);
	curl_easy_setopt(tcurl, CURLOPT_FOLLOWLOCATION, 1);
	curl_easy_setopt(tcurl, CURLOPT_WRITEFUNCTION, url_writer);
	curl_easy_setopt(tcurl, CURLOPT_WRITEDATA, &gsurl_res_buffer);
	tResult = curl_easy_perform(tcurl);
	curl_easy_cleanup(tcurl);
	if (tResult != CURLE_OK) { return -1; }

	strResponse = gsurl_res_buffer;
	return 0;
}

int myurl::post(string strURL, string strData, string &strResponse)
{
	CURL *tcurl;
	CURLcode tResult;
	gsurl_res_buffer = "";
	tcurl = curl_easy_init();
	if (!tcurl) { return -1; }

	curl_easy_setopt(tcurl, CURLOPT_ERRORBUFFER, gcurl_res_error);
	curl_easy_setopt(tcurl, CURLOPT_URL, strURL.c_str());
	curl_easy_setopt(tcurl, CURLOPT_POST, true);
    	curl_easy_setopt(tcurl, CURLOPT_POSTFIELDS, strData.c_str());
	curl_easy_setopt(tcurl, CURLOPT_HEADER, 0);
	curl_easy_setopt(tcurl, CURLOPT_USERAGENT, "HTTP CP Client");
	curl_easy_setopt(tcurl, CURLOPT_FOLLOWLOCATION, 1);
	curl_easy_setopt(tcurl, CURLOPT_WRITEFUNCTION, url_writer);
	curl_easy_setopt(tcurl, CURLOPT_WRITEDATA, &gsurl_res_buffer);
	tResult = curl_easy_perform(tcurl);
	curl_easy_cleanup(tcurl);
	if (tResult != CURLE_OK) { return -1; }

	strResponse = gsurl_res_buffer;
	return 0;
}