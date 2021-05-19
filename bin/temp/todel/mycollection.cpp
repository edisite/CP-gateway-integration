#include <stdlib.h>
#include <iostream>
#include <algorithm>
#include "mycollection.h"
using namespace std;

struct pstcNode
{
	string		dtKey;
	string		dtData1;
	string		dtData2;
	string		dtData3;
	pstcNode	*child;
};

int pCount;
pstcNode *firstNode;
pstcNode *lastNode;
pstcNode *currentNode;
pstcNode *tempNode;

mycollection::mycollection()
{
	pCount = 0;
	firstNode = NULL;
	lastNode = NULL;
	currentNode = NULL;
}

void cleanup(pstcNode *item)
{
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
}

mycollection::~mycollection()
{
	pCount = 0;
	cleanup(firstNode);
}

int mycollection::count() const { return pCount; }

int mycollection::first()
{
	if (firstNode == NULL)
	{
		return 1;
	}
	else
	{
		currentNode = firstNode;
		return 0;
	}
}

int mycollection::next()
{
	if (currentNode == NULL)
	{
		return 1;
	}
	else
	{
		if (currentNode->child == NULL)
		{
			currentNode = NULL;
			return 1;
		}
		else
		{
			currentNode = currentNode->child;
			return 0;
		}
	}
}

int mycollection::put(string psKey, string psData1, string psData2, string psData3)
{
	pstcNode *newNode = new pstcNode;
	try
	{
		transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);

		newNode->dtKey = psKey;
		newNode->dtData1 = psData1;
		newNode->dtData2 = psData2;
		newNode->dtData3 = psData3;
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
		return 0;
	}
	catch (int e) { return 1; }
}

int mycollection::read(string &psKey, string &psData1, string &psData2, string &psData3)
{
	psKey = "";
	psData1 = "";
	psData2 = "";
	psData3 = "";
	if (currentNode == NULL)
	{
		return 1;
	}
	else
	{
		psKey = currentNode->dtKey;
		psData1 = currentNode->dtData1;
		psData2 = currentNode->dtData2;
		psData3 = currentNode->dtData3;
		return 0;
	}
}

int mycollection::item(string psKey, string &psData1) { string s2, s3; return _item(psKey, psData1, s2, s3); }
int mycollection::item(string psKey, string &psData1, string &psData2) { string s3; return _item(psKey, psData1, psData2, s3); }
int mycollection::item(string psKey, string &psData1, string &psData2, string &psData3) { return _item(psKey, psData1, psData2, psData3); }
int mycollection::_item(string psKey, string &psData1, string &psData2, string &psData3)
{
	int res = 1;
	psData1 = "";
	psData2 = "";
	psData3 = "";
	if (firstNode == NULL) { return res; }
	transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);

	tempNode = firstNode;
	while (tempNode != NULL)
	{
		if (tempNode->dtKey == psKey)
		{
			psData1 = tempNode->dtData1;
			psData2 = tempNode->dtData2;
			psData3 = tempNode->dtData3;
			res = 0;
			break;
		}
		tempNode = tempNode->child;
	}
	return res;
}