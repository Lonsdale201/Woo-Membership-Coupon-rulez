<?php
/**
 * Plugin Name: HelloWP! | Woo Membership Coupon rulez
 * Description: A simple addition to coupons that allows woo membership users to optionally force to not use coupons if the membership provides a discount.
 * Version: 1.0-beta
 * Author: Soczó Kristóf
 * Author URI: https://hellowp.io/hu/
 */

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


 class My_WooCommerce_Extension {
    public function __construct() {
        add_action('woocommerce_coupon_options', array($this, 'add_coupon_meta_field'));
        add_action('woocommerce_coupon_options_save', array($this, 'save_coupon_meta_field'));
    }

    public function add_coupon_meta_field() {
        woocommerce_wp_checkbox(array(
            'id' => 'disable_coupon_for_memberships',
            'label' => __('Disable coupon for Woo Memberships', 'woocommerce'),
            'description' => __('Your customers who have a membership and want to apply the coupon to a product that has a Woo Membership discount will not be allowed to use the coupon', 'woocommerce')
        ));
    }

    public function save_coupon_meta_field($post_id) {
        $checkbox = isset($_POST['disable_coupon_for_memberships']) ? 'yes' : 'no';
        update_post_meta($post_id, 'disable_coupon_for_memberships', $checkbox);
    }
}

include_once plugin_dir_path(__FILE__) . 'includes/class-hw-coupon-restriction.php';


new My_WooCommerce_Extension();

