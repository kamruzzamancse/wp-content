<?php
/*
Plugin Name: Movie Ticket Booking
Description: Movie Ticket Booking system with WooCommerce integration.
Version: 1.1
Author: Your Name
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Register custom product type class
 */
add_action( 'init', function() {
    if ( class_exists( 'WC_Product' ) && ! class_exists( 'WC_Product_Movie_Ticket' ) ) {
        class WC_Product_Movie_Ticket extends WC_Product {
            public function __construct( $product = 0 ) {
                $this->product_type = 'movie_ticket';
                parent::__construct( $product );
            }
        }
    }
});

/**
 * Add custom product type to selector dropdown in product edit screen
 */
add_filter( 'product_type_selector', function( $types ) {
    $types['movie_ticket'] = __( 'Movie Ticket', 'movie-showtime' );
    return $types;
});

/**
 * Add Custom Tab for Movie Details
 */
add_filter( 'woocommerce_product_data_tabs', function( $tabs ) {
    $tabs['movie_tab'] = array(
        'label'  => __( 'Movie Details', 'movie-showtime' ),
        'target' => 'movie_options',
        'class'  => array( 'show_if_movie_ticket' ), // show only for movie_ticket
    );
    return $tabs;
});

/**
 * Add fields for Show Start Time and Show End Time in the movie tab
 */
add_action( 'woocommerce_product_data_panels', function() {
    global $post;

    // Get existing movie showtimes if they exist
    $showtimes = get_post_meta( $post->ID, '_movie_showtimes', true );
    $showtimes = ! empty( $showtimes ) ? $showtimes : array();

    echo '<div id="movie_options" class="panel woocommerce_options_panel">';

    echo '<div id="movie_showtimes_container">';
    // Display existing showtimes
    if ( ! empty( $showtimes ) ) {
        foreach ( $showtimes as $index => $showtime ) {
            $start_val = isset( $showtime['start'] ) ? esc_attr( $showtime['start'] ) : '';
            $end_val   = isset( $showtime['end'] ) ? esc_attr( $showtime['end'] ) : '';

            echo '<div class="movie_showtime" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">';
            
            // Start Time Field
            echo '<div class="form-field" style="margin-bottom: 10px;">';
            echo '<label for="movie_showtime_start_' . $index . '" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __( 'Show Start Time', 'movie-showtime' ) . '</label>';
            echo '<input type="datetime-local" name="movie_showtime_start_' . $index . '" id="movie_showtime_start_' . $index . '" value="' . $start_val . '" class="short" style="width: 100%;" />';
            echo '</div>';
            
            // End Time Field
            echo '<div class="form-field" style="margin-bottom: 10px;">';
            echo '<label for="movie_showtime_end_' . $index . '" style="display: block; margin-bottom: 5px; font-weight: 600;">' . __( 'Show End Time', 'movie-showtime' ) . '</label>';
            echo '<input type="datetime-local" name="movie_showtime_end_' . $index . '" id="movie_showtime_end_' . $index . '" value="' . $end_val . '" class="short" style="width: 100%;" />';
            echo '</div>';
            
            echo '<button type="button" class="button remove_showtime_button" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Remove Showtime</button>';
            echo '</div>';
        }
    }
    echo '</div>'; // #movie_showtimes_container

    // Add button to add more showtimes
    echo '<p><button type="button" class="button button-primary" id="add_new_showtime_button" style="background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">Add New Showtime</button></p>';
    echo '</div>'; // #movie_options
});

/**
 * Admin footer script to manage dynamic addition/removal of showtimes.
 * This fetches current post showtimes dynamically so it works across pages.
 */
