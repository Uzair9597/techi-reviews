<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TECHI_Reviews_Post_Type {
    const POST_TYPE = 'techi_review';
    const TAXONOMY = 'techi_review_category';

    public function __construct() {
        add_action( 'init', array( $this, 'register' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
        add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
        add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
        add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'handle_admin_sorting' ) );
        add_action( 'restrict_manage_posts', array( $this, 'category_filter' ) );
        add_filter( 'parse_query', array( $this, 'apply_category_filter' ) );
    }

    public function register() {
        register_post_type(
            self::POST_TYPE,
            array(
                'labels' => array(
                    'name'               => __( 'Reviews', 'techi-reviews' ),
                    'singular_name'      => __( 'Review', 'techi-reviews' ),
                    'add_new'            => __( 'Add Review', 'techi-reviews' ),
                    'add_new_item'       => __( 'Add New Review', 'techi-reviews' ),
                    'edit_item'          => __( 'Edit Review', 'techi-reviews' ),
                    'new_item'           => __( 'New Review', 'techi-reviews' ),
                    'view_item'          => __( 'View Review', 'techi-reviews' ),
                    'search_items'       => __( 'Search Reviews', 'techi-reviews' ),
                    'not_found'          => __( 'No reviews found.', 'techi-reviews' ),
                    'menu_name'          => __( 'Reviews', 'techi-reviews' ),
                ),
                'public'              => true,
                'has_archive'         => true,
                'show_in_rest'        => true,
                'menu_icon'           => 'dashicons-star-filled',
                'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
                'rewrite'             => array( 'slug' => 'reviews' ),
                'capability_type'     => 'post',
                'map_meta_cap'        => true,
                'show_in_nav_menus'   => true,
            )
        );
    }

    public function register_taxonomy() {
        register_taxonomy(
            self::TAXONOMY,
            array( self::POST_TYPE ),
            array(
                'labels' => array(
                    'name'          => __( 'Review Categories', 'techi-reviews' ),
                    'singular_name' => __( 'Review Category', 'techi-reviews' ),
                ),
                'public'            => true,
                'hierarchical'      => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => array( 'slug' => 'review-category' ),
            )
        );
    }

    public function columns( $columns ) {
        $new = array();
        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'title' === $key ) {
                $new['techi_score'] = __( 'Score', 'techi-reviews' );
            }
        }
        return $new;
    }

    public function column_content( $column, $post_id ) {
        if ( 'techi_score' === $column ) {
            $score = get_post_meta( $post_id, '_techi_score', true );
            echo '' !== $score ? esc_html( number_format_i18n( (float) $score, 1 ) . ' / 10' ) : '&mdash;';
        }
    }

    public function sortable_columns( $columns ) {
        $columns['techi_score'] = 'techi_score';
        return $columns;
    }

    public function handle_admin_sorting( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
            return;
        }
        if ( 'techi_score' === $query->get( 'orderby' ) ) {
            $query->set( 'meta_key', '_techi_score' );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }

    public function category_filter( $post_type ) {
        if ( self::POST_TYPE !== $post_type ) {
            return;
        }
        $selected = isset( $_GET[ self::TAXONOMY ] ) ? sanitize_text_field( wp_unslash( $_GET[ self::TAXONOMY ] ) ) : '';
        wp_dropdown_categories(
            array(
                'show_option_all' => __( 'All Review Categories', 'techi-reviews' ),
                'taxonomy'        => self::TAXONOMY,
                'name'            => self::TAXONOMY,
                'orderby'         => 'name',
                'selected'        => $selected,
                'hierarchical'    => true,
                'hide_empty'      => false,
                'value_field'     => 'slug',
            )
        );
    }

    public function apply_category_filter( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
            return;
        }
        if ( empty( $_GET[ self::TAXONOMY ] ) ) {
            return;
        }
        $slug = sanitize_title( wp_unslash( $_GET[ self::TAXONOMY ] ) );
        if ( '' !== $slug ) {
            $query->set(
                'tax_query',
                array(
                    array(
                        'taxonomy' => self::TAXONOMY,
                        'field'    => 'slug',
                        'terms'    => $slug,
                    ),
                )
            );
        }
    }
}
