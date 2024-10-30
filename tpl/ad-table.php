        <form action="" method="GET" id="cf7d-admin-action-frm">
            <input type="hidden" name="page" value="cf7-data">
            <input type="hidden" name="fid" value="<?php echo $fid; ?>">
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce(BH_CF7_DBPRO::$table_id . '-nonce'); ?>">
            <div class="tablenav top">

                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Select bulk action', 'cf7-save2data-pro');?></label>
                    <select name="action" class="cf7-action-select" id="bulk-action-selector-top">
                        <option value="-1"><?php _e('Bulk Actions', 'cf7-save2data-pro'); ?></option>
                        <?php echo bh_cf7db_array2option($bulk_actions); ?>
                    </select>
                    <input id="doaction" name="btn_apply" class="button action btn-cf7-apply" value="<?php _e('Apply'); ?>" type="submit" />
                    <input name="btn_settings" class="button action cf7wpdb_settings" value="<?php _e('Set Fields', 'cf7-save2data-pro'); ?>" type="submit" />
                    <?php do_action('cf7wpdb_after_bulkaction_btn', $fid); ?>                    
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo (($total_results == 1) ?
                    '1 ' . __('item') :
                    $total_results . ' ' . __('items')) ?></span>
                    <span class="pagination-links">
                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg('cpage', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo;'),
                            'next_text' => __('&raquo;'),
                            'total' => $total_page,
                            'current' => $current_page
                        ));
                        ?>
                    </span>
                </div>
                <br class="clear">
            </div>

            <table class="wp-list-table widefat fixed striped posts cf7d-admin-table">
                <thead>
                    <tr>
                        <?php
                        echo '<th id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-1" /></th>';
                        foreach ($fields as $k => $v) {
                            if( !empty($v['is_visible']) || isset($v['overwrite']) ) {
                                echo '<th class="manage-column" data-key="'.$k.'">'.bh_cf7db_field_name($v['label']).'</th>';
                            }
                        }
                        ?>
                        <th class="cf7-column-date manage-column column-date sortable asc"><a href="<?php echo admin_url();?>admin.php?page=wpcf7-data&amp;post=153&amp;orderby=date&amp;order=desc"><span><?php echo esc_html_e('Date');?></span><span class="sorting-indicator"></span></a></th>
                        <?php do_action('cf7d_admin_after_heading_field'); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if( $results ) {
                        $dir = wp_upload_dir();

                        foreach ($results as $k => $rs) {
                            if ($rs->cf7_value) {
                                $cf7_value = @unserialize($rs->cf7_value);
                                unset($cf7_value[BH_CF7_DBPRO::$plugin_id . '_status']);
                            }
                            echo '<tr>';
                            echo '<th class="check-column" scope="row"><input id="cb-select-' . $rs->cf7_id . '" type="checkbox" name="cf7wpdb_row_id[]" class="cf7_id" value="' . $rs->cf7_id . '" /></th>';
                            foreach ($fields as $k2 => $v2) {
                                if( !empty($v2['is_visible']) || isset($v2['overwrite']) ) {
                                    $_value = ((isset($cf7_value[$k2])) ? $cf7_value[$k2] : '&nbsp;');

                                    if ( isset($cf7_value[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file']) ) {
                                        $_value = sprintf('<a href="%s" target="_blank">Download File</a>', $dir['baseurl'] . '/cf7_wpdb_uploads/'.$cf7_value[$k2 .'_' . BH_CF7_DBPRO::$plugin_id . '_file']);
                                    }
                                    echo '<td data-head="' . $k2 . '">' . $_value . '</td>';
                                }
                            }
                            $row_id = $k;
                            do_action('cf7d_admin_after_body_field', $fid, $row_id);
                            $date = explode(' ', $rs->cf7_date);

                            if ( '0000-00-00 00:00:00' === $rs->cf7_date ) {
                                $t_time = $h_time = __( 'Unpublished' );
                                $time_diff = 0;
                            } else {
                                $t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
                                $m_time = $rs->cf7_date;
                                $time = strtotime($m_time);

                                $time_diff = time() - $time;

                                if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
                                    $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
                                } else {
                                    $h_time = mysql2date( __( 'Y/m/d' ), $m_time );
                                }
                            }
                            echo '<td>'. esc_html__('Published') .'<br /><abbr title="' .$rs->cf7_date .'">'. $h_time .'</abbr></td>';
                            echo '</tr>';
                        }
                    }else {
                        $count_field = count($fields) + 1;
                        ?>
                        <tr>
                            <td colspan="<?php echo $count_field;?>"><?php echo esc_attr('Data is empty.', 'cf7-save2data-pro');?></td>
                        </tr>

                        <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php
                        echo '<th class="manage-column column-cb check-column"><input type="checkbox" id="cb-select-all-2" /></th>';
                        foreach ($fields as $k => $v) {
                            if( !empty($v['is_visible']) || isset($v['overwrite']) ) {
                                echo '<th class="manage-column" data-key="'.$k.'">'.bh_cf7db_field_name($v['label']).'</th>';
                            }
                        }
                        ?>
                        <th class="cf7-column-date manage-column column-date sortable asc"><a href="<?php echo admin_url();?>admin.php?page=wpcf7-data&amp;post=153&amp;orderby=date&amp;order=desc"><span><?php echo esc_html_e('Date');?></span><span class="sorting-indicator"></span></a></th>
                        <?php do_action('cf7d_admin_after_heading_field'); ?>
                    </tr>
                </tfoot>
            </table>
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action', 'cf7-save2data-pro'); ?></label>
                    <select name="action2" class="cf7-action-select" id="bulk-action-selector-bottom">
                        <option value="-1"><?php _e('Bulk Actions', 'cf7-save2data-pro'); ?></option>
                        <?php echo bh_cf7db_array2option($bulk_actions); ?>
                    </select>
                    <input id="doaction2" class="button action btn-cf7-apply" value="<?php _e('Apply'); ?>" type="submit" name="btn_apply2" />
                    <input name="btn_settings" class="button action cf7wpdb_settings" value="<?php _e('Set Fields', 'cf7-save2data-pro'); ?>" type="submit" />
                </div>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo (($total_results == 1) ?
                    '1 ' . __('item') :
                    $total_results . ' ' . __('items')) ?></span>
                </div>
                <br class="clear">
            </div> 
        </form>


<div id="price-matrix-popup" class="white-popup mfp-hide">
    <form action="" method="POST" id="frm-cf7wpdb-settings" data-id="<?php echo $fid;?>">
        <table class="table table-bordered">

            <thead>
                <tr>
                    <th><?php echo esc_html_e('Field', 'cf7-save2data-pro');?></th>
                    <th><?php echo esc_html_e('Label', 'cf7-save2data-pro');?></th>
                    <th><?php echo esc_html_e('Is Visible', 'cf7-save2data-pro');?></th>
                </tr>
            </thead>

            <tbody>
                <?php
                    $cf7_fields = bh_cf7db_all_fields($fid);

                    foreach ($cf7_fields as $k2 => $v2) {
                        $value = '';
                        if(isset($cf7_fields[$k2])){
                            $value = $cf7_fields[$k2]['label'];
                        }

                        $visible = '';
                        if( isset($cf7_fields[$k2]['is_visible']) && !empty($cf7_fields[$k2]['is_visible']) ){
                            $visible = ' checked';
                        }
                        ?>

                    <tr>
                        <td><?php echo $k2;?></td>
                        <td><input type="text" name="set_label[<?php echo $k2;?>]" value="<?php echo $value;?>"></td>
                        <td><input id="cb-select-<?php echo $k2;?>" type="checkbox" name="is_visible[<?php echo $k2;?>]" value="1"<?php echo $visible;?>></td>
                    </tr>
                <?php }?>

            </tbody>
        </table>
        <div class="act-popup">
            <input type="submit" class="button-primary cf7wpdb-settings-save" name="cf7wpdb-settings-save" value="Save">
        </div>
    </form>
</div>

<style>
    .cf7-admin-date {
        display: block;
    }
    .cf7-column-date {
        width: 127px !important;
    }
    .check-column {
        width: 50px !important;
    }
    tbody .check-column [type="checkbox"]{
        margin-left: 5px;
    }
    .act-popup {
        text-align: center;
        padding-bottom: 20px;
    }
</style>