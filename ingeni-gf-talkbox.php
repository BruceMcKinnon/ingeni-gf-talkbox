<?php
/*
Plugin Name: Gravity Forms to Talkbox Feed Add-On
Version: 2022.02
Plugin URI: http://ingeni.net
Author: Bruce McKinnon - ingeni.net
Author URI: http://ingeni.net
Description: Send name, email and phone submitted from a Gravity Form to be added to TalkBox Contacts list.

License: GPL v3

Ingeni Gravity Forms - Talkbox
Copyright (C) 2022, Bruce McKinnon

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//
// v2022.01 - Initial release
// v2022.02 - Added a little extra curl debugging
//			- Added protocol scheme for the default TalkBox URL
//

define( 'GF_TALKBOX_FEED_ADDON_VERSION', '2022.02' );

define( 'INGENI_DEFAULT_TALKBOX_FEED_URL', 'https://talkbox.impactapp.com.au/service/v1');


add_action( 'gform_loaded', array( 'GF_TB_Feed_AddOn_Bootstrap', 'load' ), 5 );
class GF_TB_Feed_AddOn_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-talkbox-feed-addon.php' );

		GFAddOn::register( 'GFTBFeedAddOn' );
	}

}

function gf_tb_feed_addon() {
	return GFTBFeedAddOn::get_instance();
}

?>