struct _col_stcNode
{
	string		key;
	string		field01;
	string		field02;
	string		field03;
	_col_stcNode	*child;
};

int _col_intCount;
_col_stcNode *_col_nodFirst;
_col_stcNode *_col_nodLast;
_col_stcNode *_col_nodCurrent;

hkycollection::hkycollection()
{
	_col_intCount = 0;
	_col_nodFirst = NULL;
	_col_nodLast = NULL;
	_col_nodCurrent = NULL;
}

void cleanup(_col_stcNode *item)
{
	if (item == NULL) { return; }
	if (item->child != NULL) { cleanup(item->child); }
	delete(item);
}

hkycollection::~hkycollection()
{
	clear();
}

string hkycollection::version()
{
	string s = "hkyCollection v1.0";
	return s;
}

string hkycollection::about()
{
	string s = "hkyCollection v1.0\n\tcreated and designed by hengky irawan\n\temail: hakaye_azure@yahoo.com";
	return s;
}

string hkycollection::description()
{
	string s = "";
	s = "hkyCollection v1.0\n";
	s = s + "a. information\n\t1. version() as string\n\t2. about() as string\n\t3. description() as string\n\n";
	s = s + "b. methods\n";
	s = s + "\t1. count() as integer\n";
	s = s + "\t   - return the number of unique item in collection\n";
	s = s + "\t2. first() as boolean\n";
	s = s + "\t   - put read position to the first item in collection\n";
	s = s + "\t   - return true if success, false if there is an error or the collection is empty\n";
	s = s + "\t3. next() as boolean\n";
	s = s + "\t   - put read position to the next item in collection\n";
	s = s + "\t   - return true if success, false if there is an error, the collection is empty or reaching beyond the last item\n";
	s = s + "\t4. read(string &key, &string1, [&string2], [&string3]) as boolean\n";
	s = s + "\t   - read current item and return all of it's fields including the item's key\n";
	s = s + "\t   - return true if success, false if there is an error, the collection is empty or reaching beyond the last item\n";
	s = s + "\t5. put(string key, &string1, [&string2], [&string3]) as boolean\n";
	s = s + "\t   - put an item into the collection which an item may have up to 3 fields and 1 unique key\n";
	s = s + "\t   - return true if success, false if there is an error or key already exist in collection\n";
	s = s + "\t   - key is not case-sensitive, 'hello' and 'HeLLo' is the same key\n";
	s = s + "\t6. item(string key, &string1, [&string2], [&string3]) as boolean\n";
	s = s + "\t   - read an item specified by given key and return it's fields, this function will not change current read position\n";
	s = s + "\t   - return true if an item specified by given key is found, return false if there is an error or item not found\n";
	s = s + "\t   - key is not case-sensitive, 'hello' and 'HeLLo' is the same key\n";
	s = s + "\t7. clear() as boolean\n";
	s = s + "\t   - return true if clearing all items in collection succees, return false if there is an error or items cannot be cleared\n\n";
	s = s + "c. what's new\n";
	s = s + "\t12 October 2012\n";
	s = s + "\t - initial release version\n";
	s = s + "\t - unsorted collection utilizing linked list algorithm\n";
	s = s + "\t - tested and checked for memory leak\n";
	return s;
}

int hkycollection::count() const { return _col_intCount; }

bool hkycollection::first()
{
	if (_col_nodFirst == NULL)
	{
		delete(_col_nodCurrent);
		return false;
	}
	else
	{
		_col_nodCurrent = _col_nodFirst;
		return true;
	}
}

bool hkycollection::next()
{
	if (_col_nodCurrent == NULL)
	{
		return false;
	}
	else
	{
		if (_col_nodCurrent->child == NULL)
		{
			_col_nodCurrent = NULL;
			return false;
		}
		else
		{
			_col_nodCurrent = _col_nodCurrent->child;
			return true;
		}
	}
}

bool _read(string &psKey, string &psField01, string &psField02, string &psField03)
{
	psKey = "";
	psField01 = "";
	psField02 = "";
	psField03 = "";
	if (_col_nodCurrent == NULL)
	{
		return false;
	}
	else
	{
		psKey = _col_nodCurrent->key;
		psField01 = _col_nodCurrent->field01;
		psField02 = _col_nodCurrent->field02;
		psField03 = _col_nodCurrent->field03;
		return true;
	}
}
bool hkycollection::read(string &psKey) { string s1, s2, s3; return _read(psKey, s1, s2, s3); }
bool hkycollection::read(string &psKey, string &psField01) { string s2, s3; return _read(psKey, psField01, s2, s3); }
bool hkycollection::read(string &psKey, string &psField01, string &psField02) { string s3; return _read(psKey, psField01, psField02, s3); }
bool hkycollection::read(string &psKey, string &psField01, string &psField02, string &psField03) { return _read(psKey, psField01, psField02, psField03); }

bool _put(string psKey, string psField01, string psField02, string psField03)
{
	_col_stcNode *newNode = new _col_stcNode;
	try
	{
		transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);

		newNode->key = psKey;
		newNode->field01 = psField01;
		newNode->field02 = psField02;
		newNode->field03 = psField03;
		newNode->child = NULL;

		if (_col_nodFirst == NULL)
		{
			_col_nodFirst = newNode;
			_col_nodLast = newNode;
		}
		else
		{
			_col_nodLast->child = newNode;
			_col_nodLast = newNode;
		}
		_col_nodCurrent = newNode;
		_col_intCount = _col_intCount + 1;
		return true;
	}
	catch (int e)
	{
		return false;
	}
}
bool hkycollection::put(string psKey) { string s1, s2, s3; return _put(psKey, s1, s2, s3); }
bool hkycollection::put(string psKey, string psField01) { string s2, s3; return _put(psKey, psField01, s2, s3); }
bool hkycollection::put(string psKey, string psField01, string psField02) { string s3; return _put(psKey, psField01, psField02, s3); }
bool hkycollection::put(string psKey, string psField01, string psField02, string psField03) { return _put(psKey, psField01, psField02, psField03); }

bool _item(string psKey, string &psField01, string &psField02, string &psField03)
{
	bool res = false;
	psField01 = "";
	psField02 = "";
	psField03 = "";
	if (_col_nodFirst == NULL) { return res; }
	transform(psKey.begin(), psKey.end(), psKey.begin(), ::toupper);

	_col_stcNode *tempNode;
	tempNode = _col_nodFirst;
	while (tempNode != NULL)
	{
		if (tempNode->key == psKey)
		{
			psField01 = tempNode->field01;
			psField02 = tempNode->field02;
			psField03 = tempNode->field03;
			res = true;
			break;
		}
		tempNode = tempNode->child;
	}
	return res;
}
bool hkycollection::item(string psKey) { string s1, s2, s3; return _item(psKey, s1, s2, s3); }
bool hkycollection::item(string psKey, string &psField01) { string s2, s3; return _item(psKey, psField01, s2, s3); }
bool hkycollection::item(string psKey, string &psField01, string &psField02) { string s3; return _item(psKey, psField01, psField02, s3); }
bool hkycollection::item(string psKey, string &psField01, string &psField02, string &psField03) { return _item(psKey, psField01, psField02, psField03); }

bool hkycollection::clear()
{
	_col_intCount = 0;
	cleanup(_col_nodFirst);
	_col_nodFirst = NULL;
	_col_nodLast = NULL;
	_col_nodCurrent = NULL;
	return true;
}