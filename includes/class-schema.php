<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TECHI_Reviews_Schema {
    public function __construct() {
        add_filter( 'the_content', array( $this, 'score_card' ), 20 );
        add_action( 'wp_head', array( $this, 'json_ld' ), 20 );
    }

    public function score_card( $content ) {
        if ( ! is_singular( 'techi_review' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $post_id = get_the_ID();
        $product = get_post_meta( $post_id, '_techi_product_name', true );
        $score   = get_post_meta( $post_id, '_techi_score', true );
        $verdict = get_post_meta( $post_id, '_techi_verdict', true );
        $pros    = get_post_meta( $post_id, '_techi_pros', true );
        $cons    = get_post_meta( $post_id, '_techi_cons', true );
        $price   = get_post_meta( $post_id, '_techi_price_pkr', true );
        $date    = get_post_meta( $post_id, '_techi_review_date', true );

        ob_start();
        ?>
        <section class="techi-review-card" aria-label="<?php esc_attr_e( 'Review summary', 'techi-reviews' ); ?>" style="border:1px solid #ddd;padding:20px;margin:0 0 24px;background:#fafafa">
            <h2 style="margin-top:0"><?php esc_html_e( 'Review Summary', 'techi-reviews' ); ?></h2>
            <?php if ( $product ) : ?><p><strong><?php esc_html_e( 'Product:', 'techi-reviews' ); ?></strong> <?php echo esc_html( $product ); ?></p><?php endif; ?>
            <?php if ( '' !== $score ) : ?><p><strong><?php esc_html_e( 'Score:', 'techi-reviews' ); ?></strong> <?php echo esc_html( number_format_i18n( (float) $score, 1 ) ); ?> / 10</p><?php endif; ?>
            <?php if ( $verdict ) : ?><p><strong><?php esc_html_e( 'Verdict:', 'techi-reviews' ); ?></strong> <?php echo esc_html( $verdict ); ?></p><?php endif; ?>

            <?php $this->render_list( __( 'Pros', 'techi-reviews' ), $pros, '✓' ); ?>
            <?php $this->render_list( __( 'Cons', 'techi-reviews' ), $cons, '−' ); ?>

            <?php if ( '' !== $price && null !== $price ) : ?><p><strong><?php esc_html_e( 'Price:', 'techi-reviews' ); ?></strong> PKR <?php echo esc_html( number_format_i18n( (float) $price, 2 ) ); ?></p><?php endif; ?>
            <?php if ( $date ) : ?><p><strong><?php esc_html_e( 'Reviewed:', 'techi-reviews' ); ?></strong> <?php echo esc_html( mysql2date( get_option( 'date_format' ), $date ) ); ?></p><?php endif; ?>
        </section>
        <?php
        return ob_get_clean() . $content;
    }

    private function render_list( $heading, $items, $marker ) {
        if ( ! is_array( $items ) || empty( $items ) ) {
            return;
        }
        echo '<div class="techi-review-list"><strong>' . esc_html( $heading ) . '</strong><ul>';
        foreach ( $items as $item ) {
            echo '<li>' . esc_html( $marker . ' ' . $item ) . '</li>';
        }
        echo '</ul></div>';
    }

    public function json_ld() {
        if ( ! is_singular( 'techi_review' ) ) {
            return;
        }
        $post_id = get_queried_object_id();
        if ( ! $post_id ) {
            return;
        }

        $product = get_post_meta( $post_id, '_techi_product_name', true );
        $score   = get_post_meta( $post_id, '_techi_score', true );
        if ( '' === $product || '' === $score ) {
            return;
        }

        $review_date = get_post_meta( $post_id, '_techi_review_date', true );
        $price       = get_post_meta( $post_id, '_techi_price_pkr', true );
        $data        = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Review',
            'url'      => get_permalink( $post_id ),
            'itemReviewed' => array(
                '@type' => 'Product',
                'name'  => $product,
            ),
            'reviewRating' => array(
                '@type'       => 'Rating',
                'ratingValue' => (string) (float) $score,
                'bestRating'  => '10',
                'worstRating' => '0',
            ),
            'reviewBody' => wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ),
            'author' => array(
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
            ),
        );

        if ( $review_date ) {
            $data['datePublished'] = $review_date;
        }
        if ( '' !== $price && null !== $price ) {
            $data['itemReviewed']['offers'] = array(
                '@type'         => 'Offer',
                'price'         => (string) (float) $price,
                'priceCurrency' => 'PKR',
            );
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
    }
}
