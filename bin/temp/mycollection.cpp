#include <string.h>
#include "mycollection.h"
using namespace std;

mycollection::mycollection()
{
	pCount = 0;
	firstNode = NULL;
	lastNode = NULL;
	currentNode = NULL;
}

mycollection::~mycollection()
{
	clear();
}

int mycollection::count() const { return pCount; }

bool mycollection::first()
{
	if (firstNode == NULL)
	{
		return false;
	}
	else
	{
		currentNode = firstNode;
		return true;
	}
}

bool mycollection::next()
{
	if (currentNode == NULL)
	{
		return false;
	}
	else
	{
		if (currentNode->child == NULL)
		{
			currentNode = NULL;
			return false;
		}
		else
		{
			currentNode = currentNode->child;
			return true;
		}
	}
}

bool mycollection::put(string psKey) { string s1, s2, s3, s4, s5, s6, s7, s8, s9, s10; return put(psKey, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1) { string s2, s3, s4, s5, s6, s7, s8, s9, s10; return put(psKey, psData1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2) { string s3, s4, s5, s6, s7, s8, s9, s10; return put(psKey, psData1, psData2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3) { string s4, s5, s6, s7, s8, s9, s10; return put(psKey, psData1, psData2, psData3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4) { string s5, s6, s7, s8, s9, s10; return put(psKey, psData1, psData2, psData3, psData4, s5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5) { string s6, s7, s8, s9, s10; return put(psKey, psData1, psData2, psData3, psData4, psData5, s6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6) { string s7, s8, s9, s10; return put(psKey, psData1, psData2, psData3, psData4, psData5, psData6, s7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7) { string s8, s9, s10; return put(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, s8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8) { string s9, s10; return put(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, s9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8, string psData9) { string s10; return put(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, psData9, s10); }
bool mycollection::put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8, string psData9, string psData10)
{
	pstcNode *newNode = new pstcNode;
	try
	{
		transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);
		newNode->dtKey = psKey;
		newNode->dtData1 = psData1;
		newNode->dtData2 = psData2;
		newNode->dtData3 = psData3;
		newNode->dtData4 = psData4;
		newNode->dtData5 = psData5;
		newNode->dtData6 = psData6;
		newNode->dtData7 = psData7;
		newNode->dtData8 = psData8;
		newNode->dtData9 = psData9;
		newNode->dtData10 = psData10;
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
		currentNode = newNode;
		pCount = pCount + 1;
		return true;
	}
	catch (bool e) { return false; }
}


bool mycollection::read(string &psKey) { string s1, s2, s3, s4, s5, s6, s7, s8, s9, s10; return read(psKey, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1) { string s2, s3, s4, s5, s6, s7, s8, s9, s10; return read(psKey, psData1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2) { string s3, s4, s5, s6, s7, s8, s9, s10; return read(psKey, psData1, psData2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3) { string s4, s5, s6, s7, s8, s9, s10; return read(psKey, psData1, psData2, psData3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4) { string s5, s6, s7, s8, s9, s10; return read(psKey, psData1, psData2, psData3, psData4, s5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5) { string s6, s7, s8, s9, s10; return read(psKey, psData1, psData2, psData3, psData4, psData5, s6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6) { string s7, s8, s9, s10; return read(psKey, psData1, psData2, psData3, psData4, psData5, psData6, s7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7) { string s8, s9, s10; return read(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, s8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8) { string s9, s10; return read(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, s9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9) { string s10; return read(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, psData9, s10); }
bool mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9, string &psData10)
{
	psKey = "";
	psData1 = "";
	psData2 = "";
	psData3 = "";
	psData4 = "";
	psData5 = "";
	psData6 = "";
	psData7 = "";
	psData8 = "";
	psData9 = "";
	psData10 = "";
	if (currentNode == NULL)
	{
		return false;
	}
	else
	{
		psKey = currentNode->dtKey;
		psData1 = currentNode->dtData1;
		psData2 = currentNode->dtData2;
		psData3 = currentNode->dtData3;
		psData4 = currentNode->dtData4;
		psData5 = currentNode->dtData5;
		psData6 = currentNode->dtData6;
		psData7 = currentNode->dtData7;
		psData8 = currentNode->dtData8;
		psData9 = currentNode->dtData9;
		psData10 = currentNode->dtData10;
		return true;
	}
}

bool mycollection::item(string psKey) { string s1, s2, s3, s4, s5, s6, s7, s8, s9, s10; return item(psKey, s1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1) { string s2, s3, s4, s5, s6, s7, s8, s9, s10; return item(psKey, psData1, s2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2) { string s3, s4, s5, s6, s7, s8, s9, s10; return item(psKey, psData1, psData2, s3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3) { string s4, s5, s6, s7, s8, s9, s10; return item(psKey, psData1, psData2, psData3, s4, s5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4) { string s5, s6, s7, s8, s9, s10; return item(psKey, psData1, psData2, psData3, psData4, s5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5) { string s6, s7, s8, s9, s10; return item(psKey, psData1, psData2, psData3, psData4, psData5, s6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6) { string s7, s8, s9, s10; return item(psKey, psData1, psData2, psData3, psData4, psData5, psData6, s7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7) { string s8, s9, s10; return item(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, s8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8) { string s9, s10; return item(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, s9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9) { string s10; return item(psKey, psData1, psData2, psData3, psData4, psData5, psData6, psData7, psData8, psData9, s10); }
bool mycollection::item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9, string &psData10)
{
	psData1 = "";	// this will be the return value if sought item not found
	psData2 = "";	// this will be the return value if sought item not found
	psData3 = "";	// this will be the return value if sought item not found
	psData4 = "";	// this will be the return value if sought item not found
	psData5 = "";	// this will be the return value if sought item not found
	psData6 = "";	// this will be the return value if sought item not found
	psData7 = "";	// this will be the return value if sought item not found
	psData8 = "";	// this will be the return value if sought item not found
	psData9 = "";	// this will be the return value if sought item not found
	psData10 = "";	// this will be the return value if sought item not found

	if (firstNode == NULL) { return false; } // not found
	transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);

	tmpNode = firstNode;
	while (tmpNode != NULL)
	{
		cout << "searching: " << tmpNode->dtKey << endl;
		if (tmpNode->dtKey == psKey)
		{
			cout << "found" << endl;
			psData1 = tmpNode->dtData1;
			psData2 = tmpNode->dtData2;
			psData3 = tmpNode->dtData3;
			psData4 = tmpNode->dtData4;
			psData5 = tmpNode->dtData5;
			psData6 = tmpNode->dtData6;
			psData7 = tmpNode->dtData7;
			psData8 = tmpNode->dtData8;
			psData9 = tmpNode->dtData9;
			psData10 = tmpNode->dtData10;
			return true; // found
		}
		tmpNode = tmpNode->child;
	}
	return false; // not found
}

void mycollection::clear()
{
	pCount = 0;
	cleanup(firstNode);
	firstNode = NULL;
	lastNode = NULL;
	currentNode = NULL;
}

void mycollection::cleanup(struct pstcNode *item)
{
	if (item == NULL) { return; }
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
}

