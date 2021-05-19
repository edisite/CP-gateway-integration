#ifndef HKYQUEUE_H
#define HKYQUEUE_H
#include <string>
using namespace std;
class hkyqueue
{
	public:
		hkyqueue();
		~hkyqueue();

		string about();
		string version();
		string description();

		int count() const;

		bool put(string psField01);
		bool get(string &psField01);

		bool put(string psField01, string psField02);
		bool get(string &psField01, string &psField02);

		bool put(string psField01, string psField02, string psField03);
		bool get(string &psField01, string &psField02, string &psField03);

		bool put(string psField01, string psField02, string psField03, string psField04);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08, string psField09);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08, string &psField09);

		bool put(string psField01, string psField02, string psField03, string psField04, string psField05, string psField06, string psField07, string psField08, string psField09, string psField10);
		bool get(string &psField01, string &psField02, string &psField03, string &psField04, string &psField05, string &psField06, string &psField07, string &psField08, string &psField09, string &psField10);

		bool clear();
	private:
};
#include "hkyqueue.cpp"
#endif