<?php
class BH_CF7_DBPRO_Ajax{

	protected static $initialized = false;
	
    /**
     * Initialize functions.
     *
     * @return  void
     */
    public static function initialize() {
        if ( self::$initialized ) {
            return;
        }

	    self::admin_hooks();
        self::$initialized = true;
    }


    public static function admin_hooks(){
		add_action( 'wp_ajax_nopriv_cf7wpdb_set_label', array( __CLASS__, 'cf7wpdb_set_label') );
		add_action( 'wp_ajax_cf7wpdb_set_label', array( __CLASS__, 'cf7wpdb_set_label') );

		add_action( 'wp_ajax_nopriv_cf7wpdb_export', array( __CLASS__, 'cf7wpdb_export') );
		add_action( 'wp_ajax_cf7wpdb_export', array( __CLASS__, 'cf7wpdb_export') );
		
        add_action( 'wp_ajax_nopriv_cf7wpdb_action', array( __CLASS__, 'cf7wpdb_action') );
        add_action( 'wp_ajax_cf7wpdb_action', array( __CLASS__, 'cf7wpdb_action') );
    }


    public static function cf7wpdb_set_label(){
    	global $wpdb;

    	$json = false;
    	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';

    	parse_str($_POST['data'], $data);

    	if(isset($data['set_label'])){
    		$set_label = $data['set_label'];
            $is_visible = $data['is_visible'];

			$fields = bh_cf7db_all_fields($id);
			if($fields && is_array($fields)){
				$new = array();
				foreach ($fields as $k => $field) {
				    $visible = false;
				    if( isset($is_visible[$k])) {
                        $visible = true;
                    }
					if(isset($set_label[$k]) && !empty($set_label[$k])){

						$new[$k] = array(
						    'label' => $set_label[$k],
                            'is_visible' => $visible
                        );
					}else{
						$new[$k] = array(
						    'label' => $k,
                            'is_visible' => $visible
                        );
					}
				}

				update_option('cf7_fields_'.$id, $new);
				$json['complete'] = true;
			}
		}
		
		echo wp_json_encode($json, TRUE);
		wp_die();
    }

    public static function cf7wpdb_export(){
    	$json = false;
    	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';

    	if(is_numeric($id)){
    		bh_cf7db_export_csv($id);
    		$json['complete'] = true;
    	}

		echo wp_json_encode($json, TRUE);
		wp_die();
    }

    public static function cf7wpdb_action() {

        global $wpdb;

        $data = $_REQUEST['data'];

        if( is_array($data) ) {
            foreach ($data as $key => $post_id) {
                if( is_numeric($post_id) ) {
                    $wpdb->delete( $wpdb->prefix.'cf7_wpdb', array( 'cf7_id' => $post_id ) );
                }
            }

            $json['complete'] = true;
        }

        echo wp_json_encode($json, TRUE);

        die();
    }


}