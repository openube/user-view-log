<?php
/**
Plugin Name: User View Log
Plugin URI: https://github.com/ethanpil/user-view-log
Description: Track what posts and pages a logged in user has seen on the frontend. Does not track visitors who are not logged in. Only tracks views of pages and singles.
Version: 1.0
Author: Store Machine Inc.
Author URI: http://storemachine.com
License: GPL
Text Domain: user-view-log
*/


## Setuo the data table on plugin install

function user_view_log_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "user_view_log"; 
   
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		user_id bigint NULL,
		post_id bigint NULL,
		post_type varchar(255) NULL,
		slug varchar(255) NULL,
		time  datetime NULL,
		KEY user_id (user_id),
		KEY post_type (post_type), 
		UNIQUE KEY id (id)
		);";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql ); 
	
}
register_activation_hook( __FILE__, 'user_view_log_install' );


## Create the virtual page to receive JavaScript view and add to db 


add_action( 'init', 'user_view_log_rewrite_setup' );
function user_view_log_rewrite_setup()
{
    add_rewrite_rule( 'track-view.php$', 'index.php?track-view=1', 'top' );
}


add_filter( 'query_vars', 'user_view_log_query_vars' );
function user_view_log_query_vars( $query_vars )
{
    $query_vars[] = 'track-view';
    return $query_vars;
}


add_action( 'parse_request', 'user_view_log_parse_tracking_request' );
function user_view_log_parse_tracking_request( &$wp )
{	
	if ( array_key_exists( 'track-view', $wp->query_vars ) ) {
	//We have recieved a request to the virtual page	
		
		//Add data to DB	
		global $wpdb;		
		$wpdb->query( $wpdb->prepare( 
			"
				INSERT INTO ".$wpdb->prefix."user_view_log
				( user_id, post_id, post_type, slug, time )
				VALUES ( %d, %d, %s, %s, %s )
			", 
			array( 
					$_POST['user_id'], 
					$_POST['post_id'],
					$_POST['post_type'],
					$_POST['slug'],
					date("Y-m-d H:i:s")
				)
		) );
		
		//Exit will prevent theme from being returned
		exit();
	}
}


## Register the JS in page footer which will make the AJAX call to track the view..
### We use JavaScript so that caching plugins will still operate transparently.
function user_view_log_track_view_js() {

	if ( !is_admin() && is_user_logged_in() && (is_single() || is_page() ) && wp_script_is('jquery') ) {
		$current_user = wp_get_current_user();
		
		wp_reset_query();
		global $wp_query;
		
		?>
		<script type="text/javascript">
			$.post("/track-view.php", { user_id: "<?php echo $current_user->ID; ?>", post_id: "<?php echo $wp_query->post->ID; ?>", post_type: "<?php echo $wp_query->post->post_type; ?>", slug: "<?php echo get_the_slug($wp_query->post->ID); ?>"});
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'user_view_log_track_view_js' );


/* Delete duplicates for future version

 DELETE from wp_user_spy where id in 
	( select id from 
		( select id from wp_user_spy a 
			group by user_id, post_id 
			having count(*) > 1 
		) b
	)

*/

//utility function get the slug of a post
function get_the_slug( $id=null ){
  if( empty($id) ):
    global $post;
    if( empty($post) )
      return ''; // No global $post var available.
    $id = $post->ID;
  endif;

  $slug = basename( get_permalink($id) );
  return $slug;
}

//Template tag to see if user has viewed a page/post
# USAGE:  if (user_has_viewed()) { echo "YESSS!!!!"; }
function user_has_viewed($post_id = NULL) {
	
	$result = FALSE;
	if (!is_user_logged_in()) { return FALSE; }
	
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
	
	if (!is_int($post_id)) { 
		global $wp_query;
		$post_id = $wp_query->post->ID;
	}

	if (is_int($user_id) && is_int($post_id) )
	{
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare( 
			"
				SELECT id FROM ".$wpdb->prefix."user_view_log
				WHERE user_id = %d
				AND post_id = %d
			", 
			array( 
					$user_id, 
					$post_id
				)
		) );
	}

	return $result;
	
}

//Template tag to get a list of user ids who viewed a page
# USAGE:  foreach (users_who_viewed() as $user) ...
function users_who_viewed($post_id = NULL) {
	
	$result = FALSE;
		
	if (!is_int($post_id)) { 
		global $wp_query;
		$post_id = $wp_query->post->ID;
	}

	if (is_int($post_id) )
	{
		global $wpdb;
		$result = $wpdb->query( $wpdb->prepare( 
			"
				SELECT user_id FROM ".$wpdb->prefix."user_view_log
				WHERE post_id = %d
				GROUP_BY user_id
			", 
			array( 
					$post_id
				)
		) );
	}

	return $result;
	
}


?>