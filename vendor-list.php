<?php

/*
    Plugin Name: Shore Leave Vendors List
    Plugin URI: https://www.shore-leave.com
    Description: Adds a vendor list to the Shore Leave site.
    Version: 0.1
    Author: That Blair Guy
    Author URI: https://chaosandpenguins.com
    License: GPLv2 or later
*/


/*
    Start off by setting up the vendor post type.
*/

define('TYPE_NAME', 'vendor');

add_action( 'init', 'vendor_register_post_type');

function vendor_register_post_type() {

    // Vendors
    $labels = array(
        'name' => __( 'Vendors' ),
        'singular_name' => __( 'Vendor' ),
        'add_new' => __( 'New Vendor' ),
        'add_new_item' => __( 'Add New Vendor' ),
        'edit_item' => __( 'Edit Vendor' ),
        'new_item' => __( 'New Vendor' ),
        'view_item' => __( 'View Vendor' ),
        'search_items' => __( 'Search Vendors' ),
        'not_found' =>  __( 'No Vendors Found' ),
        'not_found_in_trash' => __( 'No Vendor found in Trash' ),
    );

    $args = array(
        'labels' => $labels,
        'description' => 'A vendor',
        'has_archive' => false,
        'public' => true,
        'publicly_queryable' => true,
        'query_var' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'hierarchical' => false,
        'supports' => array(
            // No title, no editorial field....
            'revisions'
        ),

        'rewrite'   => array( 'slug' => 'vendors' ),
        'show_in_rest' => true
    );

    return register_post_type(TYPE_NAME, $args);
}

/**
 * Setup our custom fields.
 */
add_action('admin_init', 'admin_screen_setup');

function admin_screen_setup() {
    add_meta_box('vendor_link', 'Vendor Information', 'setup_vendor_form', TYPE_NAME, 'normal', 'low');
}

function setup_vendor_form() {
    global $post;

    $custom = get_post_custom($post->ID);

    $name = $post->post_title;
    if(array_key_exists('vendor_link', $custom)) {
        $link = $custom['vendor_link'][0];
    } else {
        $link = '';
    }
    ?>
    <p><b><label>Name:</label></b><br/>
    <input name="vendor_name" type="text" size="60" maxlength="255" value="<?php echo $name ?>" required="required" />
    <p><b><label>Website:</label></b><br/>
    <input name="vendor_link" type="url"  size="60" maxlength="255" value="<?php echo $link ?>" />
    <?php
}

/**
 * Save everything.
 */
add_action('save_post', 'save_details'); // custom fields
add_filter( 'wp_insert_post_data', 'modify_post_title' , '99', 1 );  // Post title

function save_details() {
    global $post;

    $id = $post ? $post->ID : NULL;
    $link = array_key_exists('vendor_name', $_POST) ? $_POST['vendor_link'] : '';

    update_post_meta($id, 'vendor_link', $link);
}

function modify_post_title( $data ) {
    if($data['post_type'] == TYPE_NAME && isset($_POST['vendor_name']) ) {
        $data['post_title'] = $_POST['vendor_name'];
    }

    return $data;
}

add_shortcode('vendor-list', 'generate_vendor_list');
function generate_vendor_list($attributes, $content, $codeName) {

    $args = array(
        'post_type'     => TYPE_NAME,
        'nopaging'      => true,
        'orderby'       => 'title',
        'order'         => 'ASC',
    );
    $loop = new WP_Query($args);

    // Build up an array of vendor entries.
    $entries = array();
    while ( $loop->have_posts() ) {
        $loop->the_post();

        $post           = get_post();
        $customFields   = get_post_custom($post->ID);

        $title = apply_filters('the_title', $post->post_title);
        $link = $customFields['vendor_link'][0];


        if(trim($link))
            $entries[] = "<li><a href=\"$link\" target=\"_blank\">$title</a></li>";
        else
            $entries[] =  "<li>$title</li>";
    }

    // Determine number of chunks (columns).
    $vendorCount = count($entries);
    switch ($vendorCount) {
        case 0:  $chunkCount = 0; break;
        case 1:  $chunkCount = 1; break;
        default:
            $chunkCount = ($vendorCount < 5) ? 2 : 3;
            break;
    }
    $chunksize = ceil($vendorCount / $chunkCount);

    // Build up the collection of lists.
    $output = '';
    for ($i=0; $i < $chunkCount; $i++) {
        $offset = $i * $chunksize;
        $output .= '<div class="col-xs-12 col-md-4 ">';
        $output .= '<ul style="margin-top:0px;">';
        $output .= implode('', array_slice($entries, $offset, $chunksize));
        $output .= '</ul>';
        $output .= '</div>';
    }

    return $output;
}