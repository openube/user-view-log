=== User View Log ===
Contributors: ethanpil
Tags: user tracking, analytics, logging, user tracker, visitor log
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track what posts and pages a logged in user has seen on the frontend. Does not track visitors who are not logged in. Only tracks views of pages and singles.

== Description ==

For advanced developers.

Once the plugin is activated, it will track logged in users who view a single post or page. The plugin will not track views of archives or categories. 

This was created for a member based site which wanted to reward users who have read specific pages.


This plugin also makes two new template tags available:

-

    user_has_viewed($post_id)
	
Checks if the current logged in user has seen $post_id.
If no $post_id is provided it will default to the current ID from the loop.
Returns FALSE if user is not logged in or has not seen this page/post.

-

    users_who_viewed($post_id)
	
Returns an array of user ids that have seen the $post_id or FALSE
If no $post_id is provided it will default to the current ID from the loop.

-

You can also query the table directly in your templates.
    
	We have 5 columns in the table: user_id, post_id, post_type, slug, time
	$wpdb->query( $wpdb->prepare("SELECT user_id FROM ".$wpdb->prefix."user_view_log WHERE slug = ....

-
	
Fork away: https://github.com/ethanpil/user-view-log


== Installation ==

1. Upload `user-view-log.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Install this plugin into any WordPress system and it will create a new table called "user_view_log" in the database upon activation.

== Frequently Asked Questions ==

= I dont see any settings or options! =

There arent any!

= How to I enable it? =

If the plugins system has it on then its working.

= What happens if the table gets too large

The next version of the plugin will include an auto-prune feature which will run in wp-cron and periodically remove duplicate visits. (Only the most recent visit will remain).

== Screenshots ==

There is nothing to see here.

== Changelog ==

= 1.0 =
* Hello World!