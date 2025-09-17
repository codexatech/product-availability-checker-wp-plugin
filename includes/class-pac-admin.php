<?php

/**
 * Admin class for Product Availability Checker
 *
 * Handles WooCommerce settings tab and zip code rules management.
 *
 * @package ProductAvailabilityChecker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PAC_Admin
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
     * Adds WooCommerce tab, hooks, and enqueues assets.
     */
    public function __construct()
    {
        $this->handler = new PAC_Handler();

        // Add custom WooCommerce settings tab
        add_filter('woocommerce_settings_tabs_array', [$this, 'add_tab'], 50);

        // Display content inside our custom tab
        add_action('woocommerce_settings_tabs_pac', [$this, 'settings_tab']);

        // Save settings when WooCommerce form is submitted
        add_action('woocommerce_update_options_pac', [$this, 'save_settings']);

        // Enqueue admin CSS and JS
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add a custom tab under WooCommerce > Settings.
     *
     * @param array $tabs Existing WooCommerce tabs.
     * @return array Modified tabs array.
     */
    public function add_tab(array $tabs): array
    {
        $tabs['pac'] = __('Availability', 'product-availability-checker');
        return $tabs;
    }

    /**
     * Render settings UI for the Availability tab.
     */
    public function settings_tab(): void
    {
        $rules = $this->handler->get_rules();
?>
        <div class="pac-admin-wrapper">
            <div class="pac-admin-header">
                <h2><?php esc_html_e('Zip Code Availability', 'product-availability-checker'); ?></h2>
                <button type="button" class="button button-secondary" id="pac-add-row">
                    <?php esc_html_e('+ Add New Zip Code', 'product-availability-checker'); ?>
                </button>
            </div>

            <table class="form-table widefat" id="pac-zip-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Zip Code', 'product-availability-checker'); ?></th>
                        <th><?php esc_html_e('Status', 'product-availability-checker'); ?></th>
                        <th><?php esc_html_e('Message', 'product-availability-checker'); ?></th>
                        <th><?php esc_html_e('Action', 'product-availability-checker'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rules)) : ?>
                        <?php foreach ($rules as $zip => $data) : ?>
                            <tr>
                                <td>
                                    <input type="text" name="pac_rules[<?php echo esc_attr($zip); ?>][zip]"
                                        value="<?php echo esc_attr($zip); ?>" />
                                </td>
                                <td>
                                    <select name="pac_rules[<?php echo esc_attr($zip); ?>][status]">
                                        <option value="available" <?php selected($data['status'], 'available'); ?>>
                                            <?php esc_html_e('Available', 'product-availability-checker'); ?>
                                        </option>
                                        <option value="unavailable" <?php selected($data['status'], 'unavailable'); ?>>
                                            <?php esc_html_e('Unavailable', 'product-availability-checker'); ?>
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="pac_rules[<?php echo esc_attr($zip); ?>][message]"
                                        value="<?php echo esc_attr($data['message']); ?>" />
                                </td>
                                <td>
                                    <button type="button" class="button remove-zip">
                                        <?php esc_html_e('Remove', 'product-availability-checker'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="no-rules">
                            <td colspan="4" style="text-align:center; padding:12px;">
                                No record found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php
    }

    /**
     * Save zip code availability rules.
     *
     * Sanitizes inputs, checks capabilities, and updates the option.
     */
    public function save_settings(): void
    {
        if (!empty($_POST['pac_rules']) && current_user_can('manage_woocommerce')) {
            $new_rules = [];

            foreach ($_POST['pac_rules'] as $zip => $data) {
                if (!empty($data['zip'])) {
                    $clean_zip = sanitize_text_field($data['zip']);

                    $new_rules[$clean_zip] = [
                        'status'  => in_array($data['status'], ['available', 'unavailable'], true) ? $data['status'] : 'unavailable',
                        'message' => sanitize_text_field($data['message'] ?? ''),
                    ];
                }
            }

            $this->handler->save_rules($new_rules);
        }
    }

    /**
     * Enqueue admin CSS and JS for settings tab.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_assets(string $hook): void
    {
        // Only enqueue on WooCommerce settings page
        if ('woocommerce_page_wc-settings' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'pac-admin',
            PAC_URL . 'assets/css/admin.css',
            [],
            '1.0'
        );

        wp_enqueue_script(
            'pac-admin',
            PAC_URL . 'assets/js/admin.js',
            ['jquery'],
            '1.0',
            true
        );

        wp_localize_script(
            'pac-admin',
            'PAC_Admin',
            [
                'remove_text' => __('Remove', 'product-availability-checker'),
            ]
        );
    }
}
