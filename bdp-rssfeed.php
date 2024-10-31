<?php
//this a a complete new parser from brian's 0.63 version just with old class names
//http://www.ozpolitics.info/plugins/bdp-rssaggregator-0-6-3.zip
//shall be ready now

if( !class_exists('BDPFeed') ) 
{
	mb_internal_encoding( get_option('blog_charset') );
	mb_regex_encoding( get_option('blog_charset') );

	class BDPFeed 
	{
	/* This is a quick and dirty class to sort through a single channel feed.
	 */
		var $url;
//		var $preserveTagsList;
		
		function BDPFeed($url) 
		{
			$this->url = $url;
		}
		
		function title_recode($title, $captureTags=false) 
		{
		/* get rid of difficult characters - we don't want quotes as they affect the SQL and echos
		 * we don't want XHTML code here as it might cause problems when printed
		 * encode unencoded ampersands
		 */
			// blogspot puts encoded tags into the feed stream - decode them
			$title = mb_eregi_replace("\&lt;", 		'<', 		$title);
			$title = mb_eregi_replace("\&gt;", 		'>', 		$title);
			$title = mb_eregi_replace("\&amp;([a-z]+);",	'&\\1;',	$title);
			$title = mb_eregi_replace("\&amp;#([0-9]+);",	'&#\\1;',	$title);
			$title = mb_eregi_replace("\&amp;#x([a-f0-9]+);",'&#x\\1;',	$title);
			
			// remove CDATA tags - leave XHTML tags
			$title = preg_replace("'<!\[CDATA\[(.*?)\]\]>'si", '\\1', $title);
			
			// tidy-up quotes - prevents SQL insertion
			$title = mb_ereg_replace('"',	 		'&quot;', 	$title);
			$title = mb_ereg_replace("'", 			'&#39;',  	$title);
			$title = mb_eregi_replace('\&apos;',	'&#39;',  	$title); // old browser fix

			//Parteibuch change: Problems with comments not closed fixed
			$title = mb_eregi_replace('<!--[^>]*-->', 	'', 	$title); 
			$title = mb_eregi_replace('<!--', 	'', 	$title); 

			// find unencoded ampersands and encode them!
			$title = mb_ereg_replace('<',	 		'&lt;',  	$title);
			$title = mb_ereg_replace('>',	 		'&gt;',  	$title);
			$title = mb_eregi_replace('\&([a-z]+);', 	'<\\1>', 	$title); // alpha
			$title = mb_eregi_replace('\&(#[0-9]+);',	'<\\1>', 	$title); // decimal
			$title = mb_eregi_replace('\&(#x[a-f0-9]+);',	'<\\1>', 	$title); // hex
			$title = mb_eregi_replace('\&', 		'&amp;', 	$title);
			$title = mb_eregi_replace('<([^>]+)>',		'&\\1;',	$title);

			//Parteibuch fix: avoid umlaut encoding errors
			$title = mb_ereg_replace('\&A[uU][mM][lL]', 	'&#196', 	$title); 
			$title = mb_ereg_replace('\&O[uU][mM][lL]', 	'&#214', 	$title); 
			$title = mb_ereg_replace('\&U[uU][mM][lL]', 	'&#220', 	$title); 
			$title = mb_ereg_replace('\&a[uU][mM][lL]', 	'&#228', 	$title); 
			$title = mb_ereg_replace('\&o[uU][mM][lL]', 	'&#246', 	$title); 
			$title = mb_ereg_replace('\&u[uU][mM][lL]', 	'&#252', 	$title); 
			$title = mb_eregi_replace('\&szlig', 	'&#223', 	$title); 


			// tidy-up white spaces
			$title = mb_eregi_replace('\&nbsp;', 	' ',	$title);

			//parteibuch: get rid of all lower ascii chars, treat them as blanks
			$title = mb_eregi_replace('[ \n\r\s(\x00-\x1F)]+', ' ',	$title);
			
			return $title;
		}
		
		function rebaseAddresses($itemtext, $siteURL)
		{
			// simplify and manipulate links in the itemtext
			// 1 - restore quotes and angle brackets -- just for a moment
			$itemtext = mb_eregi_replace('&quot;',	 	'"', 	$itemtext);
			$itemtext = mb_eregi_replace('&#39;', 		"'",  	$itemtext);
			$itemtext = mb_eregi_replace('&lt;',	 	'<', 	$itemtext);
			$itemtext = mb_eregi_replace('&gt;', 		'>',  	$itemtext);
			
			// 2 - simplify and standardise the HTML
			$itemtext = mb_eregi_replace('<img ([^>]*)src="([^">]*)"([^>]*) />',
				"<img src='\\2' \\1 \\3 />", $itemtext);
			$itemtext = mb_eregi_replace("<img ([^>]*)src='([^'>]*)'([^>]*) />",
				"<img src='\\2' \\1 \\3 />", $itemtext);
			$itemtext = mb_eregi_replace("<img (src='[^'>]*')([^>]*)width=['\"]([^'\">]*)['\"]([^>]*) />",
				"<img \\1 width='\\3' \\2 \\4 />", $itemtext);
			$itemtext = mb_eregi_replace(
				"<img (src='[^'>]*' width='[^'>]*')[^>]*height=['\"]([^'\">]*)['\"][^>]* />",
				"<img \\1 height='\\2' />", $itemtext);
			$itemtext = mb_eregi_replace("<a [^>]*href='([^\'>]*)'[^>]*>",
				"<a href='\\1' target='_blank' rel='nofollow'>", $itemtext);
			$itemtext = mb_eregi_replace('<a [^>]*href="([^"\'>]*)"[^>]*>',
				"<a href='\\1' target='_blank' rel='nofollow'>", $itemtext);

			// 3 - substitute in full address to relative addresses
			$itemtext = mb_eregi_replace("<img src='/([^'\>]+'[^\>]+) />",
				"<img src='$siteURL/\\1 />", $itemtext);
			$itemtext = mb_eregi_replace( "<a href='/([^'>]+'[^\>]+)>",
				"<a href='$siteURL/\\1>", $itemtext);
				
			// 4 -- other tidy-ups
			$itemtext = mb_eregi_replace('<p [^>]*>', '<p>', $itemtext);
			$itemtext = mb_eregi_replace('<li [^>]*>', '<li>', $itemtext);
			$itemtext = mb_eregi_replace('<br[^>]* />', '<br />', $itemtext);

			//echo "<!-- DEBUG: $itemtext -->\n";

			// 5 - kill the quotes to be SQL secure
			$itemtext = mb_eregi_replace('"',	 	'&quot;', 	$itemtext);
			$itemtext = mb_eregi_replace("'", 		'&#39;',  	$itemtext);
			$itemtext = mb_eregi_replace('<',	 	'&lt;', 	$itemtext);
			$itemtext = mb_eregi_replace('>', 		'&gt;',  	$itemtext);
			
			return $itemtext;
		}

		function reg_capture($pattern, $subject) 
		{
			// start regular expression
			mb_eregi($pattern, $subject, $out);
			
			// if there is some result... process it and return it
			if(isset($out[1])) 
				return $out[1];
			else 
				// if there is NO result, return nothing
				return FALSE;
		}
		
		function reg_capture_all($splitter, $tail, $subject) 
		{

			$a = mb_split($splitter, $subject);
			$out = array();
			if (count($a) > 1 )
			{
				for($i=1; $i<count($a); $i++)
				{
					$out[$i-1] = mb_eregi_replace($tail, '', $a[$i]);
				}
				return $out;
			}
			return FALSE;

		}
		
		function parse()
		{
			global $bdprss_db;
			
			$result = array();
			$result['items'] = array();
			
			$snoopy = new Snoopy();
			$snoopy->agent = PBA_PRODUCT . ' ' . PBA_VERSION;
			$snoopy->read_timeout = 8;	// THINK ABOUT THIS!
			$snoopy->curl_path = FALSE;	// THINK ABOUT THIS!
			$snoopy->maxredirs = 2;
			
			if(! @$snoopy->fetch($this->url))
			{
				$bdprss_db->recordError($this->url, "Could not open ".$this->url);
				return FALSE;
			}
			$content = $snoopy->results;
			
			if($snoopy->error) $bdprss_db->recordError($this->url, $snoopy->error);
	
			if(!$content)
			{
				$bdprss_db->recordError($this->url, "Snoopy did not recover any content? See snoopy object here: " . PBALIB::get_r($snoopy));
				return FALSE;
			}
			
						//Parteibuch: detect charset
			$old_charset = $this->reg_capture("'<?xml[^>]*encoding=\"(.*?)\"[^>]*?>'", $content);
			$new_charset = get_option( 'blog_charset' ); 

			// sort out character encoding
			if($old_charset != $new_charset){
				mb_detect_order('WINDOWS-1252, UTF-8, ISO-8859-1');
				if(!$old_charset) $old_charset = mb_detect_encoding( $content ); 
				//print 'DEBUG: ' . $old_charset;
				$content = @mb_convert_encoding($content, /*to*/$new_charset, /*from*/$old_charset);
			}
			
			// quick and dirty -- work out the feedtype
			//mb_regex_encoding('UTF-8'); // this file is written in UTF-8
			$feedtype = FALSE;
			if ( mb_eregi('<rss[^>]*?>.*?</rss>', $content) )
			{
				$feedtype = 'RSS';
				$feed = $this->reg_capture('<channel[^>]*?>(.*?)</channel>', $content);
				$channeltags = array ('title', 'link', 'description', 'copyright');
				$itemtags = array('title', 'link', 'description', 'content:encoded', 'pubDate', 'dc:date', 'guid', 'issued', 'modified', 'created', 'published', 'updated', 'dc:creator', 'dc:source', 'dc:rights');
				$item = 'item';
			}
			if ( !$feedtype && mb_eregi('<rdf[^>]*?>.*?</rdf[^>]*?>', $content) )
			{
				$feedtype = 'RDF';
				$feed = $content;
				$channeltags = array ('title', 'link', 'description', 'dc:creator', 'dc:date');
				$itemtags = array('title', 'link', 'description', 'dc:date', 'dc:subject', 'dc:creator', );
				$item = 'item';
			}
			if ( !$feedtype && mb_eregi('<feed[^>]*?>.*?</feed>', $content) )
			{
				$feedtype = 'ATOM';
				$feed = $this->reg_capture('<feed[^\>]*?>(.*?)</feed>', $content);
				$channeltags = array('title', 'tagline', 'link');
				$itemtags = array('title', 'summary', 'link', 'content', 'issued', 'modified', 'created', 'published', 'updated'); 
				$bloggerlink1 = "<link[^\>]*href=[\"']([^\"']*)[\"'][^\>]*?type=[\"']text/html[\"'][^\>]*?>";
				$bloggerlink2 = "<link[^\>]*type=[\"']text/html[\"'][^\>]*?href=[\"']([^\"']*)[\"'][^\>]*?>";
				$bloggerlink3 = "<link[^\>]*href=[\"']([^\"']*)[\"'][^\>]*>";
				$item = 'entry';
			}
			if ( !$feedtype )
			{
				$bdprss_db->recordError($this->url, "Cannot ascertain feed-type, therefore ignored");
				return FALSE;
			}
			$result['feedtype'] = $feedtype;

			if(BDPRSS2_DEBUG) $bdprss_db->recordError($this->url, "DEBUG feedtype: $feedtype");
			if(BDPRSS2_DEBUG) $bdprss_db->recordError($this->url, "DEBUG feedsize: ".strlen($feed));
			
			// get the overarching feed information
			foreach($channeltags as $tag) 
			{
				if($feedtype == 'ATOM' && $tag == 'link') 
				{
					$tmp = $this->reg_capture($bloggerlink1, $feed);
					if(!$tmp) $tmp = $this->reg_capture($bloggerlink2, $feed);
					if(!$tmp) $tmp = $this->reg_capture($bloggerlink3, $feed);
				}
				else
					$tmp = $this->reg_capture('<'.$tag.'[^>]*?>(.*?)</'.$tag.'>', $feed);
				
				if(!$tmp) continue;
				$result[$tag] = $this->title_recode($tmp);
			}
			
			// manipulate site URL for use with indirect references
			$siteURL = $result['link'];
			if(!$siteURL)
				$bdprss_db->recordError($this->url, "Feed does not include a site URL");
			else
				$siteURL = mb_eregi_replace("(http://[^/]*).*$", "\\1", $siteURL);
			
			// get the item information			
			$itemArray = $this->reg_capture_all('<'.$item.'[^>]*?>', '</'.$item.'>.*', $feed);
			if(!$itemArray) 
			{
				$bdprss_db->recordError($this->url, "Feed did not contain any items");
				return $result;
			}
			
			$itemcount = count($itemArray);
			$i = 0;
			while ( $i < $itemcount ) 
			{
				$itm = $itemArray[$i];
				foreach( $itemtags as $itag ) 
				{
					if($feedtype == 'ATOM' && $itag == 'link') 
					{
						$tmp = $this->reg_capture($bloggerlink1, $itm);
						if(!$tmp) $tmp = $this->reg_capture($bloggerlink2, $itm);
						if(!$tmp) $tmp = $this->reg_capture($bloggerlink3, $itm);
					}
					else
						$tmp = $this->reg_capture('<'.$itag.'[^>]*?>(.*?)</'.$itag.'>', $itm);
					
					//parteibuch - we don't want feeds without a siteurl
					if ($tmp == '' || !$siteURL) continue;
					
					$tmp = $this->title_recode($tmp);
					
					if($siteURL)
						if($itag == 'content:encoded' || $itag == 'description' || 
						$itag == 'content' || $itag == 'summary')
							$tmp = $this->rebaseAddresses($tmp, $siteURL);
						
					//parteibuch blog.de fix of links directed to www.blog.de instead of subdomain
					if(strstr($tmp,'www.blog.de') && strstr($siteURL,'blog.de') && $itag == 'link'){
						$tmp = eregi_replace("http://[^/]*/(.*)$", "$siteURL/\\1", $tmp);
						//if(strstr($siteURL,'blog.de')) $bdprss_db->recordError($this->url, "DEBUG for rebased blog.de item link: $tmp");
					}

					$result['items'][$i][$itag] = $tmp;
				}
				$i++;
			}
			return $result;
		}
	}
}
?>