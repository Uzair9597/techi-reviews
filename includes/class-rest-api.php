<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TECHI_Reviews_REST_API {
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route(
            'techi/v1',
            '/reviews',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_reviews' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'category' => array(
                        'sanitize_callback' => 'sanitize_title',
                        'validate_callback' => function ( $value ) {
                            return '' === $value || is_string( $value );
                        },
                    ),
                    'min_score' => array(
                        'sanitize_callback' => function ( $value ) { return is_numeric( $value ) ? (float) $value : $value; },
                        'validate_callback' => function ( $value ) { return is_numeric( $value ) && (float) $value >= 0 && (float) $value <= 10; },
                    ),
                    'orderby' => array(
                        'sanitize_callback' => 'sanitize_key',
                        'validate_callback' => function ( $value ) { return in_array( $value, array( 'date', 'title', 'score', 'modified' ), true ); },
                    ),
                    'order' => array(
                        'sanitize_callback' => function ( $value ) { return strtoupper( sanitize_text_field( $value ) ); },
                        'validate_callback' => function ( $value ) { return in_array( strtoupper( $value ), array( 'ASC', 'DESC' ), true ); },
                    ),
                    'search' => array(
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function ( $value ) { return is_string( $value ); },
                    ),
                    'page' => array(
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ( $value ) { return absint( $value ) >= 1; },
                    ),
                    'per_page' => array(
                        'default'           => 10,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ( $value ) { return absint( $value ) >= 1 && absint( $value ) <= 50; },
                    ),
                ),
            )
        );
    }

    public function get_reviews( WP_REST_Request $request ) {
        $page     = max( 1, (int) $request->get_param( 'page' ) );
        $per_page = min( 50, max( 1, (int) $request->get_param( 'per_page' ) ) );
        $orderby  = $request->get_param( 'orderby' ) ?: 'date';
        $order    = $request->get_param( 'order' ) ?: 'DESC';
        $search   = $request->get_param( 'search' );

        $args = array(
            'post_type'      => 'techi_review',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'order'          => $order,
            's'              => $search ?: '',
            'no_found_rows'  => false,
        );

        if ( 'score' === $orderby ) {
            $args['meta_key'] = '_techi_score';
            $args['orderby']  = 'meta_value_num';
        } else {
            $args['orderby'] = $orderby;
        }

        $category = $request->get_param( 'category' );
        if ( $category ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'techi_review_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            );
        }

        $min_score = $request->get_param( 'min_score' );
        if ( null !== $min_score && '' !== $min_score ) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_techi_score',
                    'value'   => (float) $min_score,
                    'compare' => '>=',
                    'type'    => 'NUMERIC',
                ),
            );
        }

        $query = new WP_Query( $args );
        $items = array();
        foreach ( $query->posts as $post ) {
            $items[] = $this->format_review( $post );
        }

        $response = new WP_REST_Response( $items, 200 );
        $response->header( 'X-WP-Total', (string) $query->found_posts );
        $response->header( 'X-WP-TotalPages', (string) $query->max_num_pages );
        return $response;
    }

    private function format_review( WP_Post $post ) {
        $score = get_post_meta( $post->ID, '_techi_score', true );
        $price = get_post_meta( $post->ID, '_techi_price_pkr', true );
        return array(
            'id'           => $post->ID,
            'title'        => get_the_title( $post ),
            'link'         => get_permalink( $post ),
            'product_name' => (string) get_post_meta( $post->ID, '_techi_product_name', true ),
            'score'        => '' === $score ? null : (float) $score,
            'verdict'      => (string) get_post_meta( $post->ID, '_techi_verdict', true ),
            'pros'         => (array) get_post_meta( $post->ID, '_techi_pros', true ),
            'cons'         => (array) get_post_meta( $post->ID, '_techi_cons', true ),
            'price_pkr'    => '' === $price ? null : (float) $price,
            'review_date'  => (string) get_post_meta( $post->ID, '_techi_review_date', true ),
            'author'       => get_the_author_meta( 'display_name', $post->post_author ),
            'categories'   => wp_get_post_terms( $post->ID, 'techi_review_category', array( 'fields' => 'names' ) ),
        );
    }
}
