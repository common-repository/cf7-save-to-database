<?php
/*
  Plugin Name: Contact Form 7 - Save to Database
  Plugin URI: https://azmarket.net/item/contact-form-7-save-to-database-pro
  Description: Contact Form 7 - Save to Database is a plugin for WordPress allows you save all submitted from contact form 7 to database and display in Contact > Database menu, and you can view it anytime.
  Version: 1.0
  Author: AzMarket
  Author URI: https://azmarket.net/
 */

define('BH_CF7_DBPRO_PATH', plugin_dir_path( __FILE__ ));
define('BH_CF7_DBPRO_URL', plugin_dir_url( __FILE__ ));
define('BH_CF7_DBPRO_UP', 'cf7_wpdb_uploads');

class BH_CF7_DBPRO {

    static $plugin_id = 'bh_cf7db';
    static $table_id = 'bh_cf7db';

    static $types = array();
    /**
     * Variable to hold the initialization state.
     *
     * @var  boolean
     */
    protected static $initialized = false;
    
    /**
     * Initialize functions.
     *
     * @return  void
     */
    public static function initialize() {
        // Do nothing if pluggable functions already initialized.
        if ( self::$initialized ) {
            return;
        }

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
           add_action( 'admin_notices', array( __CLASS__, 'bh_cf7db_install_admin_notice') );
        }else{

          include BH_CF7_DBPRO_PATH .'inc/functions.php';
          include BH_CF7_DBPRO_PATH .'inc/pagination.class.php';
          if( is_admin() ){
              include BH_CF7_DBPRO_PATH .'inc/admin.php';
          }else{
              include BH_CF7_DBPRO_PATH .'inc/frontend.php';
          }

          if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX) ) {
              require_once BH_CF7_DBPRO_PATH . 'inc/ajax.php';
              BH_CF7Data_Ajax::initialize();
          }
        }
        self::$initialized = true;
    }

    public static function bh_cf7db_plugin_activation() {
      global $wpdb;

      $table_name = $wpdb->prefix.'cf7_bhdb';
      if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

          $charset_collate = $wpdb->get_charset_collate();
          $sql = "CREATE TABLE $table_name (
              cf7_id bigint(20) NOT NULL AUTO_INCREMENT,
              cf7_post_id bigint(20) NOT NULL,
              cf7_value longtext NOT NULL,
              cf7_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              cf7_ip varchar(100) NOT NULL,
              PRIMARY KEY  (cf7_id)
          ) $charset_collate;";

          require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
          dbDelta( $sql );
      }

      $upload_dir    = wp_upload_dir();
      $cf7_wpdb_dirname = $upload_dir['basedir'].'/'.BH_CF7_DBPRO_UP;
      if ( ! file_exists( $cf7_wpdb_dirname ) ) {
          wp_mkdir_p( $cf7_wpdb_dirname );
      }
      
    }

    /**
     * Method Featured.
     *
     * @return  array
     */
    public static function bh_cf7db_install_admin_notice() {?>
        <div class="error">
            <p><?php _e( 'Contact Form 7 plugin is not activated. Please install and activate it to use for plugin <strong>Contact Form 7 Save to Database</strong>.', 'cf7-save2data-pro' ); ?></p>
        </div>
        <?php    
    }

}


if ( ! function_exists('bh_cf7db_init') ) {

  add_action('plugins_loaded', 'bh_cf7db_init');

  function bh_cf7db_init(){
      BH_CF7_DBPRO::initialize();
  }

  register_activation_hook( __FILE__, array( 'BH_CF7_DBPRO', 'bh_cf7db_plugin_activation') );
}
