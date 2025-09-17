<?php

/**
 * Handler class for Product Availability Checker
 *
 * Handles storing, retrieving, and validating zip code availability rules.
 *
 * @package ProductAvailabilityChecker
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PAC_Handler
{

    /**
     * Option key for storing zip rules.
     *
     * @var string
     */
    private string $option_key = 'pac_zip_rules';

    /**
     * Get all saved zip code rules.
     *
     * @return array Associative array of zip code rules.
     */
    public function get_rules(): array
    {
        return get_option($this->option_key, []);
    }

    /**
     * Save zip code rules.
     *
     * @param array $rules Associative array of rules to save.
     * @return void
     */
    public function save_rules(array $rules): void
    {
        update_option($this->option_key, $rules);
    }

    /**
     * Check availability for a given zip code.
     *
     * Provides fallback messages if message is empty.
     *
     * @param string $zip Zip code to check.
     * @return array Array containing 'status' and 'message'.
     */
    public function check_availability(string $zip): array
    {

        $zip = $this->normalize_zip($zip);
        $rules = $this->get_rules();

        if (isset($rules[$zip])) {

            $status  = $rules[$zip]['status'] ?? 'unavailable';
            $message = trim($rules[$zip]['message'] ?? '');

            // Fallback message if empty
            if (empty($message)) {
                $message = $status === 'available'
                    ? __('This product is available in your area.', 'product-availability-checker')
                    : __('Not available in your area.', 'product-availability-checker');
            }

            return [
                'status'  => $status,
                'message' => sanitize_text_field($message),
            ];
        }

        // Default response if zip code not found
        return [
            'status'  => 'unavailable',
            'message' => __('Not available in your area.', 'product-availability-checker'),
        ];
    }

    /**
     * Normalize zip code: remove spaces, dashes, and special characters.
     *
     * @param string $zip Zip code to normalize.
     * @return string Normalized zip code.
     */
    private function normalize_zip(string $zip): string
    {
        $zip = trim($zip);
        $zip = preg_replace('/\s+/', '', $zip);
        $zip = preg_replace('/[^\dA-Za-z]/', '', $zip);
        return $zip;
    }

    /**
     * Validate zip code format.
     *
     * Only alphanumeric, 5-6 characters.
     *
     * @param string $zip Zip code to validate.
     * @return bool True if valid, false otherwise.
     */
    public function is_valid_zip(string $zip): bool
    {
        $zip = $this->normalize_zip($zip);
        return (bool) preg_match('/^[0-9A-Za-z]{5,6}$/', $zip);
    }
}
