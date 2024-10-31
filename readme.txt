=== Parteibuch Aggregator ===
Contributors: katzenfreund
Donate link: http://www.mein-parteibuch.com/
Tags: aggregator, rss, page, sidebar, search
Requires at least: 2.1
Tested up to: 2.7.1
Stable tag: 0.5.2

Easy to use RSS aggregator for pages and sidebars of your blog with search capability.

== Description ==
The Parteibuch Aggregator is a RSS aggregator that will display aggregated feeds in wordpress pages and sidebars. Share the feeds you read with the visitors of your blog. It is designed to be able to cope with hundreds of feeds, hundred thousands of aggregated feed items and a lot of traffic.

Main features:

* users can search in aggregated feeds
* allows to display diffrent groups of feeds on different pages
* templates make the display of aggregated feeds almost completely customizable
* provides a widget for displaying aggregated feeds and searchform in the sidebar
* provides RSS feeds from aggregated feeds and search results

== Installation ==

1. Unzip and upload `parteibuch-aggregator` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in your blog
1. Follow the link `start configuration here` provided by `Parteibuch Aggregator` in plugin description in your blog's 'Plugins' menu
1. If you like, add a Parteibuch Aggregator widget to the sidebar of your blog

== Frequently Asked Questions ==

= What php/mysql version do I need to run the Parteibuch Aggregator? =

Minimal system requirements for the Parteibuch Aggregator are:

* mysql 5.0 or higher (Parteibuch Aggregator may work on mysql 4.x versions, but it was not tested on any . If you want to try that anyway, you have to outcomment the version check in source. Please drop us a line about your experiences.)
* php 5.0 or higher (Parteibuch Aggregator was not tested on any php 4.x version. If you want to try that anyway, you have to outcomment the version check in source. Please drop us a line about your experiences)
* your php MUST have multibyte string support (Most php compilations out of the box have it.)

= Is there multilanguage support? =

The fronend is completely configurable. You can configure in templates to show your blog visitors whatever you like. Predefined are language sets for the frontend in German and English. The administration interface is in English.

= Can the Parteibuch Aggregator do this and that or make coffee? =

Probably. The Parteibuch Aggregator is an extremely flexible piece of software based on user defined templates and directed by about hundred parameters. You are free to play around with these. If you got a nice result, drop us a note how you configured it or hacked the source to make it doing this and that or making coffee.

= Is there some more doumentation? =

