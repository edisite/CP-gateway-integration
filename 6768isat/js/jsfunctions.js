function RunSearch()
{
	document.forms.frmSearch.a.value = 'search'; 
	document.forms.frmSearch.SearchFor.value = document.getElementById('ctlSearchFor').value; 
	document.forms.frmSearch.SearchField.value = document.getElementById('ctlSearchField').options[document.getElementById('ctlSearchField').selectedIndex].value; 
	document.forms.frmSearch.SearchOption.value = document.getElementById('ctlSearchOption').options[document.getElementById('ctlSearchOption').selectedIndex].value; 
	document.forms.frmSearch.submit();
}


function GetGotoPageUrlString (nPageNumber,sUrlText)
{
	return "<a href='JavaScript:GotoPage(" + nPageNumber + ");' style='TEXT-DECORATION: none;'>" + sUrlText 
	+ "</a>";
}

function WritePagination(mypage,maxpages)
{
	if (maxpages > 1 && mypage <= maxpages)
	{
			document.write("<table rows='1' cols='1' align='center' width='95%' border='0'>"); 
			document.write("<tr valign='center'><td align='center'>"); 
			var counterstart = mypage - 9; 
			if (mypage%10) counterstart = mypage - (mypage%10) + 1; 
 
			var counterend = counterstart + 9; 
			if (counterend > maxpages) counterend = maxpages; 
 
			if (counterstart != 1) document.write(GetGotoPageUrlString(1,TEXT_FIRST)+"&nbsp;:&nbsp;"+GetGotoPageUrlString(counterstart - 1,TEXT_PREVIOUS)+"&nbsp;"); 
 
			document.write("<b>[</b>"); 
		
		var pad="";
		var counter	= counterstart;
		for(;counter<=counterend;counter++)
		{
			if (counter != mypage) document.write("&nbsp;" + GetGotoPageUrlString(counter,pad + counter));
			else document.write("&nbsp;<b>" + pad + counter + "</b>");
		}
		document.write("&nbsp;<b>]</b>");
		if (counterend != maxpages) document.write("&nbsp;" + GetGotoPageUrlString (counterend + 1,TEXT_NEXT) + "&nbsp;:&nbsp;" + GetGotoPageUrlString(maxpages,TEXT_LAST))
			
		document.write("</td></tr></table>");		
	}
}


    var rowWithMouse = null;

    function gGetElementById(s) {
      var o = (document.getElementById ? document.getElementById(s) : document.all[s]);
      return o == null ? false : o;
    }

    function rowUpdateBg(row, myId) 
    {
        row.className = (row == rowWithMouse) ? 'rowselected' : ( (myId&1) ? '' : 'shade' );
    }

    function rowRollover(myId, isInRow) {
      // myId is our own integer id, not the DOM id
      // isInRow is 1 for onmouseover, 0 for onmouseout
      var row = document.getElementById('tr_' + myId);
      rowWithMouse = (isInRow) ? row : null;
      rowUpdateBg(row, myId);
    }



function BuildSecondDropDown(arr, SecondField, FirstValue)
{
	document.forms.editform.elements[SecondField].selectedIndex=0;

	document.forms.editform.elements[SecondField].options[0]=new Option(TEXT_PLEASE_SELECT,'');

	var i=1;
	for(ctr=0;ctr<arr.length;ctr+=3)
	{
		if (FirstValue.toLowerCase() == arr[ctr+2].toLowerCase())
		{
			document.forms.editform.elements[SecondField].options[i]=new Option(arr[ctr+1],arr[ctr]);
			i++;
		}
	}
	document.forms.editform.elements[SecondField].length=i;
	if(i<3 && i>1 && !bLoading)
		document.forms.editform.elements[SecondField].selectedIndex=1;
	else
		document.forms.editform.elements[SecondField].selectedIndex=0;
}

function SetSelection(FirstField, SecondField, FirstValue, SecondValue, arr)
{
	var ctr;

	BuildSecondDropDown(arr, SecondField, FirstValue);	 
	if(SecondValue=="" && document.forms.editform.elements[SecondField].length<3)
		return;
	for (ctr=0; ctr<document.forms.editform.elements[SecondField].length; ctr++)
	 if (document.forms.editform.elements[SecondField].options[ctr].value.toLowerCase() == SecondValue.toLowerCase() )
	 	 {
		  document.forms.editform.elements[SecondField].selectedIndex = ctr;
		  break;
		 }
}
function padDateValue(value,threedigits)
{
	if(!threedigits)
	{
		if(value>9)
			return ''+value;
		return '0'+value;
	}
	if(value>9)
	{
		if(value>99)
			return ''+value;
		return '0'+value;
	}
	return '00'+value;
}

function getTimestamp()
{
	var ts = "";
	var now = new Date();
	ts += now.getFullYear();
	ts+=padDateValue(now.getMonth()+1,false);
	ts+=padDateValue(now.getDate(),false)+'-';
	ts+=padDateValue(now.getHours(),false);
	ts+=padDateValue(now.getMinutes(),false);
	ts+=padDateValue(now.getSeconds(),false);
	return ts;
}

function addTimestamp(filename)
{
	var wpos=filename.lastIndexOf('.');
	if(wpos<0)
		return filename+'-'+getTimestamp();
	return filename.substring(0,wpos)+'-'+getTimestamp()+filename.substring(wpos);
}

function create_option( theselectobj, thetext, thevalue ) 
{
theselectobj.options[theselectobj.options.length]= new Option(thetext,thevalue);
}

function SetToFirstControl()
{
  var bFound = false;

  // for each form
  for (f=0; f < document.forms.length; f++)
  {
    // for each element in each form
    for(i=0; i < document.forms[f].length; i++)
    {
      // if it's not a hidden element
      if (document.forms[f][i].type != "hidden")
      {
        // and it's not disabled
        if (document.forms[f][i].disabled != true)
        {
	try
		{
	            // set the focus to it
        	    document.forms[f][i].focus();
	            var bFound = true;
		}
	catch(er)
		{
		} 
       }
      }
      // if found in this element, stop looking
      if (bFound == true)
        break;
    }
    // if found in this form, stop looking
    if (bFound == true)
      break;
  }
}
