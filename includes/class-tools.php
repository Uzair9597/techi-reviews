<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TECHI_Reviews_Tools {
    const SYNTHETIC_META = '_techi_synthetic';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_post_techi_seed_reviews', array( $this, 'seed' ) );
        add_action( 'admin_post_techi_delete_reviews', array( $this, 'delete' ) );
    }

    public function menu() {
        add_management_page(
            __( 'TECHi Reviews', 'techi-reviews' ),
            __( 'TECHi Reviews', 'techi-reviews' ),
            'manage_options',
            'techi-reviews-tools',
            array( $this, 'render' )
        );
    }

    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'techi-reviews' ) );
        }
        $count = $this->count_synthetic();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'TECHi Review Tools', 'techi-reviews' ); ?></h1>
            <p><?php esc_html_e( 'Use these tools to create or remove safe synthetic review data for the assessment demo.', 'techi-reviews' ); ?></p>
            <p><strong><?php esc_html_e( 'Synthetic reviews currently:', 'techi-reviews' ); ?></strong> <?php echo esc_html( $count ); ?></p>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin: 20px 0;">
                <input type="hidden" name="action" value="techi_seed_reviews" />
                <?php wp_nonce_field( 'techi_seed_reviews', 'techi_seed_nonce' ); ?>
                <?php submit_button( __( 'Seed 25 Synthetic Reviews', 'techi-reviews' ), 'primary', 'submit', false ); ?>
            </form>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="techi_delete_reviews" />
                <?php wp_nonce_field( 'techi_delete_reviews', 'techi_delete_nonce' ); ?>
                <?php submit_button( __( 'Delete Synthetic Reviews', 'techi-reviews' ), 'delete', 'submit', false ); ?>
            </form>
        </div>
        <?php
    }

    public function seed() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'techi-reviews' ) );
        }
        check_admin_referer( 'techi_seed_reviews', 'techi_seed_nonce' );

        $categories = array( 'Laptops', 'Phones', 'AI Tools', 'Software', 'Gaming' );
        foreach ( $categories as $name ) {
            if ( ! term_exists( $name, 'techi_review_category' ) ) {
                wp_insert_term( $name, 'techi_review_category' );
            }
        }

        for ( $i = 1; $i <= 25; $i++ ) {
            $post_id = wp_insert_post(
                array(
                    'post_type'    => 'techi_review',
                    'post_status'  => 'publish',
                    'post_title'   => sprintf( 'Synthetic Review %02d', $i ),
                    'post_content' => 'Synthetic review content created for the TECHi Review Desk assessment demo.',
                    'post_author'  => get_current_user_id(),
                ),
                true
            );
            if ( is_wp_error( $post_id ) ) {
                continue;
            }

            $category = $categories[ ( $i - 1 ) % count( $categories ) ];
            wp_set_object_terms( $post_id, $category, 'techi_review_category' );
            update_post_meta( $post_id, '_techi_synthetic', '1' );
            update_post_meta( $post_id, '_techi_product_name', 'Demo Product ' . $i );
            update_post_meta( $post_id, '_techi_score', round( 6 + ( ( $i * 7 ) % 41 ) / 10, 1 ) );
            update_post_meta( $post_id, '_techi_verdict', 'A synthetic verdict created for testing structured review data.' );
            update_post_meta( $post_id, '_techi_pros', array( 'Clear demo workflow', 'Structured review data' ) );
            update_post_meta( $post_id, '_techi_cons', array( 'Synthetic data only' ) );
            update_post_meta( $post_id, '_techi_price_pkr', 25000 + ( $i * 5000 ) );
            update_post_meta( $post_id, '_techi_review_date', gmdate( 'Y-m-d', strtotime( '-' . $i . ' days' ) ) );
        }

        wp_safe_redirect( add_query_arg( array( 'page' => 'techi-reviews-tools', 'seeded' => 1 ), admin_url( 'tools.php' ) ) );
        exit;
    }

    public function delete() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'techi-reviews' ) );
        }
        check_admin_referer( 'techi_delete_reviews', 'techi_delete_nonce' );

        $posts = get_posts(
            array(
                'post_type'      => 'techi_review',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_key'       => self::SYNTHETIC_META,
                'meta_value'     => '1',
            )
        );
        foreach ( $posts as $post_id ) {
            wp_delete_post( $post_id, true );
        }

        wp_safe_redirect( add_query_arg( array( 'page' => 'techi-reviews-tools', 'deleted' => count( $posts ) ), admin_url( 'tools.php' ) ) );
        exit;
    }

    private function count_synthetic() {
        $query = new WP_Query(
            array(
                'post_type'      => 'techi_review',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => self::SYNTHETIC_META,
                'meta_value'     => '1',
            )
        );
        return (int) $query->found_posts;
    }
}
