#ifndef MYCOLLECTION_H
#define MYCOLLECTION_H
#include <iostream>
using namespace std;

class mycollection
{
	public:
		mycollection();
		~mycollection();
		int count() const;
		bool first();
		bool next();

		bool put(string psKey);
		bool put(string psKey, string psData1);
		bool put(string psKey, string psData1, string psData2);
		bool put(string psKey, string psData1, string psData2, string psData3);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8, string psData9);
		bool put(string psKey, string psData1, string psData2, string psData3, string psData4, string psData5, string psData6, string psData7, string psData8, string psData9, string psData10);

		bool read(string &psKey);
		bool read(string &psKey, string &psData1);
		bool read(string &psKey, string &psData1, string &psData2);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9);
		bool read(string &psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9, string &psData10);

		bool item(string psKey);
		bool item(string psKey, string &psData1);
		bool item(string psKey, string &psData1, string &psData2);
		bool item(string psKey, string &psData1, string &psData2, string &psData3);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9);
		bool item(string psKey, string &psData1, string &psData2, string &psData3, string &psData4, string &psData5, string &psData6, string &psData7, string &psData8, string &psData9, string &psData10);
		void clear();

	private:
		struct pstcNode
		{
			string		dtKey;
			string		dtData1;
			string		dtData2;
			string		dtData3;
			string		dtData4;
			string		dtData5;
			string		dtData6;
			string		dtData7;
			string		dtData8;
			string		dtData9;
			string		dtData10;
			pstcNode	*child;
		};

		bool pCount;
		pstcNode *firstNode;
		pstcNode *lastNode;
		pstcNode *currentNode;
		pstcNode *tmpNode;

		void cleanup(struct pstcNode *item);
};

#include "mycollection.cpp"
#endif