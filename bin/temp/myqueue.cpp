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

int myqueue::count() const { return pCount; }

bool myqueue::put(string field01) { return put(field01, "", "", "", "", "", "", "", "", ""); }
bool myqueue::put(string field01, string field02) { return put(field01, field02, "", "", "", "", "", "", "", ""); }
bool myqueue::put(string field01, string field02, string field03) { return put(field01, field02, field03, "", "", "", "", "", "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04) { return put(field01, field02, field03, field04, "", "", "", "", "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05) { return put(field01, field02, field03, field04, field05, "", "", "", "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05, string field06) { return put(field01, field02, field03, field04, field05, field06, "", "", "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05, string field06, string field07) { return put(field01, field02, field03, field04, field05, field06, field07, "", "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08) { return put(field01, field02, field03, field04, field05, field06, field07, field08, "", ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08, string field09) { return put(field01, field02, field03, field04, field05, field06, field07, field08, field09, ""); }
bool myqueue::put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08, string field09, string field10)
{
	pstcNode *newNode = new pstcNode;
	try
	{
		newNode->field01 = field01;
		newNode->field02 = field02;
		newNode->field03 = field03;
		newNode->field04 = field04;
		newNode->field05 = field05;
		newNode->field06 = field06;
		newNode->field07 = field07;
		newNode->field08 = field08;
		newNode->field09 = field09;
		newNode->field10 = field10;
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
		return true;
	}
	catch (int e) { return false; }
}

bool myqueue::get(string &field01) { string s2, s3, s4, s5, s6, s7, s8, s9, s10; return get(field01, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02) { string s3, s4, s5, s6, s7, s8, s9, s10; return get(field01, field02, s3, s4, s5, s6, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03) { string s4, s5, s6, s7, s8, s9, s10; return get(field01, field02, field03, s4, s5, s6, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04) { string s5, s6, s7, s8, s9, s10; return get(field01, field02, field03, field04, s5, s6, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05) { string s6, s7, s8, s9, s10; return get(field01, field02, field03, field04, field05, s6, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06) { string s7, s8, s9, s10; return get(field01, field02, field03, field04, field05, field06, s7, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07) { string s8, s9, s10; return get(field01, field02, field03, field04, field05, field06, field07, s8, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08) { string s9, s10; return get(field01, field02, field03, field04, field05, field06, field07, field08, s9, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08, string &field09) { string s10; return get(field01, field02, field03, field04, field05, field06, field07, field08, field09, s10); }
bool myqueue::get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08, string &field09, string &field10)
{
	field01 = "";
	field02 = "";
	field03 = "";
	field04 = "";
	field05 = "";
	field06 = "";
	field07 = "";
	field08 = "";
	field09 = "";
	field10 = "";
	if (firstNode == NULL)
	{
		pCount = 0;
		return false;
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
			field01 = retNode->field01;
			field02 = retNode->field02;
			field03 = retNode->field03;
			field04 = retNode->field04;
			field05 = retNode->field05;
			field06 = retNode->field06;
			field07 = retNode->field07;
			field08 = retNode->field08;
			field09 = retNode->field09;
			field10 = retNode->field10;
			delete(retNode);
			retNode = NULL;
			return true;
		}
		catch (int e) { return false; }
	}
}

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