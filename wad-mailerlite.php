<?php
/**
 * Plugin Name:       WooCommerce All Discounts - MailerLite Addon
 * Description:       Add new Rules to check if user is mailerlite subscriber
 * Version:           1.0.0
 * Author:            Mateusz Utkała
 * Author URI:        https://utkala.pl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/donmat/woo-advanced-discounts-mailerlite
 */


if(! defined( 'ABSPATH' )) {
  die;
}


define( 'WAD_MAILERLITE_VERSION', '1.0' );
define( 'WAD_MAILERLITE_URL', plugins_url('/', __FILE__) );
define( 'WAD_MAILERLITE_DIR', dirname(__FILE__) );
define( 'WAD_MAILERLITE_MAIN_FILE', 'woo-advanced-discounts-mailerlite-addon/wad-mailerlite.php' );

if( ! class_exists( 'Wad_Mailerlite' ) ) {

  class Wad_Mailerlite{

    private static $instance;

    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new Wad_Mailerlite();
            self::$instance->includes();
            self::$instance->hooks();
        }

        return self::$instance;
    }

    private function includes() {

          // Get out if WooCommerce is not active
          if ( ! class_exists( 'WC_Integration' ) )
              return;

          if ( ! class_exists( 'Woo_Mailerlite' )) {
            $error_message = 'This plugin requires <a href="https://pl.wordpress.org/plugins/woo-mailerlite/">WooCommerce – Mailerlite</a> plugin to be active!';
            deactivate_plugins(__FILE__);
            die($error_message);
          }
    }

    private function hooks() {
      add_filter("wad_operators_fields_match", array( $this, 'custom_mailerlite_fields_match'), 20, 3);
      add_filter("wad_fields_values_match", array( $this, 'custom_mailerlite_values_match'), 20, 3);
      add_filter('wad_get_discounts_conditions', array( $this, 'custom_mailerlite_contition'), 20, 1);
      add_filter('wad_is_rule_valid', array( $this, 'custom_mailerlite_is_valid'), 10, 3);
    }

    public function custom_mailerlite_fields_match($operators_match, $condition, $selected_value){
      $operators_match["mailerlite"] = '';
      return $operators_match;
    }


    public function custom_mailerlite_values_match($array_operators, $condition, $selected_value){
      $array_operators["mailerlite"] = '';
    return $array_operators;
    }


    public function custom_mailerlite_contition($conditions) {
      $conditions["mailerlite"] = "Mailerlite";
      return $conditions;
    }


    public function custom_mailerlite_is_valid($is_valid, $rule, $discount){
      $condition = $rule["condition"];

      if($condition == 'mailerlite' && is_user_logged_in()){
        $current_user = wp_get_current_user();
        $email =  $current_user->user_email;
        $res =  mailerlite_wp_get_subscriber_by_email($email);

        return !empty($res);
      } else {
        return $is_valid;
      }
    }
  }
}

function wad_mailerlite_load() {
    return Wad_Mailerlite::instance();
}
add_action( 'plugins_loaded', 'wad_mailerlite_load' );
