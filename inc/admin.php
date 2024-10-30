<?php
class BH_CF7_DBPRO_Admin{

    
    public function __construct() {
        add_action('admin_menu', array($this, 'cf7_register_submenu'));
        add_action( 'admin_enqueue_scripts', array($this, 'cf7_scripts_method') );
        add_action( 'cf7wpdb_after_bulkaction_btn', array($this, 'cf7wpdb_after_bulkaction_btn'), 10, 1 );
    }

    public function cf7_register_submenu(){
        $menu = add_submenu_page('wpcf7', __('Database', 'cf7-save2data-pro'), __('Database', 'cf7-save2data-pro'), 'manage_options', 'wpcf7-data', array($this, 'wpcf7_data_callback') );
        add_action('load-' . $menu, array($this, 'cf7d_form_action_callback' ));
    }
    public function cf7d_form_action_callback(){

    }
    public function wpcf7_data_callback(){
        global $wpdb;

        if (!class_exists('WPCF7_Contact_Form_List_Table')) {
            require_once WPCF7_PLUGIN_DIR . '/admin/includes/class-contact-forms-list-table.php';
        }

        $list_table = new WPCF7_Contact_Form_List_Table();
        $list_table->prepare_items();

        $first_form = bh_cf7db_get_first_form();
        $fid = ((!is_null($first_form)) ? $first_form->id() : '');

        if(isset($_GET['post']) && is_numeric($_GET['post'])){
            $fid = $_GET['post'];
        }

        $contact_form7 = get_post($fid);
        $bulk_actions = array(
            'delete' => 'Delete'
        );
        $bulk_actions = apply_filters(BH_CF7_DBPRO::$plugin_id . '_bulk_actions', $bulk_actions);

        if (!empty($fid)) {
            echo '<div class="wrap">';
            ?>
            <h1><?php echo $contact_form7->post_name . '\'s' . __(' database');?>
                <select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                <?php
                $cf7_get_form = bh_cf7db_get_form();

                $selected_cf7 = $fid;
                if( isset($_GET['post']) ) {
                    $selected_cf7 = $_GET['post'];
                }
            
                foreach ($cf7_get_form as $key => $form) {
                    ?>
                    <option value="<?php echo admin_url();?>admin.php?page=wpcf7-data&post=<?php echo $form->ID;?>"<?php selected( $form->ID, $selected_cf7 ); ?>><?php echo $form->post_title;?></option>
                    <?php
                }
                ?>
                </select>
            </h1>
            <?php
            $query = sprintf("SELECT COUNT(*) FROM `".$wpdb->prefix.BH_CF7_DBPRO::$table_id."` WHERE `cf7_post_id` = ".$fid." ORDER BY `cf7_id` DESC");
            $total_records = $wpdb->get_var($query);
            $current_page = isset($_GET['cpage']) ? abs((int)$_GET['cpage']) : 1;
            $items_per_page = apply_filters(BH_CF7_DBPRO::$plugin_id . '_per_page', 4);
            $total_page = ceil($total_records / $items_per_page);


            if ($current_page > $total_page){
                $current_page = $total_page;
            }
            else if ($current_page < 1){
                $current_page = 1;
            }

            $start = ($current_page - 1) * $items_per_page;


            $query = sprintf("SELECT * FROM `".$wpdb->prefix.BH_CF7_DBPRO::$table_id."` WHERE `cf7_post_id` = ".$fid." ORDER BY `cf7_id` DESC LIMIT $start, $items_per_page");
            $results = $wpdb->get_results($query);
            $total_results = $wpdb->num_rows;


            $fields = bh_cf7db_all_fields($fid);
            $items_per_page = apply_filters(BH_CF7_DBPRO::$plugin_id . '_per_page', 2);

            include_once BH_CF7_DBPRO_PATH .'tpl/ad-table.php';
        }else{
            echo 'trong';
        }
    }

    public function cf7wpdb_after_bulkaction_btn($id){
        ?>
        <div class="cf7wpdb-group-action">
            <select id="cf7wpdb-exports" name="cf7wpdb-exports" data-fid="<?php echo $id;?>">
                <option value="-1"><?php echo esc_html_e('Export to...', 'cf7-save2data-pro');?></option>
                <option value="csv"><?php echo esc_html_e('CSV', 'cf7-save2data-pro');?></option>
            </select>
            <input name="btn_exports" class="button action cf7wpdb_export" value="<?php _e('Export', 'cf7-save2data-pro'); ?>" type="submit" />
        </div>
        <?php
    }

    public function cf7_scripts_method($hooks){
        global $wpdb;

        $screen = get_current_screen();

        if ( strpos( $screen->id, 'wpcf7' ) === false && strpos( $screen->id, 'wpcf7' ) === false ) {
            return;
        }

        wp_enqueue_style( 'magnific-popup-admin', BH_CF7_DBPRO_URL . 'assets/css/magnific-popup.css' );
        wp_enqueue_style( 'datatables-admin', BH_CF7_DBPRO_URL . 'assets/css/datatables.min.css' );
        wp_enqueue_style( 'wpcf7-data-admin', BH_CF7_DBPRO_URL . 'assets/css/admin.css' );
        
        wp_enqueue_script( 'magnific-popup-admin', BH_CF7_DBPRO_URL . 'assets/js/jquery.magnific-popup.min.js', array(), false, true);
        wp_enqueue_script( 'jquery.fileDownload', BH_CF7_DBPRO_URL . 'assets/js/jquery.fileDownload.js', array(), false, true);

        
        wp_enqueue_script( 'wpcf7-data-admin', BH_CF7_DBPRO_URL . 'assets/js/admin.js', array(), false, true);


        $get_results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = %s", 'wpcf7_contact_form') );
        $cf7_value = array();
        if($get_results){
            foreach ($get_results as $k => $cf7) {
                $cf7_total = get_post_meta($cf7->ID, '_cf7_total', true); 
                if( ! $cf7_total ) {
                    $cf7_total = 0;
                }
                $cf7_value['cf7'.$cf7->ID] = $cf7_total;
            }
        }

        wp_localize_script( 'wpcf7-data-admin', 'cf7wpdb', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'admin_url' => admin_url( 'admin.php?page=wpcf7-data' ),
            'data' => $cf7_value,
            'label' => array(
                'data' => __('Database', 'cf7-save2data-pro'),
                'count' => __('Count', 'cf7-save2data-pro')
            )
        ));
    }

}
new BH_CF7_DBPRO_Admin();