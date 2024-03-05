<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class HW_Coupon_Admin {
    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_action('woocommerce_coupon_options', array($this, 'add_coupon_meta_fields'), 10, 0);
        add_action('woocommerce_coupon_options_save', array($this, 'save_coupon_meta_fields'), 10, 1);
    }

    public function add_coupon_meta_fields() {
        // Disable coupon for Woo Memberships checkbox
        woocommerce_wp_checkbox(array(
            'id' => 'disable_coupon_for_memberships',
            'label' => __('Disable coupon for Woo Memberships', 'your-textdomain'),
            'desc_tip' => true, 
            'description' => __('If you tick this box, the user will NOT BE ABLE to use this coupon if they have any membership. Note that the Select membership field is an independent setting from this checkbox setting.', 'your-textdomain')
        ));
        
        $membership_plans = function_exists('wc_memberships_get_membership_plans') ? wc_memberships_get_membership_plans() : [];
        $saved_memberships = get_post_meta(get_the_ID(), 'selected_memberships', true);
        if (!is_array($saved_memberships)) {
            $saved_memberships = []; 
        }
    
        echo '<p class="form-field"><label for="selected_memberships">' . __('Select Memberships', 'your-textdomain') . '</label>';
        echo '<select id="selected_memberships" name="selected_memberships[]" class="wc-enhanced-select" multiple="multiple" style="width: 100%;" data-placeholder="' . esc_attr__('Choose memberships...', 'your-textdomain') . '">';
        foreach ($membership_plans as $plan) {
            $selected = in_array($plan->get_id(), $saved_memberships) ? ' selected="selected"' : '';
            echo '<option value="' . esc_attr($plan->get_id()) . '"' . $selected . '>' . esc_html($plan->get_name()) . '</option>';
        }
        echo '</select></p>';

        woocommerce_wp_textarea_input(array(
            'id' => 'disable_coupon_for_memberships_message',
            'label' => __('Disable coupon for Woo Memberships message', 'your-textdomain'),
            'description' => __('Message displayed when the coupon is not usable due to a membership.', 'your-textdomain'),
            'desc_tip' => true,
        ));
        
        woocommerce_wp_textarea_input(array(
            'id' => 'disable_coupon_for_selected_memberships_message',
            'label' => __('Disable coupon for selected memberships message', 'your-textdomain'),
            'description' => __('Invalid coupon usage message if the user does not have any of the memberships specified in the setting.', 'your-textdomain'),
            'desc_tip' => true,
        ));
    }
     
    public function save_coupon_meta_fields($post_id) {
    
        $disable_coupon_for_memberships = isset($_POST['disable_coupon_for_memberships']) ? 'yes' : 'no';
        update_post_meta($post_id, 'disable_coupon_for_memberships', $disable_coupon_for_memberships);
        

        if (isset($_POST['selected_memberships'])) {

            $selected_memberships = is_array($_POST['selected_memberships']) ? $_POST['selected_memberships'] : explode(',', $_POST['selected_memberships']);
            $cleaned_memberships = array_map('sanitize_text_field', $selected_memberships);
            update_post_meta($post_id, 'selected_memberships', $cleaned_memberships);
        } else {
            delete_post_meta($post_id, 'selected_memberships');
        }

        if (isset($_POST['disable_coupon_for_memberships_message'])) {
            update_post_meta($post_id, 'disable_coupon_for_memberships_message', sanitize_textarea_field($_POST['disable_coupon_for_memberships_message']));
        }
        
        if (isset($_POST['disable_coupon_for_selected_memberships_message'])) {
            update_post_meta($post_id, 'disable_coupon_for_selected_memberships_message', sanitize_textarea_field($_POST['disable_coupon_for_selected_memberships_message']));
        }
    } 
    
}

HW_Coupon_Admin::instance();
