#ifndef MYQUEUE_H
#define MYQUEUE_H
using namespace std;

class myqueue
{
	public:
		myqueue();
		~myqueue();

		int count() const;

		int put(string data01);
		int get(string &data01);

		int put(string data01, string data02);
		int get(string &data01, string &data02);

		int put(string data01, string data02, string data03);
		int get(string &data01, string &data02, string &data03);

		int put(string data01, string data02, string data03, string data04);
		int get(string &data01, string &data02, string &data03, string &data04);

		int put(string data01, string data02, string data03, string data04, string data05);
		int get(string &data01, string &data02, string &data03, string &data04, string &data05);

		int put(string data01, string data02, string data03, string data04, string data05, string data06);
		int get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06);

		int put(string data01, string data02, string data03, string data04, string data05, string data06, string data07);
		int get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07);

		int put(string data01, string data02, string data03, string data04, string data05, string data06, string data07, string data08);
		int get(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07, string &data08);

		void clear();

	private:
		struct pstcNode
		{
			string		field01;
			string		field02;
			string		field03;
			string		field04;
			string		field05;
			string		field06;
			string		field07;
			string		field08;
			pstcNode	*child;
		};
		
		int pCount;
		pstcNode *firstNode;
		pstcNode *lastNode;

		void cleanup(struct pstcNode *item);
		int doput(string data01, string data02, string data03, string data04, string data05, string data06, string data07, string data08);
		int doget(string &data01, string &data02, string &data03, string &data04, string &data05, string &data06, string &data07, string &data08);
};

#include "myqueue.cpp"
#endif