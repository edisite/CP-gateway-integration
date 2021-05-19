$(document).ready(function(){ 
	$("#search_suggest").mouseover(function(){ isMouseOnDiv = true; });
	$("#search_suggest").mouseout(function(){ isMouseOnDiv = false; });
	$("#ctlSearchFor").blur(function(){ if (!isMouseOnDiv) { DestroySuggestDiv(); }	});
	$("input[@name*=value_]").blur(function(){ if (!isMouseOnDiv) { DestroySuggestDiv(); } });
});

$(document).click(function(){ 
	DestroySuggestDiv();
});
		
var hide  = true;		
var cur = -1;
var selectsToHide = new Array();
var suggestValues = new Array();
var detailsCounter = 0;
var isLookupError = false;
var isSetFocus = false;
var isMouseOnDiv = false;

function myEncode(value)
{
	value = value.replace(/:/g,"%3A");
	value = value.replace(/=/g,"%3D");
	value = value.replace(/&/g,"%26");
	value = value.replace(/\//g,"%3F");
	value = value.replace(/\?/g,"%2F");
	value = value.replace(/\s/g,"+");
	return value;
}

Array.prototype.inArray = function (value,caseSensitive)
{
	var i;
	for (i=0; i < this.length; i++) {
		if (caseSensitive) {
			if (this[i] == value) { return true; }
		} else {
			if (this[i].toLowerCase() == value.toLowerCase()) { return true; }
		}
	}
	return false;
};

function DestroySuggestDiv() 
{
	cur = -1;
	isMouseOnDiv = false;
	$("#search_suggest").html("");
	$("#search_suggest").css({ visibility: "hidden"});
	$.each( selectsToHide, function(i, n){
		n.style.visibility = 'visible';
	});
	selectsToHide.splice(0,selectsToHide.length);
}

function PtInBox(oElement) 
{
	var bFlag = false;
	var el = $("#search_suggest")[0];
	var left = findPos(oElement)[0];
	var top = findPos(oElement)[1];
	var width = findPos(oElement)[2];
	var height = findPos(oElement)[3];
	
	if ( left >= el.offsetLeft && left <= (el.offsetLeft+el.offsetWidth) && top >= el.offsetTop && top <= (el.offsetTop+el.offsetHeight) ) { bFlag = true; }
	if ( (left+width) >= el.offsetLeft && (left+width) <= (el.offsetLeft+el.offsetWidth) && top >= el.offsetTop && top <= (el.offsetTop+el.offsetHeight) ) { bFlag = true; }
	if ( left >= el.offsetLeft && left <= (el.offsetLeft+el.offsetWidth) && (top+height) >= el.offsetTop && (top+height) <= (el.offsetTop+el.offsetHeight) ) { bFlag = true; }
	if ( (left+width) >= el.offsetLeft && (left+width) <= (el.offsetLeft+el.offsetWidth) && (top+height) >= el.offsetTop && (top+height) <= (el.offsetTop+el.offsetHeight) ) { bFlag = true; }
	if ( ( left <= el.offsetLeft && (left+width) >= (el.offsetLeft+el.offsetWidth) ) && ( (el.offsetTop+el.offsetHeight) >= top && el.offsetTop <= (top+height) ) ) { bFlag = true; }

	if ( bFlag ) {
		return true;
	}
	return false;
}

function showhide(obj)
{
	hide = !hide;
	$("#master_details").css("visibility",(hide) ? "hidden" : "visible");
	setLyr(obj,"master_details");
}

function setLyr(obj,lyr)
{
	var coors = findPos(obj);
	if (lyr == 'master_details') coors[0] += coors[2];
	if (lyr == 'search_suggest') coors[1] += coors[3];
	$("#"+lyr).css("top",coors[1] + "px");
	$("#"+lyr).css("left",coors[0] + "px");
}	

function findPos(obj)
{
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		curwidth = obj.offsetWidth
		curheight = obj.offsetHeight
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop,curwidth,curheight];
}
	
function moveUp(oElement, searchType) 
{
	if($("#search_suggest").children().length>0 && cur>=-1)
	{
		cur--;
		if (cur==-2) { cur=$("#search_suggest").children().length-1; oElement.focus(); }
		for(var i=0;i<$("#search_suggest").children().length;i++)
		{
			if(i==cur)
			{
				$("#search_suggest").children().get(i).className = "suggest_link_over";
				oElement.value = $("#search_suggest").children().get(i).innerHTML.replace(/\<(\/b|b)\>/gi,"");
				if ( searchType == 'lookup' ) { 
					isLookupError = false;
					$("input[@name="+oElement.name+"]").removeClass("highlight");
					if ( $("input[@name="+oElement.name.substring(8)+"]").val() != suggestValues[cur] ) {
						$("input[@name="+oElement.name.substring(8)+"]").val(suggestValues[cur]);
						$("input[@name="+oElement.name.substring(8)+"]").change();
					}
				}
			}
			else
			{
				$("#search_suggest").children().get(i).className = "suggest_link";
			}
		}
	}
}
		
