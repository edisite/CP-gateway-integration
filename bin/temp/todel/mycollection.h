#ifndef mycollection_H
#define mycollection_H
using namespace std;
class mycollection
{
	public:
		mycollection();
		~mycollection();
		int first();
		int next();
		int count() const;
		int put(string psKey, string psData1, string psData2, string psData3);
		int read(string &psKey, string &psData1, string &psData2, string &psData3);
		int item(string psKey, string &psData1);
		int item(string psKey, string &psData1, string &psData2);
		int item(string psKey, string &psData1, string &psData2, string &psData3);
		int _item(string psKey, string &psData1, string &psData2, string &psData3);

	private:
};

#include "mycollection.cpp"
#endif