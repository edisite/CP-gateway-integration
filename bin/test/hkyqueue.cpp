#include "hkyqueue.h"

struct _queue_stcNode
{
	string		field01;
	string		field02;
	string		field03;
	string		field04;
	string		field05;
	string		field06;
	string		field07;
	string		field08;
	string		field09;
	string		field10;
	_queue_stcNode	*child;
};

int _queue_intCount;
_queue_stcNode *_queue_firstNode;
_queue_stcNode *_queue_lastNode;

hkyqueue::hkyqueue()
{
	_queue_intCount = 0;
}

void cleanup(_queue_stcNode *item)
{
	if (item == NULL) { return; }
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
}

hkyqueue::~hkyqueue()
{
	clear();
}

string hkyqueue::version()
{
	string s = "hkyQueue v1.0";
	return s;
}

string hkyqueue::about()
{
	string s = "hkyQueue v1.0\n\tcreated and designed by hengky irawan\n\temail: hakaye_azure@yahoo.com";
	return s;
}

string hkyqueue::description()
{
	string s = "";
	s = "hkyQueue v1.0\n";
	s = s + "a. information\n\t1. version() as string\n\t2. about() as string\n\t3. description() as string\n\n";
	s = s + "b. methods\n";
	s = s + "\t1. count() as integer\n";
	s = s + "\t2. put(string1, [string2], [string3], ..., [string10]) as boolean\n";
	s = s + "\t   - put item into queue (at the end of queue), which each item may have up to 10 fields\n";
	s = s + "\t   - return true if success, false if there is an error\n";
	s = s + "\t3. get(&string1, [&string2], [&string3], ..., [&string10]) as boolean\n";
	s = s + "\t   - get item from queue (from the front of queue), which each item may have up to 10 fields\n";
	s = s + "\t   - return true if success, false if there is an error\n";
	s = s + "\t4. clear() as boolean\n";
	s = s + "\t   - return true if clearing all items in queue is succees, return false if there is an error or items cannot be cleared\n\n";
	s = s + "c. what's new\n";
	s = s + "\t12 October 2012\n";
	s = s + "\t - initial release version\n";
	s = s + "\t - first in first out using linked list algorithm\n";
	s = s + "\t - tested and checked for memory leak\n";
	return s;
}

bool doput(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08, string psField09, string psField10)
{
	_queue_stcNode *newNode = new _queue_stcNode;
	try
	{
		newNode->field01 = psField01;
		newNode->field02 = psField02;
		newNode->field03 = psField03;
		newNode->field04 = psField04;
		newNode->field05 = psField05;
		newNode->field06 = psField06;
		newNode->field07 = psField07;
		newNode->field08 = psField08;
		newNode->field09 = psField09;
		newNode->field10 = psField10;
		newNode->child = NULL;

		if (_queue_firstNode == NULL)
		{
			_queue_firstNode = newNode;
			_queue_lastNode = newNode;
		}
		else
		{
			_queue_lastNode->child = newNode;
			_queue_lastNode = newNode;
		}
		_queue_intCount = _queue_intCount + 1;
		return true;
	}
	catch (int e) { return false; }
}

bool doget(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08, string &psField09, string &psField10)
{
	psField01 = "";
	psField02 = "";
	psField03 = "";
	psField04 = "";
	psField05 = "";
	psField06 = "";
	psField07 = "";
	psField08 = "";
	psField09 = "";
	psField10 = "";
	if (_queue_firstNode == NULL)
	{
		_queue_intCount = 0;
		return false;
	}
	else
	{
		try
		{
			_queue_stcNode *retNode;
			retNode = _queue_firstNode;

			if (_queue_firstNode->child == NULL)
			{
				_queue_firstNode = NULL;
				_queue_lastNode = NULL;
			}
			else
			{
				_queue_firstNode = _queue_firstNode->child;
			}
			_queue_intCount = _queue_intCount - 1;
			psField01 = retNode->field01;
			psField02 = retNode->field02;
			psField03 = retNode->field03;
			psField04 = retNode->field04;
			psField05 = retNode->field05;
			psField06 = retNode->field06;
			psField07 = retNode->field07;
			psField08 = retNode->field08;
			psField09 = retNode->field09;
			psField10 = retNode->field10;
			delete(retNode);
			return true;
		}
		catch (int e) { return false; }
	}
}

int hkyqueue::count() const { return _queue_intCount; }

bool hkyqueue::put(string psField01) { return doput(psField01, "", "", "", "", "", "", "", "", ""); }
bool hkyqueue::get(string &psField01) { string s2, s3, s4, s5, s6, s7, s8, s9, s10; return doget(psField01, s2, s3, s4, s5, s6, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02) { return doput(psField01, psField02, "", "", "", "", "", "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02) { string s3, s4, s5, s6, s7, s8, s9, s10; return doget(psField01, psField02, s3, s4, s5, s6, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03) { return doput(psField01, psField02, psField03, "", "", "", "", "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03) { string s4, s5, s6, s7, s8, s9, s10; return doget(psField01, psField02, psField03, s4, s5, s6, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04) { return doput(psField01, psField02, psField03, psField04, "", "", "", "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04) { string s5, s6, s7, s8, s9, s10; return doget(psField01, psField02, psField03, psField04, s5, s6, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05) { return doput(psField01, psField02, psField03, psField04, psField05, "", "", "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05) { string s6, s7, s8, s9, s10; return doget(psField01, psField02, psField03, psField04, psField05, s6, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06) { return doput(psField01, psField02, psField03, psField04, psField05, psField06, "", "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06) { string s7, s8, s9, s10; return doget(psField01, psField02, psField03, psField04, psField05, psField06, s7, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07) { return doput(psField01, psField02, psField03, psField04, psField05, psField06, psField07, "", "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07) { string s8, s9, s10; return doget(psField01, psField02, psField03, psField04, psField05, psField06, psField07, s8, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08) { return doput(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, "", ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08) { string s9, s10; return doget(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, s9, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08, string psField09) { return doput(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, psField09, ""); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08, string &psField09) { string s10; return doget(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, psField09, s10); }

bool hkyqueue::put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08, string psField09, string psField10) { return doput(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, psField09, psField10); }
bool hkyqueue::get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08, string &psField09, string &psField10) { return doget(psField01, psField02, psField03, psField04, psField05, psField06, psField07, psField08, psField09, psField10); }

bool hkyqueue::clear()
{
	_queue_intCount = 0;
	cleanup(_queue_firstNode);
	_queue_firstNode = NULL;
	_queue_lastNode = NULL;
	return true;
}