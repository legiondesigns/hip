<?php
/**
 * @package Etsy-Shop
 */
/*
Plugin Name: Etsy Shop
Plugin URI: http://wordpress.org/extend/plugins/etsy-shop/
Description: Inserts Etsy products in page or post using shortcode method.
Author: Frédéric Sheedy
Text Domain: etsy-shop
Version: 3.0.5
*/

/*
 * Copyright 2011-2023  Frédéric Sheedy
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Roadmap
 * TODO: allow more than 100 items
 * TODO: customize currency
 * TODO: get Etsy translations
 * TODO: Add MCE Button / block
 */

define( 'ETSY_SHOP_VERSION',  '3.0.5' );
define( 'ETSY_SHOP_CACHE_PREFIX', 'etsy_shop_cache_' );

// plugin activation
register_activation_hook( __FILE__, 'etsy_shop_activate' );

// check for update
add_action( 'plugins_loaded', 'etsy_shop_update' );

// add Settings link
add_filter( 'plugin_action_links', 'etsy_shop_plugin_action_links', 10, 2 );


function etsy_shop_update() {
    $etsy_shop_version = get_option( 'etsy_shop_version' );
    if ( $etsy_shop_version != ETSY_SHOP_VERSION ) {

        // upgrade logic here

        // initialize timeout option if not already there
        if( !get_option( 'etsy_shop_timeout' ) ) {
            add_option( 'etsy_shop_timeout', '10' );
        }

        // initialize cache life option if not already there
        if( !get_option( 'etsy_shop_cache_life' ) ) {
            add_option( 'etsy_shop_cache_life', '21600' ); // 6 hours in seconds
        }

        // v3.0 - Remove debug option
        if( get_option( 'etsy_shop_debug_mode' ) ) {
            //delete_option( 'etsy_shop_debug_mode' );
        }

        // update the version value
        update_option( 'etsy_shop_version', ETSY_SHOP_VERSION );
    }

}

function etsy_shop_activate() {
    etsy_shop_update();
}