Try visiting [www.mein-parteibuch.com](http://www.mein-parteibuch.com/ "Mein Parteibuch - A home for nasty cats") and search for `Parteibuch Aggregator`.

= How to completely uninstall the Parteibuch Aggregator? =

By default, when the Parteibuch Aggregator plugin is deactivated, it will not delete any of it's data. 
So when you reactivate the Parteibuch Aggregator plugin later, you didn't loose your configs and aggregated data.

However, there is a way to tell the Parteibuch Aggregator, that it shall on plugin deactivation completely
delete all it's data. If you want the Parteibuch Aggregator to completely delete all it's data, go to the 
"Manage global options" config page in Parteibuch Aggregator admin area, tick the checkbox "Delete all data 
on plugin deactivation?", press the save button and then deactivate the Parteibuch Aggregator plugin. The 
"Delete all data on plugin deactivation?" option will tell the Parteibuch Aggregator on plugin deactivation 
to drop all it's database tables, clear the cache and remove all it's option settings from your wordpress.

== Screenshots ==

1. Screenshot of a fresh Parteibuch Aggregator 0.5 installation on a fresh German Wordpress 2.71 using the default Kubrick theme.
2. Screenshot of a Parteibuch Aggregator 0.5 installation on an English Wordpress 2.21 blog running already for a long time.

== Changelog ==
= Changelog of version 0.5.4 against version 0.5.3 =
1. Changed action variable name in bdp-rssadmin.php to avoid conflict with WP action variable introduced in WP 3.0

== Changelog ==
= Changelog of version 0.5.3 against version 0.5.2 =
1. Bugfix: removed htmlentities to prevent broken umlauts from updateItem() in bdp-rssaggregator-db.php
2. New feature: added snoopy object details to no content errormessage in function parse in bdp-rssfeed.php
3. Bugfix: made item_feed_url varchar 150, item_url varchar 183 in itemtable in bdp-rssaggregator-db.php
	Mysql maxkeylength 1000, utf8: 3 bytes per char. so req is: 3 * length(item_feed_url + item_url) < 1000
4. Bugfix: corrected cnextpolltime condition in if( $_POST['pba_cnewnextpolltime'] != "") ) part in bdp-rssadmin.php
5. Bugfix: made site title, license, description provided by feed binary safe in bdp-rss-aggregator.php
6. Code cleanup: moved some lines from update() to new func process_parsed_feed_item() in bdp-rss-aggregator.php
7. New feature: make it possible to load old wordpress blog entries with pba-loader.php
8. Code cleanup: removed old function dummies from bdp-rss-aggregator.php
9. Bugfix: added check to prevent zero time items in process_parsed_feed_item() in bdp-rss-aggregator.php
10. Code cleanup: removed bogus parameter counter from function updateItem in bdp-rss-aggregator-db.php and call in bdp-rss-aggregator.php
11. New feature: added optional parameter $justupdateitemtext to function updateItem in bdp-rss-aggregator-db.php
12. Code cleanup: completely removed bdprss_db->msitetable in favor of bdprss_db->sitetable from all search functions in pba-rsssearch.php
13. Code cleanup: removed bdprss_db->msitetable usgae from all functions in bdp-rss-aggregator-db.php
14. Database change: ALTER TABLE `wp_pba_sites` ADD `catchtextfromhtml` ENUM( 'Y', 'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'N' COMMENT 'parse site content from html using pba-loader',
ADD `catchhtmlparas` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'array of parameters for html parsing',
ADD `sitecomment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'a comment in the backend';
15. New feature: added HTML parser options and comment field to bdp-rssadmin-sno.php and bdp-rssadmin.php
16. Bugfix: made quotes and smaller, bigger signs in feeds not htmlencoded in function formatitem in pba_output_library.php
17. Bugfix: fixed a-e hex chars to a-f in function title_recode in bdp-rssfeed.php
18. New feature: added parameter max for maxitems to function geturlparameter in pba_output_library.php
19. Code cleanup: removed msitetable from bdp-rssadmin.php and pba-admin-options.php
20. Bugfix: removed deprecated Call-time pass-by-reference in various places in pba_output_function.php

= Changelog of version 0.5.2 against version 0.5.1 =
1. Bugfix: of page rewrite rules to repair broken blog/yyyy/mm/ and xyz/comments/feed/, made cache and search rule tighter in bdp-rss-aggregator.php
2. Bugfix: Set page2hookin to 0 when creating new output as copy from existing in createpbaoutput() in bdp-rssaggregator-db.php
3. New features: added some form fields to change db values of feeds in bdp-rssadmin-sno.php, 
	added processing these form fields to bdp-rssadmin in if( isset($_POST['bdprss_edit_site_button']) ) part
	for feed url change added function update_feedurl to bdp-rssaggregator-db.php
4. New features: made search class config variables to be read as definition from wp-config.php in pba-rsssearch.php
5. Code cleanup: made touch silent when writing to cache in pba_cache() in pba_output_library.php
6. Code cleanup: Cleaned up some comments, made notice from createlist() go away in bdp-rssaggregator-db.php
7. Code cleanup: replaced obsolete functions with placeholders in bdp-rss-aggregator.php, pba_output_function.php
	print_item_set, putsiteheader, output, archiveDate, archiveList, viewCache, rss_print_item_set, feeds_in_ticker, feedlist, process_url_parameter
8. Code cleanup: removed obsolete outputtable variables 
	and its functions get_all_outputs, get_output, deleteoutput in bdp-rssaggregator-db.php, pba_output_library.php
9. Code cleanup: removed obsolete file bdp-rssadmin-output.php and removed it's inclusion from bdp-rssadmin.php
10. Code cleanup: removed obsolete listtable vars in bdp-rssaggregator-db.php
11. Code cleanup: removed old output function calling possibilities in pba_output_function.php
12. Code cleanup: Added one parameter caller style to gettheage() in pba_outputlibrary
	switched all function calls of BDPRSS2::getage() to it in 
	bdp-rss-aggregator.php, bdp-rssadmin-error.php, bdp-rssadmin-edit.php, bdp-rssadmin-general.php
	and removed BDPRSS2::getage() from bdp-rss-aggregator.php
13. Code cleanup: switched constant names BDPRSS2_PRODUCT and BDPRSS2_VERSION to PBA_PRODUCT and PBA_VERSION
	in bdp-rss-aggregator.php, bdp-rssadmin.php, bdp-rssfeed.php, pba_output_library.php
14. Code cleanup: switched constant name BDPRSS2_DIRECTORY to PBA_DIRECTORY
	in bdp-rss-aggregator.php, bdp-rssadmin-general.php, bdp-rssadmin.php, pba_output_library.php
15. Code cleanup: deleted unused sql from getmonthlyarchivedates() in bdp-rssaggregator-db.php
16. Code cleanup: checked pfeed['title'] definition to suppress php notice from line 343 in bdp-rss-aggregator.php
17. Code cleanup: deleted obsolete functions getArchiveList, getItems in bdp-rssaggregator-db.php
18. Code cleanup: renamed pba_widgets.php to pba-widgets.php and made appropriate change in bdp-rss-aggregator.php
19. Code cleanup: renamed pba-output.php, pba-options.php, pba-status.php to pba-admin-xxx.php and made appropriate change in bdp-rssadmin.php

= Changelog of version 0.51 against version 0.5 =
1. Fixed bug with hard coded load threshold from old code in add_heap2search_index() in pba-rsssearch.php
2. Slightly corrected German $defaultparameter['ageunitsstring'] in pba-defaultparameter_de.php
3. switched superparameter processing to be done after server status evaluation in getoutputconfigparameter() in pba_output_library.php
4. no wp_register_sidebar_widget function in wp 2.1, so check this in pba_widgets.php
5. added additional conditions to if $resultparas['specialpage1url'] in makelastpagehref() in pba_output_library.php
6. added some more variables to return with pbaout in outputwrapper() ln 341 ff in pba_output_function.php
7. bugfix of page rewrite rules, rules more tight, changed namespace for pages in ticker from page to tickerpage
	in pba_rewrite() in bdp-rss-aggregator.php, 
	in geturlparameter(), makefeedhref(), makelastpagehref(), makenextpagehref() in pba_output_library.php
8. bugfix: specified utf8 charset and ci collation in all db table creation statements in bdp-rssaggregator-db.php
9. bugfix: reduced keylength of itemtable due to specification above of utf8 in bdp-rssaggregator-db.php
10. switched codeQuotes() to reflect real values in bdp-rss-aggregator.php
11. changed default highloadthreshold to 10 in bdp-rssaggregator-db.php

= Changes from version 0.5 against version 0.2 =
1. Many changes in most db tables, filesystem, output function, template introduction, if you need to migrate, check and migrate your db manually
2. Note: there was out a now obsolete version of 0.5 for a couple of hours, which ran in directory parteibuchaggregator instead of parteibuch-aggregator
