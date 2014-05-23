<?php
/*
Plugin Name: rel-syndication for wordpress
Plugin URI: https://github.com/jihaisse/wordpress-syndication
Text Domain: rel-syndication
Description:
Author: Jean-Sébastien Mansart
Author URI: http://jihais.se
Version: 0.2.1
License: GPL2++
Contributors: Peter Molnar
*/

// function to add markup at the end of the post, only if
if ( !( defined ( 'WORDPRESS_SYNDICATION_NOAUTO' ) && WORDPRESS_SYNDICATION_NOAUTO == true  ) ) {
	add_filter( 'the_content', 'add_js_rel_syndication', 20);
}

function add_js_rel_syndication($content) {
	load_plugin_textdomain( 'rel-syndication', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	$see_on = "";

	if (class_exists("Social")){
		$see_on = getRelSyndicationFromSocial();
	}
	elseif ( defined ( 'NextScripts_SNAP_Version' ) ) {
		$see_on = getRelSyndicationFromSNAP();
	}

	if ( !empty($see_on) ){
		$content = $content . '<nav class="usyndication"><h6>' . __("Also on:", "rel-syndication") . '</h6>' . $see_on . "</nav>";
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

	$see_on_social = "";
	$broadcasts = null;

	$snap_options = get_option('NS_SNAutoPoster');
	$urlmap = array (
		'AP' => array(),
		'BG' => array(),
		// 'DA' => array(), /* DeviantArt will use postURL */
		'DI' => array(),
		'DL' => array(),
		'FB' => array( 'url' => '%BASE%/posts/%pgID%' ),
		//'FF' => array(), /* FriendFeed should be using postURL */
		'FL' => array(),
		'FP' => array(),
		'GP' => array(),
		'IP' => array(),
		'LI' => array( 'url' => '%pgID%' ),
		'LJ' => array(),
		'PK' => array(),
		'PN' => array(),
		'SC' => array(),
		'ST' => array(),
		'SU' => array(),
		'TR' => array( 'url'=>'%BASE%/post/%pgID%' ), /* even if Tumblr has postURL set as well, it's buggy and missing a */
		'TW' => array( 'url'=>'%BASE%/status/%pgID%' ),
		'VB' => array(),
		'VK' => array(),
		'WP' => array(),
		'YT' => array(),
	);

	/* all SNAP entries are in separate meta entries for the post based on the service name's "code" */
	foreach ( $nxs_snapAvNts as $key => $serv ) {
		$mkey = 'snap'. $serv['code'];
		$urlkey = $serv['lcode'].'URL';
		$okey = $serv['lcode'];
		$metas = maybe_unserialize(get_post_meta(get_the_ID(), $mkey, true ));
		if ( !empty( $metas ) && is_array ( $metas ) ) {
			foreach ( $metas as $cntr => $m ) {
				$url = false;

				if ( isset ( $m['isPosted'] ) && $m['isPosted'] == 1 ) {
					/* postURL entry will only be used if there's no urlmap set for the service above
					 * this is due to either missing postURL values or buggy entries */
					if ( isset( $m['postURL'] ) && !empty( $m['postURL'] ) && !isset( $urlmap[ $serv['code'] ] ) ) {
						$url = $m['postURL'];
					}
					else {
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
					}

					if ( $url != false ) {
						/* trim all the double slashes, some sites cannot coope with them */
						$url = preg_replace('~(^|[^:])//+~', '\\1/', $url);
						$classname = sanitize_title ( $serv['name'], $serv['lcode'] );
						$broadcasts[] = '<li><a class="u-syndication link-'. $classname .' icon-'. $classname .'" rel="syndication" href="'. $url .'" target="_blank">'. $serv['name'] .'</a></li>';
					}
				}
			}
		}
	}

	if (count($broadcasts) != 0 ) {
		$see_on_social = '<ul>'.implode("\n", $broadcasts).'</ul>';
	}

	return $see_on_social;
}
?>
