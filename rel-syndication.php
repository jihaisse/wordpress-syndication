<?php
/*
Plugin Name: rel-syndication for wordpress
Plugin URI: https://github.com/jihaisse/wordpress-syndication
Text Domain: rel-syndication
Description: 
Author: Jean-Sébastien Mansart
Author URI: http://jihais.se
Version: 0.1
License: GPL2++
*/

// function to add markup at the end of the post
add_filter( 'the_content', 'add_js_rel_syndication', 20);
function add_js_rel_syndication($content) {
	load_plugin_textdomain( 'rel-syndication', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	$see_on = "";
	
	if (class_exists("Social")){
		$see_on = getRelSyndicationFromSocial();
	}
	if ($see_on !== ""){
		$content = $content . '<div class="usyndication">' . __("Also on:", "rel-syndication") . $see_on . "</div>";
	}
	// Returns the content.
   	return $content;
}

function getRelSyndicationFromSocial() {
	$Social = new Social();
	$ids = get_post_meta( get_the_ID(), "_social_broadcasted_ids", true );
	$services = $Social->instance()->services();
	$broadcasts = array();
	$see_on_social = "";
	
	if (is_array($ids) and count($ids)) {
		foreach ($services as $key => $service) {
			if (isset($ids[$key]) and count($ids[$key])) {
				$broadcasted = true;
				foreach ($ids[$key] as $user_id => $broadcasted) {
					$account = $service->account($user_id);
					if (empty($output)) {
						$accounts_output = '<ul class="social-broadcasted">';
					}

					foreach ($broadcasted as $broadcasted_id => $data) {
						if ($account === false) {
							$class = 'Social_Service_'.$key.'_Account';
							$account = new $class($data['account']);

							if (!$account->has_user() and $key == 'twitter') {
								$recovered = $service->recover_broadcasted_tweet_data($broadcasted_id, $post->ID);

								if (isset($recovered->user)) {
									$data['account']->user = $recovered->user;
									$account = new $class($data['account']);
								}
							}
						}

						$broadcasted = esc_html($service->title());
						if (isset($broadcasted_id)) {
							if ($account->has_user() or $service->key() != 'twitter') {
								$url = $service->status_url($account->username(), $broadcasted_id);
								if (!empty($url)) {
									$broadcasted = '<a class="u-syndication" rel="syndication" href="'.esc_url($url).'" target="_blank">'.esc_html($service->title()).'</a>';
								}
							}
						}
						array_push($broadcasts,"<li>".$broadcasted."</li>");
					}
				}

				if (count($broadcasts)) {
					$see_on_social = '<ul>'.implode("\n", $broadcasts).'</ul>';
				}
			}
		}
	}
	return $see_on_social;
}
?>