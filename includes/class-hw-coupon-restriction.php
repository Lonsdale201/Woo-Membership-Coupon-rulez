<?php
// includes/class-hw-coupon-restriction.php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class HW_Coupon_Restriction {

    public function __construct() {
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_coupon'), 10, 2);
        add_filter('woocommerce_coupon_error', array($this, 'custom_coupon_error_message'), 10, 3);
    }

    public function validate_coupon($is_valid, $coupon) {
    
        if (!$is_valid) {
            return false;
        }
    
        if (!is_user_logged_in()) {
            return $is_valid;
        }
    
        $disable_coupon_for_memberships = get_post_meta($coupon->get_id(), 'disable_coupon_for_memberships', true);
        $selected_memberships = get_post_meta($coupon->get_id(), 'selected_memberships', true);
        $user_memberships = wc_memberships_get_user_memberships();
    
        $active_user_memberships = array_filter($user_memberships, function($membership) {
            return isset($membership->plan) && $membership->is_active();
        });
    
        if ('yes' === $disable_coupon_for_memberships && !empty($active_user_memberships)) {
            $coupon->error_code = 101;
            return false;
        }
    
        if (!empty($selected_memberships) && !empty($active_user_memberships)) {
            $active_membership_ids = wp_list_pluck($active_user_memberships, 'plan_id');
            $selected_valid = array_intersect($selected_memberships, $active_membership_ids);
    
            if (empty($selected_valid)) {
                $coupon->error_code = 102;
                return false;
            }
        }
    
        return $is_valid;
    }
    
    

    public function custom_coupon_error_message($err, $err_code, $coupon) {
        $default_message = 'You can\'t use this coupon';
    
        $custom_err_code = isset($coupon->error_code) ? $coupon->error_code : null;
    
        if ($custom_err_code === 101) {
            $message = get_post_meta($coupon->get_id(), 'disable_coupon_for_memberships_message', true);
            return !empty($message) ? $message : $default_message;
        } elseif ($custom_err_code === 102) {
            $message = get_post_meta($coupon->get_id(), 'disable_coupon_for_selected_memberships_message', true);
            return !empty($message) ? $message : $default_message;
        }
    
        return $err;
    }
    
}

new HW_Coupon_Restriction();
