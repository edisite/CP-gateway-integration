#ifndef MYURL_H
#define MYURL_H
#include <curl/curl.h>		/* required for opening url */
#include <curl/types.h>		/* required for opening url */
#include <curl/easy.h>		/* required for opening url */

using namespace std;
class myurl
{
	public:
		myurl();
		~myurl();
		bool send(string psMethod, string psURL, string psData, int piTimeout, string &psResponse);
		bool send(string psMethod, string psURL, string psData, int piTimeout, string &psResponse, double &pdElapsed);

	private:
		string	gsurl_res_buffer;					/* store url response here */
		char	gcurl_res_error[CURL_ERROR_SIZE];	/* store url error here */
};

#include "myurl.cpp"
#endif
