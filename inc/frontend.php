<?php
class BH_CF7_DBPRO_Frontend{
	protected $args;
	
	function __construct() {
		add_action( 'wpcf7_mail_sent', array($this, 'wpcf7_before_send_mail'), 9999, 1 );
	}

	public static function wpcf7_before_send_mail($form_tag){
		global $wpdb;

		$table_name    = $wpdb->prefix.'cf7_wpdb';
	    $upload_dir    = wp_upload_dir();
	    $cf7_wpdb_dirname = $upload_dir['basedir'].'/'.BH_CF7_DBPRO_UP;
	    $time_now      = time();
	    $form = WPCF7_Submission::get_instance();

	    if ( $form ) {
	    	$black_list   = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_is_ajax_call','cfdb7_name', '_wpcf7_container_post');
	    	$data         = $form->get_posted_data();
	        $files        = $form->uploaded_files();
	        $uploaded_files = array();

	

	        foreach ($files as $file_key => $file) {
	            array_push($uploaded_files, $file_key);
	            copy($file, $cf7_wpdb_dirname.'/'.$time_now.'-'.basename($file));
	            
	        }

	        $form_data   = array();
	        $form_data[BH_CF7_DBPRO::$plugin_id . '_status'] = 'unread';
	        foreach ($data as $key => $d) {
	            if ( !in_array($key, $black_list ) && !in_array($key, $uploaded_files ) ) {
	                
	                $tmpD = $d;
	                
	                if ( ! is_array($d) ){

	                    $bl   = array('\"',"\'",'/','\\');
	                    $wl   = array('&quot;','&#039;','&#047;', '&#092;');

	                    $tmpD = str_replace($bl, $wl, $tmpD );
	                } 

	                $form_data[$key] = $tmpD; 
	            }
	            if ( in_array($key, $uploaded_files ) ) {
	                $form_data[$key . '_'.BH_CF7_DBPRO::$plugin_id . '_file'] = $time_now.'-'.$d;
	            }
	        }

	        /* cf7wpdb before save data. */ 
	        do_action( BH_CF7_DBPRO::$plugin_id . '_before_save_data', $form_data );
	        $form_post_id = $form_tag->id();
	        $form_value   = serialize( $form_data );
	        $form_date    = current_time('Y-m-d H:i:s');

	        $wpdb->insert( $table_name, array( 
	            'cf7_post_id' => $form_post_id,
	            'cf7_value'   => $form_value,
	            'cf7_date'    => $form_date,
	            'cf7_ip'	  => bh_cf7db_get_ip()
	        ) );

	        /* cfdb7 after save data */ 
	        $insert_id = $wpdb->insert_id;
	        do_action( BH_CF7_DBPRO::$plugin_id . '_after_save_data', $insert_id );

	        $_cf7_total = get_post_meta($form_tag->id(), '_cf7_total', true);
	        if( ! $_cf7_total ) {
	        	$_cf7_total = 0;
	        }
	        
	        update_post_meta( $form_tag->id(), '_cf7_total', ($_cf7_total + 1) );

		}
	}


}
new BH_CF7_DBPRO_Frontend();