#include <algorithm>		// required for commands: atoi, transform
#include <iostream>			// required for commands: cout
#include <sstream>			// required to cast integer to string
#include <string.h>			// required for string manipulation - strcat, strcpy, bzero, bcopy
#include <time.h>			// required to get date and time
#include <math.h>			// used to calculate application lifespan
#include "mycollection.h"

using namespace std;

mycollection gcolKwTransmit;
mycollection gcolKwResponse;


// FUNCTION, EXTRACT XML FIELD FOR SPECIFIED KEY #################################################################################
string xml_get_value(string sSource, string sKey)
{
	int n;
	size_t a, b;

	string sUPPER = sSource;
	transform(sUPPER.begin(), sUPPER.end(), sUPPER.begin(), ::toupper);

	transform(sKey.begin(), sKey.end(), sKey.begin(), ::toupper);
	string sSeek = "<" + sKey + ">";

	a = sUPPER.find(sSeek);
	if (a == string::npos) { return ""; }
	n = int(a) + sSeek.length();

	b = sUPPER.find("</" + sKey + ">", n);
	return (b == string::npos) ? sSource.substr(n) : sSource.substr(n, int(b) - n);
}

void setting_get_keywords(string psKeywords, string psURLTransmit, string psURLResponse)
{
	int i;
	int n;
	int iMode; // 0: do nothing, 1: record owner, 2: record keyword
	string s = "";
	string ss = "";
	string sOwner = "";
	string sKeyword = "";
	string sURL = "";
	string sMTD = "";
	string sTemp = "";
	string sTOUT = "";
	int Q = 0;

	// transform raw xml keyword string into upper case to avoid case-sensitive problems
	transform(psKeywords.begin(), psKeywords.end(), psKeywords.begin(), ::toupper);

	// set initial recording mode to doing nothing
	iMode = 0;

	// count the length of raw xml keyword string
	n = psKeywords.length();

	// start spliting and recording process
	for (i = 0; i < n; i++)
	{
		// when closing tag found
		if (psKeywords.substr(i, 2) == "</")
		{
			// check currently in reading value mode
			if (iMode == 1)
			{
				// this will happen when reading owner without keyword, example: <owner></owner>
				// in this case do not add empty keyword into keyword's collection, just do nothing
			}
			else if (iMode == 2)
			{
				// this will happen when reading last keyword listed, example: <owner>a,b,c,LAST</owner>
				// so add keyword LAST into keyword's collection
				sTemp = xml_get_value(psURLTransmit, sOwner);
				sMTD = xml_get_value(sTemp, "method");
				sURL = xml_get_value(sTemp, "address");
				sTOUT = xml_get_value(sTemp, "timeout");
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLTransmit, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
					sTOUT = xml_get_value(sTemp, "timeout");
				}
				gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL);

				sTemp = xml_get_value(psURLResponse, sOwner);
				sMTD = xml_get_value(sTemp, "method");
				sURL = xml_get_value(sTemp, "address");
				sTOUT = xml_get_value(sTemp, "timeout");
				gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL);
				if (sMTD == "" || sURL == "")
				{
					sTemp = xml_get_value(psURLResponse, "default");
					sMTD = xml_get_value(sTemp, "method");
					sURL = xml_get_value(sTemp, "address");
					sTOUT = xml_get_value(sTemp, "timeout");
				}

				sKeyword = "";
			}

			// reset reading mode into 0 which make next reading not processed
			iMode = 0;
		}
		else
		{
			s = psKeywords.substr(i, 1);
			if (s == " ") // invalid characters and will not be processed
			{
				// do nothing
			}
			else if (s == "<") // if opening tag found, switch to recording element name
			{
				iMode = 1;
				sOwner = "";
				sKeyword = "";
			}
			else if (s == ">")
			{
				if (iMode == 0) // if closing tag found while not in recording mode, ignore it
				{
					// do nothing
				}
				else if (iMode == 1) // if closing tag found while in recording element name, then switch it to record element value
				{
					iMode = 2;
				}
			}
			else
			{
				if (iMode == 0)
				{
					// do nothing
				}
				else if (iMode == 1)
				{
					// record owner
					sOwner = sOwner + s;
				}
				else if (iMode == 2)
				{
					if (s == ",")
					{
						// keyword separator found, add keyword into keyword's collection
						sTemp = xml_get_value(psURLTransmit, sOwner);
						sMTD = xml_get_value(sTemp, "method");
						sURL = xml_get_value(sTemp, "address");
						sTOUT = xml_get_value(sTemp, "timeout");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLTransmit, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
							sTOUT = xml_get_value(sTemp, "timeout");
						}
						gcolKwTransmit.put(sKeyword, sOwner, sMTD, sURL, sTOUT);

						sTemp = xml_get_value(psURLResponse, sOwner);
						sMTD = xml_get_value(sTemp, "method");
						sURL = xml_get_value(sTemp, "address");
						sTOUT = xml_get_value(sTemp, "timeout");
						if (sMTD == "" || sURL == "")
						{
							sTemp = xml_get_value(psURLResponse, "default");
							sMTD = xml_get_value(sTemp, "method");
							sURL = xml_get_value(sTemp, "address");
							sTOUT = xml_get_value(sTemp, "timeout");
						}
						gcolKwResponse.put(sKeyword, sOwner, sMTD, sURL, sTOUT);

						sKeyword = "";
					}
					else
					{
						// record keyword
						sKeyword = sKeyword + s;
					}
				}
				else {} // do nothing
			}
		}
	}
}

