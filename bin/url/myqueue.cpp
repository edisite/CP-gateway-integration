#include <string.h>
#include "myqueue.h"

myqueue::myqueue()
{
	pCount = 0;
	firstNode = NULL;
	lastNode = NULL;
}

myqueue::~myqueue()
{
	clear();
}

int myqueue::doput(string data01, string data02, string data03, string data04, string data05, string data06, string data07, string data08)
{
	pstcNode *newNode = new pstcNode;
	try
	{
		newNode->field01 = data01;
		newNode->field02 = data02;
		newNode->field03 = data03;
		newNode->field04 = data04;
		newNode->field05 = data05;
		newNode->field06 = data06;
		newNode->field07 = data07;
		newNode->field08 = data08;
		newNode->child = NULL;

		if (firstNode == NULL)
		{
			firstNode = newNode;
			lastNode = newNode;
		}
		else
		{
			lastNode->child = newNode;
			lastNode = newNode;
		}
		pCount = pCount + 1;
		return 0;
	}
	catch (int e) { return 1; }
}

int myqueue::doget(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07, string &data08)
{
	data01 = "";
	data02 = "";
	data03 = "";
	data04 = "";
	data05 = "";
	data06 = "";
	data07 = "";
	data08 = "";
	if (firstNode == NULL)
	{
		pCount = 0;
		return 1;
	}
	else
	{
		try
		{
			pstcNode *retNode;
			retNode = firstNode;

			if (firstNode->child == NULL)
			{
				firstNode = NULL;
				lastNode = NULL;
			}
			else
			{
				firstNode = firstNode->child;
			}
			pCount = pCount - 1;
			data01 = retNode->field01;
			data02 = retNode->field02;
			data03 = retNode->field03;
			data04 = retNode->field04;
			data05 = retNode->field05;
			data06 = retNode->field06;
			data07 = retNode->field07;
			data08 = retNode->field08;
			delete retNode;
			retNode = NULL;
			return 0;
		}
		catch (int e) { return 1; }
	}
}

int myqueue::count() const { return pCount; }

int myqueue::put(string data01) { return doput(data01, "", "", "", "", "", "", ""); }
int myqueue::get(string &data01) { string s2, s3, s4, s5, s6, s7, s8; return doget(data01, s2, s3, s4, s5, s6, s7, s8); }

int myqueue::put(string data01, string data02) { return doput(data01, data02, "", "", "", "", "", ""); }
int myqueue::get(string &data01, string &data02) { string s3, s4, s5, s6, s7, s8; return doget(data01, data02, s3, s4, s5, s6, s7, s8); }

int myqueue::put(string data01, string data02, string data03) { return doput(data01, data02, data03, "", "", "", "", ""); }
int myqueue::get(string &data01, string &data02, string &data03) { string s4, s5, s6, s7, s8; return doget(data01, data02, data03, s4, s5, s6, s7, s8); }

int myqueue::put(string data01, string data02, string data03, string data04) { return doput(data01, data02, data03, data04, "", "", "", ""); }
int myqueue::get(string &data01, string &data02, string &data03, string &data04) { string s5, s6, s7, s8; return doget(data01, data02, data03, data04, s5, s6, s7, s8); }

int myqueue::put(string data01, string data02, string data03, string data04, string data05) { return doput(data01, data02, data03, data04, data05, "", "", ""); }
int myqueue::get(string &data01, string &data02, string &data03, string &data04, string &data05) { string s6, s7, s8; return doget(data01, data02, data03, data04, data05, s6, s7, s8); }

int myqueue::put(string data01, string data02, string data03, string data04, string data05, string data06) { return doput(data01, data02, data03, data04, data05, data06, "", ""); }
int myqueue::get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06) { string s7, s8; return doget(data01, data02, data03, data04, data05, data06, s7, s8); }

int myqueue::put(string data01, string data02, string data03, string data04, string data05, string data06, string data07) { return doput(data01, data02, data03, data04, data05, data06, data07, ""); }
int myqueue::get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07) { string s8; return doget(data01, data02, data03, data04, data05, data06, data07, s8); }

int myqueue::put(string data01, string data02, string data03, string data04, string data05, string data06, string data07, string data08) { return doput(data01, data02, data03, data04, data05, data06, data07, data08); }
int myqueue::get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07, string &data08) { return doget(data01, data02, data03, data04, data05, data06, data07, data08); }

void myqueue::clear()
{
	pCount = 0;
	cleanup(firstNode);
	firstNode = NULL;
	lastNode = NULL;
}

void myqueue::cleanup(struct pstcNode *item)
{
	if (item == NULL) { return; }
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
	item = NULL;
}