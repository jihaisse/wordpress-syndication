<?php
/*
Plugin Name: rel-syndication for wordpress
Plugin URI: https://github.com/jihaisse/wordpress-syndication
Text Domain: rel-syndication
Description:
Author: Jean-Sébastien Mansart
Author URI: http://jihais.se
Version: 0.3
License: GPL2++
Contributors: Peter Molnar, Ryan Barrett
*/

// function to add markup at the end of the post
add_filter( 'the_content', 'add_js_rel_syndication', 20);
function add_js_rel_syndication($content) {
	load_plugin_textdomain( 'rel-syndication', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	$see_on = "";

	if (class_exists("Social")){
		$see_on = getRelSyndicationFromSocial();
	}
	elseif ( defined ( 'NextScripts_SNAP_Version' ) ) {
		$see_on = getRelSyndicationFromSNAP();
	}
	else {
		$see_on = getRelSyndicationFromBridgyPublish();
	}

	if ($see_on !== ""){
		$content = $content . '<div class="u-syndication">' . __("Also on:", "rel-syndication") . $see_on . "</div>";
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

function getRelSyndicationFromSNAP() {
	global $nxs_snapAvNts;
	global $post;

	$snap_options = get_option('NS_SNAutoPoster');
	$urlmap = array (
		'AP' => array(),
		'BG' => array(),
		'DA' => array(),
		'DI' => array(),
		'DL' => array(),
		'FB' => array( 'url' => '%BASE%/posts/%pgID%' ),
		'FF' => array(),
		'FL' => array(),
		'FP' => array(),
		'GP' => array(),
		'IP' => array(),
		'LI' => array(),
		'LJ' => array(),
		'PK' => array(),
		'PN' => array(),
		'SC' => array(),
		'ST' => array(),
		'SU' => array(),
		'TR' => array( 'url'=>'%BASE%/post/%pgID%' ),
		'TW' => array( 'url'=>'%BASE%/status/%pgID%' ),
		'VB' => array(),
		'VK' => array(),
		'WP' => array(),
		'YT' => array(),
	);

	foreach ( $nxs_snapAvNts as $key => $serv ) {
		/* all SNAP entries are in separate meta entries for the post based on the service name's "code" */
		$mkey = 'snap'. $serv['code'];
		$urlkey = $serv['lcode'].'URL';
		$okey = $serv['lcode'];
		$metas = maybe_unserialize(get_post_meta(get_the_ID(), $mkey, true ));
		if ( !empty( $metas ) && is_array ( $metas ) ) {
			foreach ( $metas as $cntr => $m ) {
				$url = false;

				if ( isset ( $m['isPosted'] ) && $m['isPosted'] == 1 ) {
					/* this should be available for some services, for example Tumblr,
					 * but buggy and misses slashes so URL ends up invalid
					if ( isset( $m['postURL'] ) && !empty( $m['postURL'] ) ) {
						$url = $m['postURL'];
					}
					else {
					*/
						$base = (isset( $urlmap[ $serv['code'] ]['url'])) ? $urlmap[ $serv['code'] ]['url'] : false;

						if ( $base != false ) {
							/* Facebook exception, why not */
							if ( $serv['code'] == 'FB' ) {
								$pos = strpos( $m['pgID'],'_' );
								$pgID = ( $pos == false ) ? $m['pgID'] : substr( $m['pgID'], $pos + 1 );
							}
							else {
								$pgID = $m['pgID'];
							}

							$o = $snap_options[ $okey ][$cntr];
							$search = array('%BASE%', '%pgID%' );
							$replace = array ( $o[ $urlkey ], $pgID );
							$url = str_replace ( $search, $replace, $base );
						}
					/* } */

					if ( $url != false ) {
						$url = preg_replace('~(^|[^:])//+~', '\\1/', $url);
						$classname = sanitize_title ( $serv['name'], $serv['lcode'] );
						$broadcasts[] = '<li><a class="u-syndication link-'. $classname .' icon-'. $classname .'" rel="syndication" href="'. $url .'" target="_blank">'. $serv['name'] .'</a></li>';
					}
				}
			}
		}
	}

	if (count($broadcasts)) {
		$see_on_social = '<ul>'.implode("\n", $broadcasts).'</ul>';
	}

	return $see_on_social;
}

function getRelSyndicationFromBridgyPublish() {
	$broadcasts = array();
	foreach (get_post_custom_values('bridgy_publish_syndication_urls', get_the_ID()) as $key => $link) {
		array_push($broadcasts, '<li>' . $link . '</li>');
	}
	if ($broadcasts) {
		return '<ul>' . implode("\n", $broadcasts) . '</ul>';
	} else {
		return '';
	}
}

function store_bridgy_publish_link($response, $source, $target, $post_ID) {
	if (!$post_ID) {
		return;
	}

	$json = json_decode(wp_remote_retrieve_body($response));
	if (!is_wp_error($response) && $json && $json->url &&
		preg_match('~https?://(?:www\.)?(brid.gy|localhost:8080)/publish/(.*)~', $target, $matches)) {
		$link = '<a href="' . $json->url . '">' . ucfirst($matches[2]) . '</a>';
		$existing = get_post_custom_values('bridgy_publish_syndication_urls', $post_ID);
		if (array_search($link, get_post_custom_values('bridgy_publish_syndication_urls', $post_ID)) == false) {
			add_post_meta($post_ID, 'bridgy_publish_syndication_urls', $link);
		}
	}
}
add_action('webmention_post_send', 'store_bridgy_publish_link', 10, 4);

?>
