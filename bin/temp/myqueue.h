#ifndef MYQUEUE_H
#define MYQUEUE_H
using namespace std;

class myqueue
{
	public:
		myqueue();
		~myqueue();

		int count() const;

		bool put(string field01);
		bool put(string field01, string field02);
		bool put(string field01, string field02, string field03);
		bool put(string field01, string field02, string field03, string field04);
		bool put(string field01, string field02, string field03, string field04, string field05);
		bool put(string field01, string field02, string field03, string field04, string field05, string field06);
		bool put(string field01, string field02, string field03, string field04, string field05, string field06, string field07);
		bool put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08);
		bool put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08, string field09);
		bool put(string field01, string field02, string field03, string field04, string field05, string field06, string field07, string field08, string field09, string field10);

		bool get(string &field01);
		bool get(string &field01, string &field02);
		bool get(string &field01, string &field02, string &field03);
		bool get(string &field01, string &field02, string &field03, string &field04);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08, string &field09);
		bool get(string &field01, string &field02, string &field03, string &field04, string &field05, string &field06, string &field07, string &field08, string &field09, string &field10);

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
			string		field09;
			string		field10;
			pstcNode	*child;
		};

		int pCount;
		pstcNode *firstNode;
		pstcNode *lastNode;

		void cleanup(struct pstcNode *item);
};

#include "myqueue.cpp"
#endif