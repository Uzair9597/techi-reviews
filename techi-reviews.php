<?php
/**
 * Plugin Name: TECHi Review Desk
 * Description: Structured product reviews for TECHi with a custom post type, review metadata, REST API, score card, schema, and synthetic demo tools.
 * Version: 1.0.0
 * Author: Assessment Submission
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: techi-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TECHI_REVIEWS_VERSION', '1.0.0' );
define( 'TECHI_REVIEWS_FILE', __FILE__ );
define( 'TECHI_REVIEWS_DIR', plugin_dir_path( __FILE__ ) );
define( 'TECHI_REVIEWS_URL', plugin_dir_url( __FILE__ ) );

require_once TECHI_REVIEWS_DIR . 'includes/class-post-type.php';
require_once TECHI_REVIEWS_DIR . 'includes/class-meta-box.php';
require_once TECHI_REVIEWS_DIR . 'includes/class-rest-api.php';
require_once TECHI_REVIEWS_DIR . 'includes/class-tools.php';
require_once TECHI_REVIEWS_DIR . 'includes/class-schema.php';

function techi_reviews_bootstrap() {
    new TECHI_Reviews_Post_Type();
    new TECHI_Reviews_Meta_Box();
    new TECHI_Reviews_REST_API();
    new TECHI_Reviews_Tools();
    new TECHI_Reviews_Schema();
}
add_action( 'plugins_loaded', 'techi_reviews_bootstrap' );

function techi_reviews_activate() {
    $post_type = new TECHI_Reviews_Post_Type();
    $post_type->register();
    $post_type->register_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'techi_reviews_activate' );

// Rewrite rules are intentionally flushed on activation only.
