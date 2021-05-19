#include <stdlib.h>
#include <iostream>
#include "mycollection.h"
using namespace std;

struct pstcNode
{
	string		dtOwner;
	string		dtKeyword;
	string		dtURL;
	pstcNode	*child;
};

int pCount;
pstcNode *firstNode;
pstcNode *lastNode;
pstcNode *currentNode;
pstcNode *tmpNode;

mycollection::mycollection()
{
	pCount = 0;
	firstNode = NULL;
	lastNode = NULL;
	currentNode = NULL;
}

void cleanup(struct pstcNode *item)
{
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
}

mycollection::~mycollection()
{
	pCount = 0;
	cleanup(firstNode);
}

int mycollection::put(string strOwner, string strKeyword, string strURL)
{
	pstcNode *newNode = new pstcNode;
	try
	{
		transform(strKeyword.begin(), strKeyword.end(), strKeyword.begin(), ::toupper);

		newNode->dtOwner = strOwner;
		newNode->dtKeyword = strKeyword;
		newNode->dtURL = strURL;
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

int mycollection::read(string &strOwner, string &strKeyword, string &strURL)
{
	strOwner = "";
	strKeyword = "";
	strURL = "";
	if (currentNode == NULL)
	{
		return 1;
	}
	else
	{
		strOwner = currentNode->dtOwner;
		strKeyword = currentNode->dtKeyword;
		strURL = currentNode->dtURL;
		return 0;
	}
}

int mycollection::count() const { return pCount; }

string mycollection::getOwner(string strKeyword)
{
	if (firstNode == NULL) { return ""; }
	transform(strKeyword.begin(), strKeyword.end(), strKeyword.begin(), ::toupper);

	string sRes = "";
	tmpNode = firstNode;
	while (tmpNode != NULL)
	{
		if (tmpNode->dtKeyword == strKeyword) { sRes = tmpNode->dtOwner; break; }
		tmpNode = tmpNode->child;
	}
	tmpNode = NULL;
	return sRes;
}

string mycollection::getURL(string strKeyword)
{
	if (firstNode == NULL) { return ""; }
	transform(strKeyword.begin(), strKeyword.end(), strKeyword.begin(), ::toupper);

	string sRes = "";
	tmpNode = firstNode;
	while (tmpNode != NULL)
	{
		if (tmpNode->dtKeyword == strKeyword) { sRes = tmpNode->dtURL; break; }
		tmpNode = tmpNode->child;
	}
	tmpNode = NULL;
	return sRes;
}