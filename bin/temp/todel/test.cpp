#include <iostream>			// required for commands: cout
#include <sstream>			// required to cast integer to string
#include <fstream>			// required to open xml file in stream mode
#include <string.h>			// required for string manipulation - strcat, strcpy, bzero, bcopy
#include <stdlib.h>
#include "mycollection.h"

using namespace std;


int test()
{
	mycollection gcol;
	gcol.put("batman", "batman1", "batman2", "batman3");
	gcol.put("superman", "superman1", "superman2", "superman3");
	gcol.put("spiderman", "spiderman1", "spiderman2", "spiderman3");
	gcol.put("hulk", "hulk1", "hulk2", "hulk3");
	gcol.put("THOR", "THOR1", "THOR2", "THOR3");
	gcol.put("jackal", "jackal1", "jackal2", "jackal3");
	gcol.put("ironman", "ironman1", "ironman2", "ironman3");
	gcol.put("joker", "joker1", "joker2", "joker3");
	gcol.put("robin", "robin1", "robin2", "robin3");
	return 0;	
}

int main (int argc, char **argv)
{
	int i = 0;
	
	while (1)
	{
		i = i + 1;
		cout << i << " ";
		test();	
		cout << " done" << endl;
	}

}