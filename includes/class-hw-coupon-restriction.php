<?php
// includes/class-hw-coupon-restriction.php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class HW_Coupon_Restriction {

    public function __construct() {
        add_filter('woocommerce_coupon_is_valid', array($this, 'validate_coupon'), 10, 2);
    }

    public function validate_coupon($is_valid, $coupon) {
        if (!is_user_logged_in()) {
            return $is_valid;
        }
    
        $user_memberships = wc_memberships_get_user_memberships();
        if (empty($user_memberships)) {
            return $is_valid;
        }
    
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($this->product_has_membership_discount($product, $user_memberships)) {
                if ('yes' === get_post_meta($coupon->get_id(), 'disable_coupon_for_memberships', true)) {
                    return false; 
                }
                break;
            }
        }
    
        return $is_valid;
    }
    
    

    private function product_has_membership_discount($product, $user_memberships) {
        foreach ($user_memberships as $membership) {
            if (wc_memberships_product_has_member_discount($product->get_id())) {
                return true;
            }
        }
        return false;
    }

}

new HW_Coupon_Restriction();