function etsy_shop_process() {
    if ( func_num_args() === 1) {
        $attributes         = func_get_arg(0);
        $shop_id            = wp_strip_all_tags( $attributes['shop_name'] );
        $section_id         = wp_strip_all_tags( $attributes['section_id'] );
        $listing_id         = wp_strip_all_tags( $attributes['listing_id'] );
        $show_available_tag = ( !$attributes['show_available_tag'] ? 'true' : wp_strip_all_tags( $attributes['show_available_tag'] ) );
        $language           = ( !$attributes['language'] ? null : wp_strip_all_tags( $attributes['language']) );
        $columns            = ( !$attributes['columns'] ? 3 : (int) wp_strip_all_tags( $attributes['columns'] ) );
        $thumb_size         = ( !$attributes['thumb_size'] ? "medium" : wp_strip_all_tags( $attributes['thumb_size'] ) );
        $width              = ( !$attributes['width'] ? "172px" : wp_strip_all_tags( $attributes['width'] ) );
        $height             = ( !$attributes['height'] ? "135px" : wp_strip_all_tags( $attributes['height'] ) );
        $limit              = ( !$attributes['limit'] ? 100 : (int) $attributes['limit'] );
        $offset             = ( !$attributes['offset'] ? 0 : (int) $attributes['offset'] );
    } else {
        return __( 'Etsy Shop: invalid number of arguments', 'etsy-shop' );
    }

    // Filter the values
    $shop_id    = preg_replace( '/[^a-zA-Z0-9,]/', '', $shop_id );
    $section_id = preg_replace( '/[^a-zA-Z0-9,]/', '', $section_id );
    $listing_id = preg_replace( '/[^a-zA-Z0-9,]/', '', $listing_id );
    $width      = esc_attr($width);
    $height     = esc_attr($height);

    //Filter the thumb size
    switch ( $thumb_size ) {
        case ( "small" ):
            $thumb_size = "url_75x75";
            break;
        case ( "medium" ):
            $thumb_size = "url_170x135";
            break;
        case ( "large" ):
            $thumb_size = "url_570xN";
            break;
        case ( "original" ):
            $thumb_size = "url_fullxfull";
            break;
        default:
            $thumb_size = "url_570xN";
            break;
    }

    // Filter Language
    if ( strlen( $language ) != 2 ) {
        $language = null;
    }

    if ( $shop_id != '' && $section_id != '' ) {
        // generate listing for shop section
        $listings_array = etsy_shop_getShopSectionListings( $shop_id, $section_id, $limit, $offset );
        if ( is_array( $listings_array ) ) {
            $listings_type  = $listings_array[0];
            $listings       = $listings_array[1];
        } else {
            $listings = $listings_array;
        }

            if ( !is_wp_error( $listings ) ) {
                $data = "<!-- etsy_shop_cache $listings_type -->";

                $data .= '<div class="etsy-shop-listing-container">';
                $n = 0;

                // verify if we use target blank
                if ( get_option( 'etsy_shop_target_blank' ) ) {
                    $target = '_blank';
                } else {
                    $target = '_self';
                }

                foreach ( $listings->results as $result ) {

                    if ( !empty($listing_id) && $result->listing_id != $listing_id ) {
                        continue;
                    }

                    if ($language && isset( $result->translations->$language->title ) ) {
                        $title = $result->translations->$language->title;
                    } else {
                        $title = $result->title;
                    }

                    if ($result->price->divisor > 0) {
                        $price = $result->price->amount/$result->price->divisor;
                    } else {
                        $price = $result->price->amount;
                    }

                    $listing_html = etsy_shop_generateListing(
                        $result->listing_id,
                        $title,
                        $result->state,
                        $price,
                        $result->price->currency_code,
                        $result->quantity,
                        $result->url,
                        $result->images[0]->$thumb_size,
                        $target,
                        $show_available_tag,
                        $width,
                        $height
                    );
                    if ( $listing_html !== false ) {
                        $data = $data.'<div class="etsy-shop-listing">'.$listing_html.'</div>';
                    }
                }
                $data = $data.'</div>';
            } else {
                $data = $listings->get_error_message();
            }
    } else {
        // must have 2 arguments
        $data = __( 'Etsy Shop: empty arguments', 'etsy-shop' );
    }

    return $data;
}

// Process shortcode
function etsy_shop_shortcode( $atts ) {
    // if API Key exist
    if ( get_option( 'etsy_shop_api_key' ) ) {
        $attributes = shortcode_atts( array(
            'shop_name'          => null,
            'section_id'         => null,
            'listing_id'         => null,
            'thumb_size'         => null,
            'language'           => null,
            'columns'            => null,
            'limit'              => null,
            'offset'             => null,
            'show_available_tag' => true,
            'width'              => "172px",
            'height'             => "135px"
        ), $atts );

        $content = etsy_shop_process( $attributes );
        return $content;
    } else {
        // no API Key set
        return __( 'Etsy Shop: Shortcode detected but API KEY is not set.', 'etsy-shop' );
    }
}
add_shortcode( 'etsy-shop', 'etsy_shop_shortcode' );

function etsy_shop_getShopSectionListings( $etsy_shop_name, $etsy_section_id, $limit, $offset ) {
    $name = ETSY_SHOP_CACHE_PREFIX.$etsy_shop_name.'-c'.$etsy_section_id.'-'.$limit.'-'.$offset;
    $etsy_cached_content = get_transient( $name );
    $cache_type = 'none';

    if ( false === $etsy_cached_content ) {
        $etsy_shop_id = etsy_shop_getShopId( $etsy_shop_name );
        if ( !is_wp_error( $etsy_shop_id ) ) {
            $reponse = etsy_shop_api_request( "shops/$etsy_shop_id/shop-sections/listings", "shop_section_ids=$etsy_section_id" . '&limit='.$limit.'&offset='.$offset );
            $listing_list = etsy_shop_generateListingList( $reponse );
            $reponse = etsy_shop_api_request( "listings/batch", "includes=images,translations&listing_ids=$listing_list" );
        } else {
            // return WP_Error
            $reponse = $etsy_shop_id;
        }

        if ( !is_wp_error( $reponse ) ) {
            // if request OK
            set_transient( $name, $reponse, get_option( 'etsy_shop_cache_life' ) );
        } else {
            // return WP_Error
            return $reponse;
        }
    } else {
        // return cached content
        $cache_type = $name;
        $reponse = $etsy_cached_content;
    }

    $data = json_decode( $reponse );
    $final = array ($cache_type, $data);
    return $final;
}