int main (int argc, char **argv)
{
	int i = 0;
	string s1, s2, s3;

	s1 = ""; s2 = ""; s3 = "";

	s1 = s1 + "<owner_keyword>";
	s1 = s1 + "*<crm>bugfirstkeyword,star,sa,skymobi,sky,mobi,starsky,starmoby,ayu1,ayu7,dewi,goal,game,sehat</crm>";
	s1 = s1 + "	<koin>ac,astro,atm,bas,belajarku,best,pulsa,khalifah,bg,bgpol,bola,budget,cck,cek,cs,data,far,fav,gprs,harga,hari</koin>";
	s1 = s1 + "	<koin>hoki,hp,hpturun,info,jawab,jomblo,kby,koinfun,koinhp,koinluv,kz,lagu,lahir,lelang,maju,merdeka,mimpi,motivasi</koin>";
	s1 = s1 + "	<koin>murah,nama,p,pantau,pantun,pilih,poll,rbt,rekomendasi,request,saldo,saran,sholat,stnk,sumpah,survey,thr</koin>";
	s1 = s1 + "	<koin>tj,total,tpt,vobe,warna,weton,wisata,umb_harga,umb_spec,umb_tips,umb_rekomendasi,umb_info,umb_aduhp,umb_setara</koin>";
	s1 = s1 + "	<koin>umb_plusminus,umb_pantau,umb_program,_fbr,_pekat,unreg_fbr,unreg_pekat,radio</koin>";
	s1 = s1 + "	<koin>tomboati,cahayahati,asmaulhusna,ym,rahasia,kisah,hikmah,matahati,pls,tauladan,bsmr</koin>";
	s1 = s1 + "	<cheese>amoy,belle,boom,cherry,chibi,dl,fm,hantu,horor,khaliza,koko,kultum,marawis,miss,model,nta,predik,pssi,rianti,rot,salam,song,tidak,tiket,timnas,tr,vid,wp,wpnta,wpmu,ya,zakat</cheese>";
	s1 = s1 + "</owner_keyword>";

	s2 = s2 + "<transmitter>";
	s2 = s2 + "	<default>";
	s2 = s2 + "		<method>get</method>";
	s2 = s2 + "		<timeout>3</timeout>";
	s2 = s2 + "		<address>http://10.1.1.75:7004/mo</address>";
	s2 = s2 + "	</default>";
	s2 = s2 + "	<crm>";
	s2 = s2 + "		<method>get</method>";
	s2 = s2 + "		<timeout>3</timeout>";
	s2 = s2 + "		<address>http://10.1.1.75:7004/mo</address>";
	s2 = s2 + "	</crm>";
	s2 = s2 + "	<koin>";
	s2 = s2 + "		<method>get</method>";
	s2 = s2 + "		<timeout>3</timeout>";
	s2 = s2 + "		<address>http://202.149.71.75/koinfrm/indosis-mo.php</address>";
	s2 = s2 + "	</koin>";
	s2 = s2 + "	<cheese>";
	s2 = s2 + "		<method>get</method>";
	s2 = s2 + "		<timeout>3</timeout>";
	s2 = s2 + "		<address>http://116.90.162.34/cheesefrm/main_slg.php</address>";
	s2 = s2 + "	</cheese>";
	s2 = s2 + "</transmitter>";


	s3 = s3 + "<response>";
	s3 = s3 + "	<success_identifier1><status>0</status></success_identifier1>";
	s3 = s3 + "	<save_to_db>";
	s3 = s3 + "		<enabled>no</enabled>";
	s3 = s3 + "		<into_table></into_table>";
	s3 = s3 + "		<into_field1></into_field1>";
	s3 = s3 + "		<from_xml_field1></from_xml_field1>";
	s3 = s3 + "	</save_to_db>";
	s3 = s3 + "	<forward>";
	s3 = s3 + "		<enabled>no</enabled>";
	s3 = s3 + "		<recipient>";
	s3 = s3 + "			<default>";
	s3 = s3 + "				<method></method>";
	s3 = s3 + "				<address></address>";
	s3 = s3 + "			</default>";
	s3 = s3 + "		</recipient>";
	s3 = s3 + "	</forward>";
	s3 = s3 + "</response>";


	setting_get_keywords(s1, s2, s3);


	string sVal1, sVal2, sVal3, sVal4, sVal5;

	// show keywords mapping
	cout << "keywords mapping - transmitter" << endl;
	cout << "number; keyword; owner; method; url" << endl;
	cout << "===================================================================" << endl;
	i = 0;
	gcolKwTransmit.first();
	i = i + 1;
	gcolKwTransmit.read(sVal1, sVal2, sVal3, sVal4);
	cout << "    " << i << "; " << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << endl;
	while (gcolKwTransmit.next() == 0)
	{
		i = i + 1;
		gcolKwTransmit.read(sVal1, sVal2, sVal3, sVal4);
		cout << "    " << i << "; " << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << endl;
	}

	// show keywords mapping
	cout << endl;
	cout << "keywords mapping - forwarding response" << endl;
	cout << "number; keyword; owner; method; url" << endl;
	cout << "===================================================================" << endl;
	i = 0;
	gcolKwResponse.first();
	i = i + 1;
	gcolKwResponse.read(sVal1, sVal2, sVal3, sVal4);
	cout << "    " << i << "; " << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << endl;
	while (gcolKwResponse.next() == 0)
	{
		i = i + 1;
		gcolKwResponse.read(sVal1, sVal2, sVal3, sVal4);
		cout << "    " << i << "; " << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << endl;
	}

	sVal1 = sVal2 = sVal3 = sVal4 = sVal5 = "";

	cout << "--------------------" << endl;
	gcolKwTransmit.item("bugfirstkeyword", sVal2, sVal3, sVal4, sVal5);
	cout << "bugfirstkeyword" << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << "; " << sVal5 << endl;

	gcolKwResponse.item("bugfirstkeyword", sVal2, sVal3, sVal4, sVal5);
	cout << "bugfirstkeyword" << sVal1 << "; " << sVal2 << "; " << sVal3 << "; " << sVal4 << "; " << sVal5 << endl;

}

