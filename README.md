# Product Availability Checker

Manage WooCommerce product availability by zip code.

## Installation

1. Upload the `product-availability-checker` folder to `/wp-content/plugins/`.
2. Activate the plugin via the 'Plugins' menu.
3. Go to **WooCommerce > Settings > Availability** to manage zip code rules.

## Usage

- Add individual zip codes with status (Available/Unavailable) and optional message.
- On single product pages, customers can check availability.
- If unavailable, the "Add to Cart" button is disabled.

## Security

- All inputs sanitized/validated.
- AJAX uses nonces to prevent CSRF.
- Admin actions require `manage_woocommerce` capability.