function etsy_shop_getShopSection( $etsy_shop_name, $etsy_section_id ) {
    $etsy_shop_id = etsy_shop_getShopId( $etsy_shop_name );
    $reponse = etsy_shop_api_request( "shops/$etsy_shop_id/sections/$etsy_section_id", NULL , 1 );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_testAPIKey() {
    $reponse = etsy_shop_api_request( 'openapi-ping' );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_generateShopSectionList($etsy_shop_name) {
    $list = etsy_shop_getShopSectionList($etsy_shop_name);
    if ( !is_wp_error( $list ) ) {
        $data = '';
        foreach ( $list->results as $result ) {
            $data .= '<td>'.$result->title.'</td>';
            $data .= '<td>'.$result->shop_section_id.'</td>';
            $data .= '<td>'.$result->active_listing_count.'</td>';
             $data .= '<td>[etsy-shop shop_name="'.get_option( 'etsy_shop_quickstart_shop_id' ).'" section_id="'.$result->shop_section_id.'"]</td></tr>';

        }

    } else {
        $data = 'ERROR: '.$list->get_error_message();
    }

    return $data;
}

function etsy_shop_getShopSectionList($etsy_shop_name) {
    $etsy_shop_id = etsy_shop_getShopId( $etsy_shop_name );
    if ( !is_wp_error( $etsy_shop_id ) ) {
        $reponse = etsy_shop_api_request( "/shops/$etsy_shop_id/sections" );
    } else {
        return $etsy_shop_id;
    }

    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
    } else {
        // return WP_Error
        return $reponse;
    }

    return $data;
}

function etsy_shop_generateListingList($response) {
    $data = json_decode( $response );
    $array = array();
    foreach ( $data->results as $result ) {
        $array[] = $result->listing_id;
    }

    $list = implode('%2C', $array);
    return $list;
}

function etsy_shop_getShopId($etsy_shop_name) {
    $reponse = etsy_shop_api_request( "shops", 'shop_name='.$etsy_shop_name );
    if ( !is_wp_error( $reponse ) ) {
        $data = json_decode( $reponse );
        if ( !isset( $data->results[0]->shop_id ) ) {
            return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Your shop ID is invalid.', 'etsy-shop' ) );
        }
        $shop_id = $data->results[0]->shop_id;
    } else {
        // return WP_Error
        return $reponse;
    }

    return $shop_id;
}

function etsy_shop_api_request( $etsy_request, $query_args = null ) {
    $etsy_api_key = get_option( 'etsy_shop_api_key' );
    $url = "https://api.etsy.com/v3/application/$etsy_request";
    $headers = array( 'User-Agent' => 'wordpress_etsy_shop_plugin_'.ETSY_SHOP_VERSION, 'x-api-key' => $etsy_api_key );

    if ( $query_args ) {
        $url = $url . '?' . $query_args;
    }

    $wp_request_args = array( 'timeout' => get_option( 'etsy_shop_timeout' ), 'headers' => $headers );
    $request = wp_remote_request( $url , $wp_request_args );

    if ( !is_wp_error( $request ) ) {
        if ( $request['response']['code'] == 200 ) {
            $request_body = $request['body'];
        } else {
            $json = json_decode( $request['body'], true );
            $error = __( 'Not specified', 'etsy-shop' );
            if ( null != $json && is_array( $json ) && array_key_exists( 'error', $json ) ) {
                $error = $json['error'];
            }
            if ( $request['headers']['x-error-detail'] ==  'Not all requested shop sections exist.' ) {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Your section ID is invalid.', 'etsy-shop' ) );
            } elseif ( $request['response']['code'] == 0 )  {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: The plugin timed out waiting for etsy.com reponse. Please change Time out value in the Etsy Shop Options page.', 'etsy-shop' ) );
            } elseif ( $error === 'Invalid API key' ) {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Invalid API Key, make sure your API Key is approved.', 'etsy-shop' ) );
            } else {
                return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: API reponse should be HTTP 200 <br>API Error Description:', 'etsy-shop' ) . ' ' . $error );
            }
        }
    } else {
        return  new WP_Error( 'etsy-shop', __( 'Etsy Shop: Error on API Request', 'etsy-shop' ) );
    }

    return $request_body;
}

function etsy_shop_generateListing($listing_id, $title, $state, $price, $currency_code, $quantity, $url, $imgurl, $target, $show_available_tag, $width = "172px", $height = "135px") {
    // if the Shop Item is active
    if ( $state == 'active' ) {

        if ( $show_available_tag === 'true' ) {
            $state = __( 'Available', 'etsy-shop' );
        } else {
            $state = '&nbsp;';
        }

        // Determine Currency Symbol
        if ( $currency_code == 'EUR' ) {
            // Euro sign
            $currency_symbol = '&#8364;';
        } else if ( $currency_code == 'GBP' ) {
            // Pound sign
            $currency_symbol = '&#163;';
        } else if ( $currency_code == 'DKK' or $currency_code == 'NOK' ) {
            // Nothing for now
            $currency_symbol = '';
        } else {
            // Dollar Sign
            $currency_symbol = '&#36;';
        }

        $script_tags =  '
            <div class="etsy-shop-listing-card" id="' . $listing_id . '" style="width:' . $width . '">
                <a title="' . $title . '" href="' . $url . '" target="' . $target . '" class="etsy-shop-listing-thumb">
                    <img alt="' . $title . '" src="' . $imgurl . '" style="height:' . $height . ';">

                <div class="etsy-shop-listing-detail">
                    <p class="etsy-shop-listing-title">'.$title.'</p>
                    <p class="etsy-shop-listing-maker">'.$state.'</p>
                </div>
                <p class="etsy-shop-listing-price">'.$currency_symbol.$price.' <span class="etsy-shop-currency-code">'.$currency_code.'</span></p>
                </a>
            </div>';

        return $script_tags;
    } else {
        return false;
    }
}

// Custom CSS

add_action( 'wp_print_styles', 'etsy_shop_css' );

function etsy_shop_css() {
    $link = plugins_url( 'etsy-shop.css', __FILE__ );
    wp_register_style( 'etsy_shop_style', $link, null, ETSY_SHOP_VERSION );
    wp_enqueue_style( 'etsy_shop_style' );
}


// Options Menu
add_action( 'admin_menu', 'etsy_shop_menu' );

function etsy_shop_menu() {
    add_options_page( __( 'Etsy Shop Options', 'etsy-shop' ), __( 'Etsy Shop', 'etsy-shop' ), 'manage_options', basename( __FILE__ ), 'etsy_shop_options_page' );
}

function etsy_shop_enqueue( $hook ) {
    if ( 'settings_page_etsy-shop' !== $hook ) {
        return;
    }
    wp_enqueue_script(
        'ajax-script',
        plugins_url( '/js/etsy-shop-admin.js', __FILE__ ),
        array( 'jquery' ),
        '1.0.0',
        true
    );
    $title_nonce = wp_create_nonce( 'etsy_shop_delete' );
    wp_localize_script(
        'ajax-script',
        'etsy_shop_admin_ajax',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $title_nonce,
        )
    );
}

