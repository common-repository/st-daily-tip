=== St-Daily-Tip ===
Contributors: dharashah
Donate link: https://sanskruti.net/daily-tip-plugin-for-wordpress/
Tags: daily tips 
Requires at least: 5.0
Tested up to: 5.8
Stable tag: 4.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple plugin to display different daily tips from a list.
Option to select specific date or day of week to display a tip.
Try it!

== Description ==

This plugin displays daily tip on your page using a shortcode or widget.
If you want to display different Expert tips on any topic on your website, you can upload the tips to this plugin and display different tips daily.

** Features **

1. Use Widget or Short Code to display Tips
2. Add the tips manually, or upload in batch from a CSV file.
3. Add Tips with HTML formatting with HTML Editor
4. Group the Tips and display different Tips on different Locations
5. Mention a Specific date or Specific Day of Week to Display Tip
6. Repeat the tips Yearly on Specific Date
7. The Tips that are not displayed will be displayed first before repeating the Tips. The oldest tip ( that was displayed first) will be displayed if no un-displayed tips are left.
9. Repeat the tips Yearly on Specific Date.
10. Export Tips to a CSV file
11. Translate ready!


== Installation ==

1. Download the Plugin using the Install Plugins 
   OR 
   Upload folder `st-daily-tip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add Tips in  Daily Tips (See How to use in Other Notes)
3. Place [stdailytip] in your page/post where you want to display the daily tip
4. Use attributes : group -> to display only tips that belong to a particular group
                  : date -> show/no show to display or hide date
5. You may also use the Daily Tip Widget to display the tips
6. To Display previously displayed tips use [stdailytiplist]
== How To Use ==
1. Go To **Daily Tips** In Side Menu
2. Create **List of Tips** by :
a. Uploading a CSV file of List of Tips

   The Format of CSV File must be as below :
     *The First line must be headers as it is ignored while uploading.*
     From the second line, the data should begin in following order :
		**Tip Text,Display Date,Display Day,Group Name,Display Yearly.**
         *Tip Text* : The Actual Statement to be displayed.
         *Display Date* : Any Specific Date in format YYYY-MM-DD when you want to display the Tip.
         *Display Day* : Day of week (number format) on which the Tip Should Come. (1 = Sunday ,2 = Monday , 3 = Tuesday, 4 = Wednessday ...7 = Saturday) 
		 *Group Name* : Name Of Group. By Default this is Tip. You can assign a Group to a Tip and can display the tips Group wise.
        **Please Note:Display Day is ignored if Display Date is mentioned.**
b. Adding the tips one by one using the **Enter Manual Data**
	Enter the **Tip Text**
	*You can also enter the text in HTML Format*
	**Display Date (optional) ** in format YYYY-MM-DD
	Select **Display Day (optional)**
	Select **Repeat Yearly? (optional)** to repeat the tip on same date every year
	Select **Group Name** to divide the tips in several Groups
	and press Submit
3.  To Display The tips, you have two ways:
	a. Use Widget . Mention The Group Name of the Tips you want to display.
	b. Use Short Code  [stdailytip group="<group name>"] e.g.[stdailytip group="Tip"]
	c. Developers may also use the PHP Code 
	`<?php
		if (function_exists('add_daily_tip')) {	print add_daily_tip('<group_name>');	}
	?> `
	Replace <group_name> with Tip or any other Group Name you want to display
4.  The Added Tips will be shown in the table Below
5. 	You can also edit or Delete the Tip using the **Edit** and **Delete** button 
6.  If *Display Date* and *Display Day* are not specified, the tip that is not shown yet (or the oldest shown tip) will be displayed
7.  If you have specified a *Display Date* , the tip will be displayed only on that particular date
8. 	If *Display Date* is not specified and *Display Day* is specified, the tip will only be shown on that date
9.  We have not used any styling for Daily Tip. So that it can merge easily in your website.
    To Apply CSS. Use following classes.
	a. tip_container - The main div that contains daily tip, has this class.
	b. tip_text - the div that contains tip_text.
	c. tip_last_shown - div that contains Last Shown Date
	d. tip_title - the div that contains tip_title.
	e. tip_date -  the div that contains date.
10. For Developers.
    Use following code in your template php files
	`<?php
		if (function_exists('add_daily_tip')) {
			print add_daily_tip('<group_name>');
		}
	?>`

== Changelog ==

= 4.8 =
* Security Fixes

= 4.6 =
* Compatible with 5.5.3

= 4.6 =
* Optional Use for UTF Encode

= 4.5 =
* Media buttons added back in Add Tip

= 4.4 =
* Tested with latest Wordpress

= 4.3 =
* Corrected problem in adding UTF Tips

= 4.2 =
* Corrected error in Widget

= 4.1 =
* Limit on Tips displayed in [stdailytiplist]

= 4.0 =
* Added attribute date_title

= 3.8 =
* Correcting Error in Widget

= 3.8 =
* Correcting Name Error

= 3.7 =
* Allow to set Default Text for no tip

= 3.6 =
* Correction for WP_List_Table 
= 3.5 =
*
= 3.4 =
* Add Media To Tips
* Filters Added to Easily search the tips
* The Form is not cleared after save. 

= 3.3 =
* HTML Editor (WP Editor) for adding Tips

= 3.2 =
* Now add Shortcode in Tip Text.e.g. [dropcap]

= 3.1 =
* Export CSV Bug Fix

= 2.9 =
* Minor Bug Fixes

= 2.8 =
* Allowed upload of Unicode Characters through CSV

= 2.7 =
* Bug Fixes

= 2.6 =
* Use of Wordpress Upload Folder for CSV Upload
* Translation in Serbo-Croatian with support from Borisa Djuraskovic

= 2.5 =
* Bug Fix in File Upload
* Bug Fix in Widget

= 2.4 =
* Attribute to suppress title
* Solved bug of addition of slash while CSV upload

= 2.3 =
* Made Translate ready

= 2.2 =
* Compatible with Wordpress 3.9

= 2.1 =
* Bug Fix : Edit Problem

= 2.0 =
* Bug Fix : Display Yearly Problem

= 1.9 =
* Now add tip titles in HTML Format

= 1.8 =

= 1.7 = 
* Added provision to export tips in CSV format

= 1.6 = 
* Shortcode added to display all previously displayed tips
* Added Tip Title
* Added classes tip_title, tip_text, single_tip for easy styling

= 1.5 = 
* Support added for UTF Characters in Tip text

= 1.4 = 
* Short Code problem solved 

= 1.3 = 
* PHP Code problem solved 

= 1.2 =
* Date Bug Fixed according to suggestion from xenoalien

= 1.1 =
* Minor Bug Fixing

= 1.0 =
* Use of DataTables to display Tips
* Provision to Search Tips

= 0.9 =
* Removed Few Bugs
* Added Little Formatting to Admin Panel

= 0.8 =
* Solved Problem of Blank Group Name in CSV

= 0.7 =
* Bug removal - Update Strings with quotes.

= 0.6 =
* Create Tips Group Wise. Display Different Tips in different Groups.

= 0.5 =
* Repeat the tips Yearly on Same Date
* Add tips with HTML Formatting
* Delete Tips in Bulk
* See Daily Tips in Side Panel

= 0.4 =
* Widget Added
* Pagination added for Tips

= 0.2 =
* First Deployed Version
* Provision to Upload a CSV File
* Provision to Enter Manual Data


== Upgrade Notice ==
= 4.3 =
* Corrected problem in adding UTF Tips

= 4.2 =
* Corrected error in Widget

= 4.1 =
* Limit the number of tips shown in Tips List

= 4.0 =
* Added attribute date_title to show / hide the date title

= 3.8 =
* Correcting Error in Widget

= 3.4 =
* Add Media To Tips
* Filters Added to Easily search the tips
* The Form is not cleared after save. 

= 3.3 =
* HTML Editor (WP Editor) for adding Tips

= 3.2 =
* Now add Shortcode in Tip Text.e.g. [dropcap]

= 2.9 =
* Minor Bug Fixes

= 2.8 =
* Allowed upload of Unicode Characters through CSV

= 2.7 =
* Bug Fixes

= 2.6 =
* Use of Wordpress Upload Folder for CSV Upload
* Translation in Serbo-Croatian with support from Borisa Djuraskovic

= 2.5 =
* Bug Fix in File Upload
* Bug Fix in Widget

= 2.4 =
* Attribute to suppress title
* Solved bug of addition of slash while CSV upload

= 2.3 =
* Now transalte ready

= 1.0 =
* Use of DataTables to display Tips

= 0.8 =
* Solved Problem of Blank Group Name in CSV

= 0.7 =
* Bug removal - Update Strings with quotes.

= 0.6 =
* Repeat the tips Yearly on Same Date

= 0.5 =
* Repeat the tips Yearly on Same Date
* Add tips with HTML Formatting
* Delete Tips in Bulk
* See Daily Tips in Side Panel

= 0.4 =
* Daily Tip Widget 
* Tips will now be shown page wise. With 15 tips on each Page

= 0.2 =
* First Deployed version









