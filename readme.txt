=== rel-syndication for wordpress ===
Contributors: jihaisse, cadeyrn
Donate link:
Tags: syndication, indieweb, indiewebcamp, POSSE
Requires at least: 3.3
Tested up to: 3.9.1
Stable tag: 0.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

rel-syndication is a way to discoverably link from your original blog post permalinks to syndicated copies of it on other sites

== Description ==

Add a list of syndicated copies (POSSEd) at the end of the post.
In order to POSSE you will need another plugin, like Social (http://wordpress.org/plugins/social/).

== Installation ==

1. Upload the folder `wordpress-syndication` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently asked questions ==

= Why ? =

An original post should hyperlink to syndicated copies (e.g. per POSSE) with a rel value of syndication.

Additionally the microformats2 class name u-syndication should also be placed on such hyperlinks from original posts (inside their h-entry markup/object) to their syndicated copies.

see http://indiewebcamp.com/rel-syndication

= Can I disable automatic syndicated links insertion? =

Add
`define ('WORDPRESS_SYNDICATION_NOAUTO', true);` to wp-config.php
to disable automatic link insertion and use :

`<?php if ( function_exists('add_js_rel_syndication')) echo add_js_rel_syndication(''); ?>`
to place it anywhere in your theme.

== Changelog ==

= 0.2.1 =
*2014-05-23*

* DeviantArt, FriendFeed & LinkedIn support for Social Networks Auto Poster {SNAP}
* call from theme hack

= 0.2 =
*2014-05-16*

* partial ( Facebook, Twitter & Tumblr only ) support for Social Networks Auto Poster {SNAP}

= Supported plugins =

* Social plugin is fully supported (http://wordpress.org/plugins/social/)
* partial ( see changelog ) support for Social Networks Auto Poster {SNAP}