add_action( 'wp_ajax_etsy_shop_delete_cache', 'etsy_shop_delete_cache_ajax_handler' );
function etsy_shop_delete_cache_ajax_handler() {
    // did the user is allowed?
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'etsy-shop' ) );
    }

    check_ajax_referer( 'etsy_shop_delete' );

    global $wpdb;
    $name = '_transient_' . ETSY_SHOP_CACHE_PREFIX . '%';  // do not use user input here
    $transient_list = $wpdb->get_results( "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE '$name'" );

    // delete all items
    foreach ($transient_list as $item ) {
        $name = $item->option_name;
        $name = str_replace( '_transient_', '', $name );
        delete_transient( $name );
    }

    echo '<span style="color:green;font-weight:bold;">'. __( 'Cache content deleted', 'etsy-shop' ) . '</span>';
    wp_die();
}

// Options Page Ajax
add_action( 'admin_enqueue_scripts', 'etsy_shop_enqueue' );

function etsy_shop_options_page() {
    // did the user is allowed?
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.', 'etsy-shop' ) );
    }

    $updated = false;
    $deleted = false;

    if ( isset( $_POST['submit'] ) ) {

        // Nonces Verification
        $key = get_option( 'etsy_shop_api_key' );
        check_admin_referer( 'etsy-shop-update-settings_'.$key );

        // did the user enter an API Key?
        if ( isset( $_POST['etsy_shop_api_key'] ) ) {
            $etsy_shop_api_key = wp_filter_nohtml_kses( preg_replace( '/[^A-Za-z0-9]/', '', $_POST['etsy_shop_api_key'] ) );
            update_option( 'etsy_shop_api_key', $etsy_shop_api_key );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter target new window for links?
        if ( isset( $_POST['etsy_shop_target_blank'] ) ) {
            $etsy_shop_target_blank = wp_filter_nohtml_kses( $_POST['etsy_shop_target_blank'] );
            update_option( 'etsy_shop_target_blank', $etsy_shop_target_blank );

            // and remember to note the update to user
            $updated = true;
        }else {
            $etsy_shop_target_blank = 0;
            update_option( 'etsy_shop_target_blank', $etsy_shop_target_blank );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter an Timeout?
        if ( isset( $_POST['etsy_shop_timeout'] ) ) {
            $etsy_shop_timeout = wp_filter_nohtml_kses( preg_replace( '/[^0-9]/', '', $_POST['etsy_shop_timeout'] ) );
            update_option( 'etsy_shop_timeout', $etsy_shop_timeout );

            // and remember to note the update to user
            $updated = true;
        }

        // did the user enter an Cache life?
        if ( isset( $_POST['etsy_shop_cache_life'] ) ) {
            $etsy_shop_cache_life = wp_filter_nohtml_kses( preg_replace( '/[^0-9]/', '', $_POST['etsy_shop_cache_life'] ) );
            update_option( 'etsy_shop_cache_life', $etsy_shop_cache_life * 3600 );  // update time in hours * seconds

            // and remember to note the update to user
            $updated = true;
        }
    }

    $etsy_shop_quickstart_shop_id = null;
    $quick_start_update = false;
    if ( isset( $_POST['submitQuickstart'] ) ) {
        // Nonces Verification
        check_admin_referer( 'etsy-shop-settings-quickstart' );

        // did the user enter an shop name?
        if ( isset( $_POST['etsy_shop_quickstart_shop_id'] ) ) {
            $etsy_shop_quickstart_shop_id = wp_filter_nohtml_kses(  preg_replace( '/[^a-zA-Z0-9,]/', '', $_POST['etsy_shop_quickstart_shop_id'] ) );
            update_option( 'etsy_shop_quickstart_shop_id', $etsy_shop_quickstart_shop_id );
            $quick_start_update = true;
        }
    }

    // grab the Etsy API key
    if( get_option( 'etsy_shop_api_key' ) ) {
        $etsy_shop_api_key = get_option( 'etsy_shop_api_key' );
    } else {
        add_option( 'etsy_shop_api_key', '' );
    }

    // grab the Etsy Target for links
    if( get_option( 'etsy_shop_target_blank' ) ) {
        $etsy_shop_target_blank = get_option( 'etsy_shop_target_blank' );
    } else {
        add_option( 'etsy_shop_target_blank', '0' );
    }

    // grab the Etsy Timeout
    if( get_option( 'etsy_shop_timeout' ) ) {
        $etsy_shop_timeout = get_option( 'etsy_shop_timeout' );
    } else {
        add_option( 'etsy_shop_timeout', '10' );
    }

    // grab the Etsy Cache life
    if( get_option( 'etsy_shop_cache_life' ) ) {
        $etsy_shop_cache_life = get_option( 'etsy_shop_cache_life' );
    } else {
        add_option( 'etsy_shop_cache_life', '21600' );
    }

    // create the Quickstart shop name
    if( !get_option( 'etsy_shop_quickstart_shop_id' ) ) {
        add_option( 'etsy_shop_quickstart_shop_id', '' );
    }

    if ( $updated ) {
        echo '<div class="updated fade"><p><strong>'. __( 'Options saved.', 'etsy-shop' ) .'</strong></p></div>';
    }

    $etsy_shop_quickstart_step = 1;
    // print the Options Page
    ?>
    <style>
        .etsty-shop-quickstart-step {
            display: inline-block;
            margin: 5px 15px 5px 5px;
            padding: 5px 10px 5px 10px;
            background-color: black;
            font-weight: bold;
            color: white;
        }
        #quickStartButton {
            display: block;
            text-decoration: none;
            margin: 10px 10px 10px 10px;
        }
        #etsy-shop-quick-start-content {
            width: 100%;
            background-color: lightgray;
            margin-top: 20px;
        }
        #etsy-shop-quickstart-sections{
            padding: 5px 20px 10px 80px;
        }
        #etsy-shop-delete-cache-result {
            display: inline-block;
            padding-top: 5px;
        }
    </style>
    <script>
        function quickStartButton() {
            var x = document.getElementById("etsy-shop-quick-start-content");
            if (x.style.display === "none") {
                x.style.display = "block";
            } else {
                x.style.display = "none";
            }
        }
    </script>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br /></div><h2><?php _e( 'Etsy Shop Options', 'etsy-shop' ); ?></h2>
        <div id="etsy-shop-quick-start" style="margin:10px 0px 10px 0px;padding:0px;border:2px solid #dddddd;">
            <a id="quickStartButton" href="#" onclick="quickStartButton()">
                <span class="dashicons dashicons-hammer" style="color:green;"></span>
                <span style="color:green;"><?php _e( 'Click here to start easilly!', 'etsy-shop' ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2" style="color:green;"></span>
            </a>
            <div id="etsy-shop-quick-start-content" <?php if (!$quick_start_update) { ?>style="display:none;"<?php } ?>>
                    <form name="etsy_shop_quickstart_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    <div class="etsty-shop-quickstart-step"><?php _e( 'STEP 1', 'etsy-shop' ); ?></div><span style="font-weight: bold;"><?php _e( 'Is your Etsy API Key is valid?', 'etsy-shop' ); ?></span>
                    <?php if ( !is_wp_error( etsy_shop_testAPIKey()) ) { $etsy_shop_quickstart_step = 2; ?>
                        <span id="etsy_shop_api_key_status_qs" style="color:green;font-weight:bold;"><?php _e( 'OK, go to step 2', 'etsy-shop' ); ?></span>
                    <?php } elseif ( get_option('etsy_shop_api_key') ) { ?>
                        <span id="etsy_shop_api_key_status_qs" style="color:red;font-weight:bold;"><?php _e( 'Please configure an API Key below in this page', 'etsy-shop' ); ?></span>
                    <?php } ?>
                <?php if ( $etsy_shop_quickstart_step === 2 ) { ?>
                    <br><div class="etsty-shop-quickstart-step"><?php _e( 'STEP 2', 'etsy-shop' ); ?></div><span style="margin-top: 8px;font-weight: bold;"><?php _e( 'What is your Shop name on etsy?', 'etsy-shop' ); ?></span>
                        <input id="etsy_shop_quickstart_shop_id" name="etsy_shop_quickstart_shop_id" type="text" size="25" value="<?php echo get_option( 'etsy_shop_quickstart_shop_id' ); ?>" class="regular-text code" />
                        <?php wp_nonce_field( 'etsy-shop-settings-quickstart' ); ?>
                        <input type="submit" name="submitQuickstart" id="submitQuickstart" class="button-primary" value="<?php _e( 'Search', 'etsy-shop' ); ?>" />
                    </form>
                <?php } ?>
                <?php if ( $etsy_shop_quickstart_step === 2 && get_option( 'etsy_shop_quickstart_shop_id' ) ) { $etsy_shop_quickstart_sections_list = etsy_shop_generateShopSectionList( get_option( 'etsy_shop_quickstart_shop_id' )); ?>
                    <br><div class="etsty-shop-quickstart-step"><?php _e( 'STEP 3', 'etsy-shop' ); ?></div><span style="margin-top: 8px;font-weight: bold;"><?php _e( 'List of sections that you can use, put the short code in your page or post:', 'etsy-shop' ); ?></span>
                    <?php if ( substr( $etsy_shop_quickstart_sections_list, 0, 3 ) === "ERR" ) { ?>
                    <p style="color:red;font-weight:bold;padding-left:80px;"><?php echo $etsy_shop_quickstart_sections_list; ?></p>
                    <?php } else { ?>
                    <table id="etsy-shop-quickstart-sections" class="wp-list-table widefat">
                        <thead>
                            <tr>
                                <th><?php _e('Shop Section Name', 'etsy-shop'); ?></th>
                                <th><?php _e('ID', 'etsy-shop'); ?></th>
                                <th><?php _e('Active listing', 'etsy-shop'); ?></th>
                                <th><?php _e('Short code', 'etsy-shop'); ?></th>
                            </tr>
                        </thead>
                        <?php echo $etsy_shop_quickstart_sections_list; ?>
                    </table>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <form name="etsy_shop_options_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_api_key"></label><?php _e('Etsy API Key', 'etsy-shop'); ?>
                    </th>
                    <td>
                        <input id="etsy_shop_api_key" name="etsy_shop_api_key" type="text" size="25" value="<?php echo get_option( 'etsy_shop_api_key' ); ?>" class="regular-text code" />
                                    <?php if ( !is_wp_error( etsy_shop_testAPIKey()) ) { ?>
                                        <span id="etsy_shop_api_key_status" style="color:green;font-weight:bold;"><?php _e( 'Your API Key is valid', 'etsy-shop' ); ?></span>
                                    <?php } elseif ( get_option('etsy_shop_api_key') ) { ?>
                                        <span id="etsy_shop_api_key_status" style="color:red;font-weight:bold;"><?php _e( 'You API Key is invalid', 'etsy-shop' ); ?></span>
                                    <?php } ?>
                                    <p class="description">
                                    <?php echo sprintf( __('You may get an Etsy API Key by <a href="%1$s">Creating a new Etsy App</a>', 'etsy-shop' ), 'http://www.etsy.com/developers/register' ); ?>
                                    <br><?php if ( is_wp_error( etsy_shop_testAPIKey()) ) { echo '<span id="etsy_shop_api_key_status" style="color:red;font-weight:bold;">'; } ?><?php echo sprintf( __('Make sure that your API Key is approved, not in Pending approval status. Go to <a href="%1$s">Manage your apps</a>', 'etsy-shop' ), 'https://www.etsy.com/developers/your-apps' ); ?><?php if ( is_wp_error( etsy_shop_testAPIKey()) ) { echo '</span>'; } ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_target_blank"></label><?php _e('Link to new window', 'etsy-shop'); ?></th>
                            <td>
                               <input id="etsy_shop_target_blank" name="etsy_shop_target_blank" type="checkbox" value="1" <?php checked( '1', get_option( 'etsy_shop_target_blank' ) ); ?> />
                                   <p class="description">
                                   <?php echo __( 'If you want your links to open a page in a new window', 'etsy-shop' ); ?>
                                   </p>
                            </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_timeout"></label><?php _e('Timeout', 'etsy-shop'); ?></th>
                            <td>
                                <input id="etsy_shop_timeout" name="etsy_shop_timeout" type="text" size="2" class="small-text" value="<?php echo get_option( 'etsy_shop_timeout' ); ?>" class="regular-text code" />
                                   <p class="description">
                                   <?php echo __( 'Time in seconds until a request times out. Default 10 seconds', 'etsy-shop' ); ?>
                                   </p>
                            </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_cache_life"></label><?php _e('Cache life', 'etsy-shop'); ?></th>
                            <td>
                                <input id="etsy_shop_cache_life" name="etsy_shop_cache_life" type="text" size="2" class="small-text" value="<?php echo get_option( 'etsy_shop_cache_life' ) / 3600; ?>" class="regular-text code" />
                                <?php _e('hours', 'etsy-shop'); ?>
                                <p class="description">
                                   <?php echo __( 'Time before the cache update the listing. Default: 6 hours', 'etsy-shop' ); ?>
                                </p>
                            </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="etsy_shop_cache_delete"></label><?php _e('Delete cache', 'etsy-shop'); ?></th>
                            <td>
                                <a id="btn-etsy-shop-delete-cache" class="button-secondary" href="#"> <?php _e('Clear all the cache for this plugin', 'etsy-shop'); ?></a> <span id="etsy-shop-delete-cache-result"></span>
                                <p class="description">
                                   <?php echo __( 'Delete all cache files for Etsy-Shop Plugin.', 'etsy-shop' ); ?>
                                </p>
                            </td>
                </tr>
</table>

        <h3 class="title"><?php _e( 'Need help?', 'etsy-shop' ); ?></h3>
        <p><?php echo sprintf( __( 'Please open a <a href="%1$s">new topic</a> on Wordpress.org Forum. This is your only way to let me know!', 'etsy-shop' ), 'http://wordpress.org/support/plugin/etsy-shop' ); ?></p>

        <h3 class="title"><?php _e( 'Need more features?', 'etsy-shop' ); ?></h3>
        <p><?php echo sprintf( __( 'Please sponsor a feature go to <a href="%1$s">Donation Page</a>.', 'etsy-shop' ), 'http://fsheedy.wordpress.com/etsy-shop-plugin/donate/' ); ?></p>

        <p class="submit">
                <?php $key = get_option( 'etsy_shop_api_key' );
                 wp_nonce_field( 'etsy-shop-update-settings_'.$key ); ?>
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e( 'Save Changes', 'etsy-shop' ); ?>" />
        </p>

        </form>
    </div>
<?php
}

// admin warning
if ( is_admin() ) {
    etsy_shop_warning();
}

function etsy_shop_warning() {
    if ( !get_option( 'etsy_shop_api_key' ) ) {
        function etsy_shop__api_key_warning() {
            echo "<div id='etsy-shop-warning' class='updated fade'><p><strong>".__( 'Etsy Shop is almost ready.', 'etsy-shop' )."</strong> ".sprintf( __( 'You must <a href="%1$s">enter your Etsy API key</a> for it to work.', 'etsy-shop' ), 'options-general.php?page=etsy-shop.php' )."</p></div>";
        }

        add_action( 'admin_notices', 'etsy_shop__api_key_warning' );
    }
}

function etsy_shop_plugin_action_links( $links, $file ) {
    if ( $file == plugin_basename( dirname( __FILE__ ).'/etsy-shop.php' ) ) {
        $links[] = '<a href="' . admin_url( 'options-general.php?page=etsy-shop.php' ) . '">'.__( 'Settings' ).'</a>';
    }

    return $links;
}
