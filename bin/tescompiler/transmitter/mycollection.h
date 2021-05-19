#ifndef mycollection_H
#define mycollection_H
using namespace std;
class mycollection
{
	public:
		mycollection();
		~mycollection();
		int put(string strOwner, string strKeyword, string strURL);
		int first();
		int next();
		int read(string &strOwner, string &strKeyword, string &strURL);
		int count() const;
		string getOwner(string strKeyword);
		string getURL(string strKeyword);

	private:
};

#include "mycollection.cpp"
#endif