add_action( 'admin_footer', function() {
    // Only run on product post type edit screen
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'product' ) {
        return;
    }

    global $post;
    // Retrieve existing showtimes for the current post if available
    $showtimes = array();
    if ( isset( $post->ID ) ) {
        $showtimes = get_post_meta( $post->ID, '_movie_showtimes', true );
        $showtimes = ! empty( $showtimes ) ? $showtimes : array();
    }

    // Safe JSON encode for JS
    $showtimes_count = is_array( $showtimes ) ? count( $showtimes ) : 0;
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($){
        var showtimeIndex = <?php echo json_encode( $showtimes_count ); ?>;

        // Add new showtime input fields
        $('#add_new_showtime_button').on('click', function(e){
            e.preventDefault();
            var index = showtimeIndex;
            showtimeIndex++;
            var newShowtimeHTML = `
                <div class="movie_showtime" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                    <div class="form-field" style="margin-bottom: 10px;">
                        <label for="movie_showtime_start_${index}" style="display: block; margin-bottom: 5px; font-weight: 600;">Show Start Time</label>
                        <input type="datetime-local" name="movie_showtime_start_${index}" id="movie_showtime_start_${index}" value="" class="short" style="width: 100%;" />
                    </div>
                    <div class="form-field" style="margin-bottom: 10px;">
                        <label for="movie_showtime_end_${index}" style="display: block; margin-bottom: 5px; font-weight: 600;">Show End Time</label>
                        <input type="datetime-local" name="movie_showtime_end_${index}" id="movie_showtime_end_${index}" value="" class="short" style="width: 100%;" />
                    </div>
                    <button type="button" class="button remove_showtime_button" style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Remove Showtime</button>
                </div>
            `;
            $('#movie_showtimes_container').append(newShowtimeHTML);
        });

        // Remove showtime field
        $(document).on('click', '.remove_showtime_button', function(e){
            e.preventDefault();
            $(this).closest('.movie_showtime').remove();
        });
    });
    </script>
    <?php
});

/**
 * Save the custom fields (multiple showtimes) when the product is saved
 */
add_action( 'woocommerce_admin_process_product_object', function( $product ) {
    // Initialize the array of showtimes
    $showtimes = array();

    if ( isset( $_POST ) && is_array( $_POST ) ) {
        // Loop through the incoming $_POST data to capture each showtime start and end time
        foreach ( $_POST as $key => $value ) {
            // We're looking for keys that start with 'movie_showtime_start_'
            if ( strpos( $key, 'movie_showtime_start_' ) === 0 ) {
                // Extract the index from the key (e.g., '1' from 'movie_showtime_start_1')
                $index = str_replace( 'movie_showtime_start_', '', $key );

                // Check if the corresponding end time exists
                if ( isset( $_POST["movie_showtime_end_{$index}"] ) && ! empty( $_POST["movie_showtime_start_{$index}"] ) ) {
                    $start_val = sanitize_text_field( wp_unslash( $_POST["movie_showtime_start_{$index}"] ) );
                    $end_val   = sanitize_text_field( wp_unslash( $_POST["movie_showtime_end_{$index}"] ) );

                    // Only store if start is present (end can be optional)
                    $showtimes[] = array(
                        'start' => $start_val,
                        'end'   => $end_val,
                    );
                }
            }
        }
    }

    // Update the product's metadata with the showtimes array (or delete if empty)
    if ( ! empty( $showtimes ) ) {
        $product->update_meta_data( '_movie_showtimes', $showtimes );
    } else {
        // Remove meta if no showtimes provided
        $product->delete_meta_data( '_movie_showtimes' );
    }
});

/**
 * Display Show Start Time and Show End Time on the single product page
 */
add_action( 'woocommerce_single_product_summary', function() {
    global $product;

    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    if ( $product->get_type() === 'movie_ticket' ) {
        // Retrieve the saved showtimes from product metadata
        $showtimes = $product->get_meta( '_movie_showtimes' );

        // Check if there are any showtimes saved
        if ( ! empty( $showtimes ) && is_array( $showtimes ) ) {
            echo '<div class="movie-showtimes">';
            foreach ( $showtimes as $showtime ) {
                if ( ! empty( $showtime['start'] ) ) {
                    $start_time = date( 'l, F j, Y \a\t g:i A', strtotime( $showtime['start'] ) );
                    $end_time   = ! empty( $showtime['end'] ) ? date( 'g:i A', strtotime( $showtime['end'] ) ) : '';
                    echo '<p><strong>Showtime:</strong> ' . esc_html( $start_time );
                    if ( $end_time ) {
                        echo ' - ' . esc_html( $end_time );
                    }
                    echo '</p>';
                }
            }
            echo '</div>';
        } else {
            echo '<p><strong>No showtimes available for this movie.</strong></p>';
        }
    }
}, 25);

/**
 * Optional: Add a CSS style for the movie showtimes on the front-end (enqueue)
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_register_style( 'movie-showtimes-style', false );
    wp_enqueue_style( 'movie-showtimes-style' );
    $custom_css = "
        .movie-showtimes { margin: 15px 0; padding: 15px; border: 1px solid #e1e1e1; background:#fafafa; border-radius: 5px; }
        .movie-showtimes p { margin: 0 0 10px; font-size: 16px; }
        .movie-showtimes p:last-child { margin-bottom: 0; }
    ";
    wp_add_inline_style( 'movie-showtimes-style', $custom_css );
});