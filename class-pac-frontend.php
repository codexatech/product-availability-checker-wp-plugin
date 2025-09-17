<?php

/**
 * Frontend class for Product Availability Checker
 *
 * Adds zip code availability checker on single product pages.
 *
 * @package ProductAvailabilityChecker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PAC_Frontend
{
    /**
     * Handler instance.
     *
     * @var PAC_Handler
     */
    private PAC_Handler $handler;

    /**
     * Constructor.
     *
     * Hooks into WooCommerce single product page, enqueues assets, and registers AJAX.
     */
    public function __construct()
    {
        $this->handler = new PAC_Handler();

        // Add checker **inside the Add to Cart form**
        add_action('woocommerce_before_add_to_cart_button', [$this, 'add_checker']);

        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // AJAX actions
        add_action('wp_ajax_pac_check_zip', [$this, 'ajax_check']);
        add_action('wp_ajax_nopriv_pac_check_zip', [$this, 'ajax_check']);

        // Add ZIP availability to cart items
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_zip_to_cart_item'], 10, 3);
        add_filter('woocommerce_get_item_data', [$this, 'show_zip_availability_in_cart'], 10, 2);
    }

    /**
     * Enqueue frontend JS and CSS.
     */
    public function enqueue_assets(): void
    {
        wp_enqueue_style(
            'pac-frontend',
            PAC_URL . 'assets/css/frontend.css',
            [],
            '1.0'
        );

        wp_enqueue_script(
            'pac-frontend',
            PAC_URL . 'assets/js/pac-frontend.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script(
            'pac-frontend',
            'PAC_Ajax',
            [
                'url'              => admin_url('admin-ajax.php'),
                'nonce'            => wp_create_nonce('pac_nonce'),
                'unavailable_text' => __('Not available in your area.', 'product-availability-checker'),
            ]
        );
    }

    /**
     * Output frontend HTML checker inside Add to Cart form.
     */
    public function add_checker(): void
    {
?>
        <div id="pac-checker" class="pac-checker">
            <input type="text" id="pac-zip" placeholder="<?php esc_attr_e('Enter Zip Code', 'product-availability-checker'); ?>">
            <button type="button" id="pac-check-btn"><?php esc_html_e('Check Availability', 'product-availability-checker'); ?></button>
            <div id="pac-result" class="pac-result"></div>

            <!-- Hidden field to store last checked zip for Add to Cart -->
            <input type="hidden" name="pac_zip" id="pac-zip-hidden" value="">
        </div>
<?php
    }

    /**
     * Handle AJAX zip code check.
     */
    public function ajax_check(): void
    {
        check_ajax_referer('pac_nonce', 'nonce');

        $zip = isset($_POST['zip']) ? sanitize_text_field(wp_unslash($_POST['zip'])) : '';

        if (empty($zip) || ! $this->handler->is_valid_zip($zip)) {
            wp_send_json_error([
                'status'  => 'unavailable',
                'message' => __('Please enter a valid zip code.', 'product-availability-checker'),
            ]);
        }

        $result = $this->handler->check_availability($zip);

        wp_send_json_success($result);
    }

    /**
     * Add ZIP availability to cart item data.
     */
    public function add_zip_to_cart_item($cart_item_data, $product_id, $variation_id)
    {
        if (!empty($_POST['pac_zip'])) {
            $zip = sanitize_text_field($_POST['pac_zip']);
            $availability = $this->handler->check_availability($zip);

            $cart_item_data['pac_zip_availability'] = $availability;

            // Prevent cart merge
            $cart_item_data['unique_key'] = md5(microtime() . rand());
        }

        return $cart_item_data;
    }

    /**
     * Display ZIP availability in cart and checkout.
     */
    public function show_zip_availability_in_cart($item_data, $cart_item)
    {
        if (isset($cart_item['pac_zip_availability'])) {
            $availability = $cart_item['pac_zip_availability'];
            $status = $availability['status'] ?? 'unavailable';
            $message = $availability['message'] ?? '';

            $item_data[] = [
                'name'  => __('Availability', 'product-availability-checker'),
                'value' => $message,
            ];
        }

        return $item_data;
    }
}
