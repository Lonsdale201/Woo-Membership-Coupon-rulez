<?php
/**
 * Plugin Name: Woo Membership Coupon Rules
 * Plugin URI: https://github.com/Lonsdale201/Woo-Membership-Coupon-rulez
 * Description: A simple addition to coupons that allows WooCommerce membership users to optionally force to not use coupons if the membership provides a discount, or not have a selected mmship for the user based on the settings.
 * Version: 2.0
 * Author: Soczó Kristóf
 * Author URI: https://github.com/Lonsdale201?tab=repositories
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

final class My_WooCommerce_Extension {
    const MINIMUM_WOOCOMMERCE_VERSION = '7.0';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_action('plugins_loaded', [$this, 'init'], 20); 
    }

    public function init() {
        if (!$this->check_dependencies()) {
            return;
        }

        include_once plugin_dir_path(__FILE__) . 'includes/class-hw-coupon-restriction.php';
        if (class_exists('HW_Coupon_Restriction')) {
            new HW_Coupon_Restriction();
        }
        include_once plugin_dir_path(__FILE__) . 'includes/class-hw-coupon-admin.php';
    }

    private function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_woocommerce_missing']);
            return false;
        }

        if (version_compare(WC_VERSION, self::MINIMUM_WOOCOMMERCE_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_woocommerce_version']);
            return false;
        }

        if (!class_exists('WC_Memberships')) {
            add_action('admin_notices', [$this, 'admin_notice_wc_memberships_missing']);
            return false;
        }

        return true;
    }

    public function admin_notice_woocommerce_missing() {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo __('HelloWP! | Woo Membership Coupon Rules requires WooCommerce to be installed and active.', 'your-textdomain');
        echo '</p></div>';
    }

    public function admin_notice_minimum_woocommerce_version() {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo sprintf(__('HelloWP! | Woo Membership Coupon Rules requires at least WooCommerce version %s.', 'your-textdomain'), self::MINIMUM_WOOCOMMERCE_VERSION);
        echo '</p></div>';
    }

    public function admin_notice_wc_memberships_missing() {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo __('HelloWP! | Woo Membership Coupon Rules requires WooCommerce Memberships to be installed and active.', 'your-textdomain');
        echo '</p></div>';
    }

}

My_WooCommerce_Extension::instance();
