<?php
/*
Plugin Name: rel-syndication for wordpress
Plugin URI: https://github.com/jihaisse/wordpress-syndication
Description: 
Author: Jean-Sébastien Mansart
Author URI: http://jihais.se
Version: 0.1.2
License: GPL2++
*/

// Plugin uninstall: delete option
register_uninstall_hook( __FILE__, 'rel_syndication_uninstall' );
function rel_syndication_uninstall() {
	delete_option( 'rel_syndication' );
}

// function to add markup at the end of the post

add_filter( 'the_content', 'add_js_rel_syndication', 20);
function add_js_rel_syndication($content) {
	$content = $content."
	<ul>
		<li>
			<a class=\"u-syndication\" rel=\"syndication\" href=\"https://twitter.com/aaronpk/status/391335890179469312\">View on Twitter</a>
		</li>
	</ul>";
	
	$meta_values = get_post_meta( get_the_ID(), "_social_broadcasted_ids", true );
	
	$content =  $content.print_r($meta_values['twitter']);
	
	// Returns the content.
   	return $content;
}
?>