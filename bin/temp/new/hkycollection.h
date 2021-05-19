#ifndef HKYCOLLECTION_H
#define HKYCOLLECTION_H
#include <string>
#include <algorithm>
using namespace std;
class hkycollection
{
	public:
		hkycollection();
		~hkycollection();

		string about();
		string version();
		string description();

		int count() const;
		bool first();
		bool next();

		bool read(string &psKey);
		bool read(string &psKey, string &psField01);
		bool read(string &psKey, string &psField01, string &psField02);
		bool read(string &psKey, string &psField01, string &psField02, string &psField03);

		bool put(string psKey);
		bool put(string psKey, string psField01);
		bool put(string psKey, string psField01, string psField02);
		bool put(string psKey, string psField01, string psField02, string psField03);

		bool item(string psKey);
		bool item(string psKey, string &psField01);
		bool item(string psKey, string &psField01, string &psField02);
		bool item(string psKey, string &psField01, string &psField02, string &psField03);

		bool clear();
	private:
};
#include "hkycollection.cpp"
#endif