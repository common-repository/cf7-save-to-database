<?php
if( ! function_exists( 'bh_cf7db_get_first_form' ) ) {
    function bh_cf7db_get_first_form()
    {
        $forms = WPCF7_ContactForm::find();
        $form = array();
        foreach ($forms as $k => $v) {
            $form = $v;
            break;
        }
        return $form;
    }
}


if ( ! function_exists('bh_cf7db_array2option') ) {
    function bh_cf7db_array2option($arr)
    {
        $html = '';
        foreach ($arr as $k => $v) {
            $html .= '<option value="'.$k.'">'.$v.'</option>';
        }
        return $html;
    }
}

if ( ! function_exists('bh_cf7db_db_fields') ) {
    function bh_cf7db_db_fields($fid)
    {
        global $wpdb;
        $fields = array();

        $sql = sprintf("SELECT `cf7_value` FROM `".$wpdb->prefix.BH_CF7_DBPRO::$table_id."` WHERE cf7_post_id = %d GROUP BY `cf7_value`", $fid);
        $query = $wpdb->get_row($sql);


        if($cf7_fields = get_option('cf7_fields_'.$fid)){
            $fields = $cf7_fields;
        }else {
            if ($query) {
                $data = @unserialize($query->cf7_value);
                unset($data[BH_CF7_DBPRO::$plugin_id . '_status']);
                foreach ($data as $key => $value) {
                    $fields[$key] = $key;
                }
            } else {
                $post = WPCF7_ContactForm::get_instance($fid);
                $fields = bh_cf7db_filter_shortcode($post->prop('form'));
            }
        }
        return $fields;
    }
}

if ( ! function_exists('bh_cf7db_all_fields') ) {
    function bh_cf7db_all_fields($fid){
        $post = WPCF7_ContactForm::get_instance($fid);
        $datas = bh_cf7db_filter_shortcode($post->prop('form'));


        if($datas) {
            $cf7_fields = get_option('cf7_fields_'.$fid);
            if($cf7_fields = get_option('cf7_fields_'.$fid)){
                $fields = $cf7_fields;

                foreach ($datas as $k => $data) {
                    if( !isset($fields[$data]) ) {
                        $fields[$data] = array(
                            'label' => $data,
                            'is_visible' => false
                        );
                    }

                }


            }else {
                $fields = array();
                foreach ($datas as $k => $data) {
                    $fields[$k] = array(
                        'label' => $data,
                        'is_visible' => false,
                        'overwrite' => false
                    );
                }
            }
        }

        return $fields;
    }
}

if ( ! function_exists('bh_cf7db_get_entry') ) {
    function bh_cf7db_get_entry($fid, $entry_ids = '', $order_by = '')
    {
        global $wpdb;
        if (empty($order_by)) {
            $order_by = '`cf7_date` DESC';
        }
        $query = sprintf("SELECT * FROM `".$wpdb->prefix.BH_CF7_DBPRO::$table_id."` WHERE `cf7_post_id` = %d ORDER BY " . $order_by, $fid);
        $data = $wpdb->get_results($query);
        return $data;
    }
}

if ( ! function_exists('bh_cf7db_field_name') ) {
    function bh_cf7db_field_name($field)
    {
        return $field;
    }
}

if ( ! function_exists('bh_cf7db_sortdata') ) {
    function bh_cf7db_sortdata($data)
    {
        $data_sorted = array();
        $upload_dir    = wp_upload_dir();
        $cf7_wpdb_dirname = $upload_dir['basedir'].'/'.BH_CF7_DBPRO_UP;

        foreach ($data as $k => $v) {
            $value = @unserialize($v->cf7_value);
            unset($value['cf7wpdb_status']);
            foreach ($value as $key => $val) {
                if(file_exists($cf7_wpdb_dirname.'/'.$val) && $val){
                    $data_sorted[$v->cf7_id][$key] = $upload_dir['baseurl'].'/'.BH_CF7_DBPRO_UP.'/'.$val; 
                }else{
                    $data_sorted[$v->cf7_id][$key] = $val;
                }
            }
        }
        return $data_sorted;
    }
}

if ( ! function_exists('bh_cf7db_export_csv') ) {
    function bh_cf7db_export_csv($fid, $id_export = ''){
        $checked = $_POST['checked'];
        $fields = bh_cf7db_all_fields($fid);
        $data = bh_cf7db_get_entry($fid, $id_export, 'cf7_date desc');
        $data_sorted = bh_cf7db_sortdata($data);
        $dir = wp_upload_dir();

        if( is_array($fields) ) {
            header("Content-type: text/x-csv");
            header("Content-Disposition: attachment; filename=cf7wpdb-data-".gmdate("d-m-Y").".csv");

            $fp = fopen('php://output', 'w');
            fputs($fp, "\xEF\xBB\xBF");

            $new_fields = array();
            foreach ($fields as $k => $field) {
                if( !empty($field['is_visible']) || isset($field['overwrite']) ) {
                    $new_fields[] = $field['label'];
                }
            }
            fputcsv($fp, $new_fields);

            foreach ($data_sorted as $k => $v) {
                $temp_value = array();
                if($checked && is_array($checked) && !empty($checked)){
                    if(in_array($k, $checked)){
                        foreach ($fields as $k2 => $v2) {
                            if( !empty($v2['is_visible']) || isset($v2['overwrite']) ) {
                                if ( isset($v[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file']) ) {
                                    $temp_value[] = $dir['baseurl'] . '/cf7_wpdb_uploads/'.$v[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file'];
                                }else {
                                    $temp_value[] = ((isset($v[$k2])) ? $v[$k2] : '');
                                }
                            }
                        }
                    }
                }

                if(!$checked && !is_array($checked) && empty($checked)){
                    foreach ($fields as $k2 => $v2) {
                        if( !empty($v2['is_visible']) || isset($v2['overwrite']) ) {
                            if ( isset($v[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file']) ) {
                                $temp_value[] = $dir['baseurl'] . '/cf7_wpdb_uploads/'.$v[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file'];
                            }else {
                                $temp_value[] = ((isset($v[$k2])) ? $v[$k2] : '');
                            }
                        }
                    }
                }

                fputcsv($fp, $temp_value);

            }
        }

        fclose($fp);

        exit();
    }
}

if ( ! function_exists('bh_cf7db_filter_shortcode') ) {
    function bh_cf7db_filter_shortcode($input_lines) {
        $new = array();
        if( preg_match_all("/\[(.*?)\]/", $input_lines, $output_array) ) {
            if( count($output_array) == 2 ) {
                foreach ($output_array[1] as $key => $value) {
                    if ( strpos($value, 'submit') === false ) {
                        $value = explode(' ', $value);
                        $new[$value[1]] = $value[1];
                    }

                }
            }
        }
        return $new;
    }
}

if ( ! function_exists('bh_cf7db_get_ip') ) {
    function bh_cf7db_get_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}

if ( ! function_exists('bh_cf7db_get_form') ) {
    function bh_cf7db_get_form() {
       global $wpdb;
       
        $query = sprintf("SELECT * FROM `".$wpdb->posts."` WHERE `post_type` = %s ORDER BY %s DESC", "'wpcf7_contact_form'", 'post_title');
        $data = $wpdb->get_results($query);
        return $data; 
    }
}