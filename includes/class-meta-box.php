<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TECHI_Reviews_Meta_Box {
    const META_KEYS = array(
        '_techi_product_name',
        '_techi_score',
        '_techi_verdict',
        '_techi_pros',
        '_techi_cons',
        '_techi_price_pkr',
        '_techi_review_date',
    );

    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_techi_review', array( $this, 'save' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'init', array( $this, 'register_meta' ) );
    }

    public function register_meta() {
        register_post_meta(
            'techi_review',
            '_techi_product_name',
            array(
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback'     => array( $this, 'meta_auth' ),
            )
        );
        register_post_meta(
            'techi_review',
            '_techi_score',
            array(
                'type'              => 'number',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => array( $this, 'sanitize_score' ),
                'auth_callback'     => array( $this, 'meta_auth' ),
            )
        );
        register_post_meta(
            'techi_review',
            '_techi_verdict',
            array(
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => array( $this, 'sanitize_verdict' ),
                'auth_callback'     => array( $this, 'meta_auth' ),
            )
        );
        foreach ( array( '_techi_pros', '_techi_cons' ) as $key ) {
            register_post_meta(
                'techi_review',
                $key,
                array(
                    'type'              => 'array',
                    'single'            => true,
                    'show_in_rest'      => false,
                    'sanitize_callback' => array( $this, 'sanitize_repeatable' ),
                    'auth_callback'     => array( $this, 'meta_auth' ),
                )
            );
        }
        register_post_meta(
            'techi_review',
            '_techi_price_pkr',
            array(
                'type'              => 'number',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => array( $this, 'sanitize_price' ),
                'auth_callback'     => array( $this, 'meta_auth' ),
            )
        );
        register_post_meta(
            'techi_review',
            '_techi_review_date',
            array(
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => array( $this, 'sanitize_date' ),
                'auth_callback'     => array( $this, 'meta_auth' ),
            )
        );
    }

    public function meta_auth( $allowed, $meta_key, $post_id, $user_id ) {
        return user_can( $user_id, 'edit_post', $post_id );
    }

    public function add_meta_box() {
        add_meta_box(
            'techi-review-details',
            __( 'TECHi Review Details', 'techi-reviews' ),
            array( $this, 'render' ),
            'techi_review',
            'normal',
            'high'
        );
    }

    public function assets( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'techi_review' !== $screen->post_type ) {
            return;
        }
        wp_enqueue_style( 'techi-reviews-admin', TECHI_REVIEWS_URL . 'assets/admin.css', array(), TECHI_REVIEWS_VERSION );
        wp_enqueue_script( 'techi-reviews-admin', TECHI_REVIEWS_URL . 'assets/admin.js', array(), TECHI_REVIEWS_VERSION, true );
    }

    public function render( $post ) {
        wp_nonce_field( 'techi_review_save', 'techi_review_nonce' );

        $product = get_post_meta( $post->ID, '_techi_product_name', true );
        $score   = get_post_meta( $post->ID, '_techi_score', true );
        $verdict = get_post_meta( $post->ID, '_techi_verdict', true );
        $pros    = get_post_meta( $post->ID, '_techi_pros', true );
        $cons    = get_post_meta( $post->ID, '_techi_cons', true );
        $price   = get_post_meta( $post->ID, '_techi_price_pkr', true );
        $date    = get_post_meta( $post->ID, '_techi_review_date', true );

        $pros = is_array( $pros ) ? $pros : array();
        $cons = is_array( $cons ) ? $cons : array();
        if ( empty( $pros ) ) {
            $pros = array( '' );
        }
        if ( empty( $cons ) ) {
            $cons = array( '' );
        }
        ?>
        <div class="techi-review-fields">
            <p>
                <label for="techi_product_name"><strong><?php esc_html_e( 'Product Name', 'techi-reviews' ); ?></strong></label>
                <input class="widefat" type="text" id="techi_product_name" name="techi_product_name" value="<?php echo esc_attr( $product ); ?>" />
            </p>
            <div class="techi-two-col">
                <p>
                    <label for="techi_score"><strong><?php esc_html_e( 'Score (0–10)', 'techi-reviews' ); ?></strong></label>
                    <input type="number" id="techi_score" name="techi_score" value="<?php echo esc_attr( $score ); ?>" min="0" max="10" step="0.1" />
                </p>
                <p>
                    <label for="techi_price_pkr"><strong><?php esc_html_e( 'Price (PKR)', 'techi-reviews' ); ?></strong></label>
                    <input class="widefat" type="number" id="techi_price_pkr" name="techi_price_pkr" value="<?php echo esc_attr( $price ); ?>" min="0" step="0.01" />
                </p>
            </div>
            <p>
                <label for="techi_verdict"><strong><?php esc_html_e( 'Verdict (max 140 characters)', 'techi-reviews' ); ?></strong></label>
                <textarea class="widefat" id="techi_verdict" name="techi_verdict" maxlength="140" rows="3"><?php echo esc_textarea( $verdict ); ?></textarea>
            </p>
            <p>
                <label for="techi_review_date"><strong><?php esc_html_e( 'Review Date', 'techi-reviews' ); ?></strong></label>
                <input type="date" id="techi_review_date" name="techi_review_date" value="<?php echo esc_attr( $date ); ?>" />
            </p>

            <?php $this->render_repeatable( 'pros', __( 'Pros', 'techi-reviews' ), $pros ); ?>
            <?php $this->render_repeatable( 'cons', __( 'Cons', 'techi-reviews' ), $cons ); ?>
        </div>
        <?php
    }

    private function render_repeatable( $type, $label, $items ) {
        ?>
        <div class="techi-repeatable" data-type="<?php echo esc_attr( $type ); ?>">
            <div class="techi-repeatable-heading">
                <strong><?php echo esc_html( $label ); ?></strong>
                <button type="button" class="button techi-add-item"><?php echo esc_html( sprintf( __( 'Add %s', 'techi-reviews' ), rtrim( $label, 's' ) ) ); ?></button>
            </div>
            <div class="techi-repeatable-list">
                <?php foreach ( $items as $item ) : ?>
                    <div class="techi-repeatable-row">
                        <input class="widefat" type="text" name="techi_<?php echo esc_attr( $type ); ?>[]" value="<?php echo esc_attr( $item ); ?>" />
                        <button type="button" class="button-link-delete techi-remove-item"><?php esc_html_e( 'Remove', 'techi-reviews' ); ?></button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST['techi_review_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['techi_review_nonce'] ) ), 'techi_review_save' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $this->update_or_delete( $post_id, '_techi_product_name', isset( $_POST['techi_product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['techi_product_name'] ) ) : '' );

        if ( isset( $_POST['techi_score'] ) && '' !== $_POST['techi_score'] ) {
            $raw_score = wp_unslash( $_POST['techi_score'] );
            $score = filter_var( $raw_score, FILTER_VALIDATE_FLOAT );
            if ( false !== $score && $score >= 0 && $score <= 10 && 1 === strlen( substr( strrchr( (string) $raw_score, '.' ) ?: '', 1 ) ) ) {
                update_post_meta( $post_id, '_techi_score', round( (float) $score, 1 ) );
            } elseif ( false !== $score && $score >= 0 && $score <= 10 && floor( (float) $score ) == (float) $score ) {
                update_post_meta( $post_id, '_techi_score', (float) $score );
            } else {
                delete_post_meta( $post_id, '_techi_score' );
            }
        } else {
            delete_post_meta( $post_id, '_techi_score' );
        }

        $verdict = isset( $_POST['techi_verdict'] ) ? sanitize_textarea_field( wp_unslash( $_POST['techi_verdict'] ) ) : '';
        $verdict = function_exists( 'mb_substr' ) ? mb_substr( $verdict, 0, 140 ) : substr( $verdict, 0, 140 );
        $this->update_or_delete( $post_id, '_techi_verdict', $verdict );

        foreach ( array( 'pros', 'cons' ) as $type ) {
            $items = isset( $_POST[ 'techi_' . $type ] ) && is_array( $_POST[ 'techi_' . $type ] ) ? wp_unslash( $_POST[ 'techi_' . $type ] ) : array();
            $items = $this->sanitize_repeatable( $items );
            if ( empty( $items ) ) {
                delete_post_meta( $post_id, '_techi_' . $type );
            } else {
                update_post_meta( $post_id, '_techi_' . $type, $items );
            }
        }

        if ( isset( $_POST['techi_price_pkr'] ) && '' !== $_POST['techi_price_pkr'] ) {
            $price = filter_var( wp_unslash( $_POST['techi_price_pkr'] ), FILTER_VALIDATE_FLOAT );
            if ( false !== $price && $price >= 0 ) {
                update_post_meta( $post_id, '_techi_price_pkr', round( (float) $price, 2 ) );
            } else {
                delete_post_meta( $post_id, '_techi_price_pkr' );
            }
        } else {
            delete_post_meta( $post_id, '_techi_price_pkr' );
        }

        $date = isset( $_POST['techi_review_date'] ) ? sanitize_text_field( wp_unslash( $_POST['techi_review_date'] ) ) : '';
        $date = $this->sanitize_date( $date );
        $this->update_or_delete( $post_id, '_techi_review_date', $date );
    }

    private function update_or_delete( $post_id, $key, $value ) {
        if ( '' === $value || null === $value ) {
            delete_post_meta( $post_id, $key );
        } else {
            update_post_meta( $post_id, $key, $value );
        }
    }

    public function sanitize_score( $value ) {
        if ( '' === $value || null === $value || ! is_numeric( $value ) ) {
            return null;
        }
        $score = (float) $value;
        if ( $score < 0 || $score > 10 ) {
            return null;
        }
        return round( $score, 1 );
    }

    public function sanitize_verdict( $value ) {
        $value = sanitize_textarea_field( $value );
        return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 140 ) : substr( $value, 0, 140 );
    }

    public function sanitize_repeatable( $value ) {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $clean = array();
        foreach ( $value as $item ) {
            $item = sanitize_text_field( $item );
            if ( '' !== $item ) {
                $clean[] = $item;
            }
        }
        return array_values( $clean );
    }

    public function sanitize_price( $value ) {
        if ( '' === $value || null === $value || ! is_numeric( $value ) || (float) $value < 0 ) {
            return null;
        }
        return round( (float) $value, 2 );
    }

    public function sanitize_date( $value ) {
        $value = sanitize_text_field( $value );
        if ( '' === $value ) {
            return '';
        }
        $date = DateTime::createFromFormat( 'Y-m-d', $value );
        if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
            return '';
        }
        return $value;
    }
}
