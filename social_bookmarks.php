<?php

//this script is called directly by browsers
//we need php in this script only to put in the link to the social bookmark directory
$directory=preg_replace('/\/[^\/]*$/',"",$_SERVER['REQUEST_URI']);

?>function sbopen(url){
	sbwin=window.open(url, '_blank', 'scrollbars=yes,menubar=no,height=600,width=750,resizable=yes,toolbar=no,location=no,status=no');
	sbwin.focus();
}

function replace_link_title(intext, link, thetext) {
	intext = intext.replace('##THE_LINK##', link);
	intext = intext.replace('##THE_TITLE##', thetext);
	intext = intext.replace('%23%23THE_TITLE%23%23', thetext);
	return intext.replace('%23%23THE_TITLE%23%23', thetext);
}


function sbbview(divid, sbblink, sbbtitle){
	var ssbdiv = document.getElementById(divid);
	if (ssbdiv.style.display != 'block') {
		ssbdiv.style.display = 'block';
	}else {
		if (ssbdiv.style.display == 'block') {
			ssbdiv.style.display = 'none';
		}
	}
	
	if(ssbdiv.id != 'sbbpt_id_social_prototype'){
		var sbbprototype = document.getElementById('sbbpt_id_social_prototype');
		var numofchilds  = sbbprototype.childNodes.length -1;
		for(var i = 0; numofchilds >= i;  i++)	{
			if(!ssbdiv.childNodes[i]){
				var clonednode = sbbprototype.childNodes[i].cloneNode(true);
				ssbdiv.appendChild(clonednode);
			}
		}
	}
	
	var numofchilds  = ssbdiv.childNodes.length -1;
	for(var i = 0; numofchilds >= i;  i++)	{
		if(ssbdiv.childNodes[i].href){
			ssbdiv.childNodes[i].href=replace_link_title(ssbdiv.childNodes[i].href, sbblink, sbbtitle);
		}
		if(ssbdiv.childNodes[i].title){
			ssbdiv.childNodes[i].title=replace_link_title(ssbdiv.childNodes[i].title, sbblink, sbbtitle);
		}
	}
	
	return;
}

function writesbb(link, caption, icon){
	document.write('<a onclick="sbopen(this.href);return false;" href="' + link + '" title="\'##THE_TITLE##\' bookmarken bei ' + caption + '"><img style="padding:1px; width:18px; height:18px; border-width:0px;" src="<?php echo $directory; ?>' + icon + '" alt="' + caption + '" /></a>');
	return;
}


document.write('<div id="sbbpt_id_social_prototype" style="display:none;padding:5px; margin-left:auto; margin-right:auto; text-align:center;">');

writesbb('http://www.wikio.de/vote?url=##THE_LINK##', 'Wikio', '/ico/sb/wikio2_18x18.jpg');
writesbb('http://www.alltagz.de/bookmarks/?action=add&amp;address=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23', 'Alltagz', '/ico/sb/alltagz.gif');
writesbb('http://www.icio.de/add.php?url=##THE_LINK##"', 'icio', '/ico/sb/icio.gif');
writesbb('http://infopirat.com/node/add/userlink?edit[url]=##THE_LINK##&amp;edit[title]=%23%23THE_TITLE%23%23"', 'Infopirat', '/ico/sb/infopirat.gif');
writesbb('http://linkarena.com/bookmarks/addlink/?url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Linkarena', '/ico/sb/linkarena.gif');
writesbb('http://www.mister-wong.de/addurl/?bm_url=##THE_LINK##&amp;bm_description=%23%23THE_TITLE%23%23"', 'Mister Wong', '/ico/sb/mister-wong.gif');
writesbb('http://www.oneview.de/quickadd/neu/addBookmark.jsf?URL=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Oneview', '/ico/sb/oneview.gif');
writesbb('http://tausendreporter.stern.de/submit.php?url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Tausend Reporter', '/ico/sb/tausend-reporter-stern.gif');
writesbb('http://www.webnews.de/einstellen?url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Webnews', '/ico/sb/webnews.gif');
writesbb('http://yigg.de/neu?exturl=##THE_LINK##"', 'Yigg', '/ico/sb/yigg.gif');
writesbb('http://del.icio.us/post?url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Del.icio.us', '/ico/sb/delicious.png');
writesbb('http://digg.com/submit?phase=2&amp;url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'digg', '/ico/sb/digg.png');
writesbb('http://blinklist.com/index.php?Action=Blink/addblink.php&amp;Name=%23%23THE_TITLE%23%23&amp;Description=%23%23THE_TITLE%23%23&amp;Url=##THE_LINK##"', 'blinklist', '/ico/sb/blinklist.png');
writesbb('http://www.technorati.com/faves?add=##THE_LINK##"', 'Technorati', '/ico/sb/technorati.png');
writesbb('http://myweb2.search.yahoo.com/myresults/bookmarklet?u=##THE_LINK##&amp;t=%23%23THE_TITLE%23%23"', 'Yahoo My Web', '/ico/sb/yahoo_myweb.png');
writesbb('http://www.google.com/bookmarks/mark?op=edit&amp;output=popup&amp;bkmk=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Google Bookmarks', '/ico/sb/google.png');
writesbb('http://co.mments.com/track?url=##THE_LINK##&amp;title=%23%23THE_TITLE%23%23"', 'Co.mments', '/ico/sb/comments.png');
writesbb('http://www.bloglines.com/sub/##THE_LINK##"', 'Bloglines', '/ico/sb/bloglines.png');

document.write('</div>');