function moveDown(oElement, searchType) 
{
	if($("#search_suggest").children().length>0 && cur<($("#search_suggest").children().length))
	{
		cur++;
		for(var i=0;i<$("#search_suggest").children().length;i++)
		{
			if(i==cur)
			{
				$("#search_suggest").children().get(i).className = "suggest_link_over";
				oElement.value = $("#search_suggest").children().get(i).innerHTML.replace(/\<(\/b|b)\>/gi,"");
				if ( searchType == 'lookup' ) { 
					isLookupError = false;
					$("input[@name="+oElement.name+"]").removeClass("highlight");
					if ( $("input[@name="+oElement.name.substring(8)+"]").val() != suggestValues[cur] ) {
						$("input[@name="+oElement.name.substring(8)+"]").val(suggestValues[cur]);
						$("input[@name="+oElement.name.substring(8)+"]").change();
					}
				}
			}
			else
			{
				$("#search_suggest").children().get(i).className = "suggest_link";
			}
		}
		if (cur==($("#search_suggest").children().length)) { cur=-1; oElement.focus(); }
	}
}

function suggestOver(div_value) 
{
	$("div.suggest_link_over").each(function(){
		this.className = 'suggest_link';
	}) ;
	div_value.className = 'suggest_link_over';
	cur = div_value.id.substring(10);
}

function suggestOut(div_value) 
{
	div_value.className = 'suggest_link';
}

function setSearch(inputName, value) 
{
	if ( document.getElementById('ctlSearchFor') == null ) {
		$("input[@name="+inputName+"]").val(value);
		if (setSearch.arguments[2] == 'lookup') {
			isLookupError = false;
			$("input[@name="+inputName+"]").removeClass("highlight");
			if ( $("input[@name="+inputName.substring(8)+"]").val() != setSearch.arguments[3] )
			{
				$("input[@name="+inputName.substring(8)+"]").val(setSearch.arguments[3]);
				$("input[@name="+inputName.substring(8)+"]").change();
			}
		}
	} else {
		$("#ctlSearchFor").val(value);
	}
	DestroySuggestDiv();
}

function showDetailsPreview(obj, str) 
{
	detailsCounter++;
	$("#master_details").html("");
	$.get(str, 
	{
		counter: detailsCounter,
		rndVal: (new Date().getTime())
	}, 
	function(txt){ 
		var str = txt.split("counterSeparator");
		if ( detailsCounter == str[1] ) { 
			$("#master_details").html(str[0]); 
			if ( $.browser.mozilla && ( $("#master_details").get(0).offsetTop > window.innerHeight/2 + document.body.scrollTop ) ) {
				$("#master_details").css("top", ($("#master_details").get(0).offsetTop - $("#master_details").get(0).offsetHeight) +"px");
			} else if ( $.browser.msie && ( $("#master_details").get(0).offsetTop > document.body.clientHeight/2 + document.body.scrollTop ) ) {
				$("#master_details").css("top", ($("#master_details").get(0).offsetTop - $("#master_details").get(0).offsetHeight) +"px");
			}
		}
	});
	showhide(obj);
};		

function hideDetailsPreview(obj) 
{
	showhide(obj);
	$("#master_details").html("");
};			

function listenEvent(oEvent,oElement,searchType) 
{
	oEvent=window.event || oEvent;
	iKeyCode=oEvent.keyCode;

	switch(iKeyCode)
	{	
		case 38: //up arrow
			moveUp(oElement,searchType);
			break;
		case 40: //down arrow
			moveDown(oElement,searchType);
			break;
		case 13: //enter 
			//if (oElement.value != undefined || oElement.value != "") {
			//setSearch(oElement.name, oElement.value, searchType);
			DestroySuggestDiv();
			if ( searchType == 'ordinary' ) { RunSearch(); return false; } 
			if ( searchType == 'advanced' || searchType == 'advanced1' ) { document.forms[0].submit(); return false;}
			if ( searchType == 'lookup' ) { return false; } 
			//}
			break;
	}
	return true;
}

