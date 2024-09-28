<?php

class SCrmTools  
{
    static function print_main_menu( $p_page = '' ) 
    {
        $pages = array();

        $pages['main_page.php'] = array( 'url'   => plugin_page('main_page'), 'label' => '');
        $pages['edit_services.php'] = array( 'url'   => plugin_page('edit_services'), 'label' => plugin_lang_get('main_menu_services') );
        $pages['edit_normatives.php'] = array( 'url'   => plugin_page('edit_normatives'), 'label' => plugin_lang_get('main_menu_normatives') );
        $pages['edit_contacts.php'] = array( 'url'   => plugin_page('edit_contacts'), 'label' => plugin_lang_get('main_menu_contacts') );
        $pages['edit_groups.php'] = array( 'url'   => plugin_page('edit_groups'), 'label' =>  plugin_lang_get('main_menu_groups') );
        $pages['edit_customers.php'] = array( 'url'   => plugin_page('edit_customers'), 'label' => plugin_lang_get('main_menu_customers') );
        $pages['view_email.php'] = array( 'url'   => plugin_page('view_email'), 'label' => plugin_lang_get('main_menu_import_emails'));
        $pages['view_reports.php'] = array( 'url'   => plugin_page('view_reports'), 'label' => plugin_lang_get('main_menu_reports'));
        if (current_user_is_administrator()){
            $pages['config_page.php'] = array( 'url'   => plugin_page('config_page'), 'label' => plugin_lang_get('main_menu_configuration'));
        }
        if (plugin_page($p_page) == '')
        {
            $p_page = '';
        }
        echo '<div class="hidden-print">';
        print_menu( $pages, $p_page);
        echo '</div>';
    }

    static function format_mail_link($mail, $iconOnly=false)
    {
        if ($mail != ''){
            if ($iconOnly)
            {
                return "&nbsp;&nbsp;<a href=\"mailto:{$mail}\"><i class=\"fa fa-envelope ace-icon btn btn-primary btn-white btn-round btn-sm\"></i></a>";
            }
            else
            {
                return "<a href=\"mailto:{$mail}\"><i class=\"padding-6 fa fa-envelope ace-icon btn btn-primary btn-white btn-round btn-sm\"></i></a>&nbsp;&nbsp;{$mail}";
            }
        }
    }

    static function format_phone_link($phone,$iconOnly=false)
    {
        if ($phone != ''){
            if ($iconOnly)
            {
                return "&nbsp;&nbsp;<a href=\"tel:{$phone}\"><i class=\"fa fa-phone ace-icon btn btn-primary btn-white btn-round btn-sm\"></i></a>";
            }
            else
            {
                return "<a href=\"tel:{$phone}\"><i class=\"fa fa-phone ace-icon btn btn-primary btn-white btn-round btn-sm\"></i></a>&nbsp;&nbsp;{$phone}";
            }
        }
    }

    static function format_select($p_query, $p_selectAttributes, $p_id_field, $p_display_field, $p_current_value, $p_enable_empty_value=true)
    {
		$res ="<select {$p_selectAttributes}>";
        $res .= SCrmTools::format_select_options($p_query, $p_id_field, $p_display_field, $p_current_value, $p_enable_empty_value=true);
        $res .= "</select>";
        return $res;
    }

    static function format_select_options($p_query, $p_id_field, $p_display_field, $p_current_value, $p_enable_empty_value=true)
    {
        $res = "";
        if ($p_enable_empty_value)
        {
            $res .= "<option value=\"\"></option>";
        }
        while( $row = db_fetch_array( $p_query ) ) 
        {
            $id_field = (int)$row[$p_id_field];
            $display_field = $row[$p_display_field];
            if ($id_field != $p_current_value)
            {
                $res .= "<option value=\"{$id_field}\">{$display_field}</option>";
            }
            else{
                $res .= "<option value=\"{$id_field}\" selected>{$display_field}</option>";
            }
        }				
        return $res;								
    }

    static function get_select_options_array ($p_query, $p_id_field, $p_display_field)
    {
        $options = [];
        while( $row = db_fetch_array( $p_query ) ) 
        {
            $options[$row[$p_id_field]] = $row[$p_display_field];
        }	
        return 	$options;										
    }

    static function format_checkmark($p_is_checked)
    {
        if ($p_is_checked)
        {
            return "<i class=\"fa fa-check fa-lg\"></i>";
        }
        else 
        {
            return "";
        }
    }
}