function searchSuggest(oEvent,oElement,searchType) 
{
	oEvent=window.event || oEvent;
	iKeyCode=oEvent.keyCode;
	
	switch(searchType)
	{
		case "ordinary":
			fieldForSearch = $("select#ctlSearchField").val();
			break;
		case "advanced":
			fieldForSearch = oElement.name.substring(6);
			break;
		case "advanced1":
			fieldForSearch = oElement.name.substring(7);
			break;
	}

	if (((iKeyCode >= 65) && (iKeyCode <= 90)) || ((iKeyCode >= 48) && (iKeyCode <= 57))
		|| ((iKeyCode >= 96) && (iKeyCode <= 105)) || (iKeyCode==8) || (iKeyCode==46) || (iKeyCode==32)) {
		cur = -1;
		$.get(SUGGEST_TABLE,
		{
			searchFor: myEncode( oElement.value ), 
			searchField: myEncode( fieldForSearch ),
			rndVal: (new Date().getTime())
		},
		function(txt){
			$("#search_suggest").html("");
			var str = txt.split("\n");
			for(i=0; i < str.length-1; i++) {
				var suggest = '<div id="suggestDiv'+i+'" style="cursor:pointer;" onmouseover="suggestOver(this);" ';
				suggest += 'onmouseout="suggestOut(this);" ';
				suggest += 'onclick="setSearch(\''+oElement.name+'\',this.innerHTML.replace(/\\<(\\/b|b)\\>/gi,\'\'));" ';
				suggest += 'class="suggest_link">' + unescape(str[i]) + '</div>';
				$(suggest).appendTo("#search_suggest")
			}
			if (txt) {
				$("select").each(function(){
					if ( PtInBox(this) ) {
						selectsToHide[selectsToHide.length] = this;
						this.style.visibility = 'hidden';
					}
				});
				$("#search_suggest").css({ visibility: "visible"});
			} else {
				DestroySuggestDiv();
			}
		});
	}
	setLyr(oElement,"search_suggest");
}

function showHideLookupError(oElement)
{
	if ( isLookupError ) {
		$("input[@name="+oElement.name+"]").addClass("highlight");
	} else {
		$("input[@name="+oElement.name+"]").removeClass("highlight");
	}
}

function lookupSuggest(oEvent,oElement, lpValue) 
{
	oEvent=window.event || oEvent;
	iKeyCode=oEvent.keyCode;
	
	if (((iKeyCode >= 65) && (iKeyCode <= 90)) || ((iKeyCode >= 48) && (iKeyCode <= 57))
		|| ((iKeyCode >= 96) && (iKeyCode <= 105)) || (iKeyCode==8) || (iKeyCode==46) || (iKeyCode==32)) {
		cur = -1;
		
		if (oElement.value == "") {
			DestroySuggestDiv();
			isLookupError = false;
			$("input[@name="+oElement.name+"]").removeClass("highlight");
			$("input[@name="+oElement.name.substring(8)+"]").val(""); 
			$("input[@name="+oElement.name.substring(8)+"]").change();
			return;
		}
		
		$.get(SUGGEST_LOOKUP_TABLE,
		{
			searchFor: myEncode( oElement.value ), 
			searchField: myEncode( oElement.name.substring(14) ),
			lookupValue: myEncode( lpValue ),
			rndVal: (new Date().getTime())
		},
		function(txt){
			$("#search_suggest").html("");
			var str = txt.split("\n");
			$.each( str, function(i, n){
				str[i] = unescape(n);
			});
			
			if (str.inArray(oElement.value,false)) {
				isLookupError = false;
				$("input[@name="+oElement.name+"]").removeClass("highlight");
				$.each( str, function(i, n){
					if((n.toLowerCase()==oElement.value.toLowerCase()) && ($("input[@name="+oElement.name.substring(8)+"]").val()!=str[i-1] )) { 
						$("input[@name="+oElement.name.substring(8)+"]").val(str[i-1]); 
						$("input[@name="+oElement.name.substring(8)+"]").change();
					}
				});
			} else {
				isLookupError = true;
				if ( !isSetFocus ) { $("input[@name="+oElement.name+"]").addClass("highlight"); }
			}
			
			for(i=0,j=0; i < str.length-1; i=i+2,j++) {
				var suggest = '<div id="suggestDiv'+i+'" style="cursor:pointer;" onmouseover="javascript:suggestOver(this);" ';
				suggest += 'onmouseout="javascript:suggestOut(this);" ';
				suggest += 'onclick="javascript:setSearch(\''+oElement.name+'\',this.innerHTML,\'lookup\',\''+str[i]+'\');" ';
				suggest += 'class="suggest_link">' + str[i+1] + '</div>';
				$(suggest).appendTo("#search_suggest")
				suggestValues[j] = str[i];
			}
			if (txt) {
				$("select").each(function(){
					if ( PtInBox(this) ) {
						selectsToHide[selectsToHide.length] = this;
						this.style.visibility = 'hidden';
					}
				});
				if ( isSetFocus ) { $("#search_suggest").css({ visibility: "visible"}); }
			} else {
				DestroySuggestDiv();
			}
		});
	}
	setLyr(oElement,"search_suggest");
}