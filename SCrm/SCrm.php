<?php

class SCrmPlugin extends MantisPlugin 
{
    public const CFG_KEY_THEME_ENABLED = 'plugin_SCrm_cfg_theme_enabled';
    public const CFG_KEY_MANAGE_TABLES_TRESHOLD = 'plugin_SCrm_cfg_manage_tables_treshold';
    public const CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD = 'plugin_SCrm_cfg_manage_customer_vault';
    public const CFG_KEY_CUSTOMER_REQUIRED = 'plugin_SCrm_cfg_customer_required';
    public const CFG_KEY_SERVICE_REQUIRED = 'plugin_SCrm_cfg_serice_required';
    public const CFG_KEY_TIME_SPENT_REQUIRED = 'plugin_SCrm_cfg_time_spent_required';
    public const CFG_KEY_TABLE_ROWS_PER_PAGE = 'plugin_SCrm_cfg_table_rows_per_page';
    public const CFG_KEY_PRINT_ISSUE_COLUMN_LIST = 'plugin_SCrm_cfg_print_issie_column_list';

    public const CFG_KEY_MAIL_IMPORT_HOST = 'plugin_SCrm_cfg_mail_import_host';
    public const CFG_KEY_MAIL_IMPORT_PORT = 'plugin_SCrm_cfg_mail_import_port';
    public const CFG_KEY_MAIL_IMPORT_TYPE = 'plugin_SCrm_cfg_mail_import_type';
    public const CFG_KEY_MAIL_IMPORT_USER = 'plugin_SCrm_cfg_mail_import_user';
    public const CFG_KEY_MAIL_IMPORT_PWD = 'plugin_SCrm_cfg_mail_import_pwd';
    public const CFG_KEY_MAIL_IMPORT_SSL = 'plugin_SCrm_cfg_mail_import_SSL';


    public static function get_version()
    {
        return "1.0.1";
    }


    function register() 
    {
        plugin_require_api( 'core/SCrmSchema.php' );

        $this->name = plugin_lang_get( 'title' );                   # Proper name of plugin
        $this->description = plugin_lang_get( 'description' );      # Short description of the plugin
        $this->page = 'main_page';                                          # Default plugin page

        $this->version = SCrmPlugin::get_version();                         # Plugin version string
        $this->requires = [                                                 # Plugin dependencies
            'MantisCore' => '2.0',                                          # Should always depend on an appropriate version of MantisBT
        ];

        $this->author = 'Darko Prenosil';                                   # Author/team name
        $this->contact = 'dprenosil@google.com';                            # Author/team e-mail address
        $this->url = 'https://github.com/pokrad/SCrm/tree/main/SCrm';       # Support webpage
    }

    function hooks() 
    {
        return 
        [
            'EVENT_LAYOUT_RESOURCES' => 'add_css',
			'EVENT_ACCOUNT_PREF_UPDATE_FORM' => 'account_update_form',
			'EVENT_ACCOUNT_PREF_UPDATE' => 'account_update',
            'EVENT_MENU_MAIN' => 'menu',
            "EVENT_REPORT_BUG_FORM_TOP" => "event_report_bug_form_top",
            "EVENT_UPDATE_BUG_FORM" => "event_update_bug_form",
            "EVENT_UPDATE_BUG_STATUS_FORM" => "event_update_bug_status_form",
            "EVENT_UPDATE_BUG" => "event_update_bug",
            "EVENT_REPORT_BUG" => "event_report_bug",
            "EVENT_VIEW_BUG_DETAILS" => "event_view_bug_details",
            "EVENT_BUG_DELETED" => "event_bug_deleted",
            "EVENT_FILTER_COLUMNS" => "event_filter_columns",
            "EVENT_FILTER_FIELDS" => "event_filter_fields",
            "EVENT_VIEW_BUGNOTE" => "event_view_bugnote",
            "EVENT_BUGNOTE_ADD" => "event_bugnote_add",
            "EVENT_BUGNOTE_ADD_FORM" => "event_bugnote_add_form",
            "EVENT_BUGNOTE_EDIT_FORM" => "event_bugnote_edit_form",
            "EVENT_BUGNOTE_EDIT" => "event_bugnote_edit",
            "EVENT_BUGNOTE_DELETED" => "event_bugnote_deleted",
            "EVENT_VIEW_BUGNOTES_END" => "event_view_bugnotes_end",
        ];
    }

	function init() 
    {
        plugin_require_api('core/SCrmSchema.php');
        plugin_require_api('core/SCrmHooks.php');

        plugin_require_api('core/ColumnCustomer.php');
        plugin_require_api('core/ColumnGroup.php');
        plugin_require_api('core/FilterCustomer.php');
        plugin_require_api('core/FilterGroup.php');

        plugin_require_api('core/DAOBugNote.php');
        plugin_require_api('core/DAOService.php');

        if (!config_is_set(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD))
        {
            config_set(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD, UPDATER);
            config_set(SCrmPlugin::CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD, UPDATER);
            config_set(SCrmPlugin::CFG_KEY_THEME_ENABLED, false);
            config_set(SCrmPlugin::CFG_KEY_CUSTOMER_REQUIRED, false);
            config_set(SCrmPlugin::CFG_KEY_SERVICE_REQUIRED, false);
            config_set(SCrmPlugin::CFG_KEY_TIME_SPENT_REQUIRED, false);
            config_set(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE, 25);
            config_set(SCrmPlugin::CFG_KEY_PRINT_ISSUE_COLUMN_LIST, 'id,category_id,summary,severity,status,date_submitted,last_updated,reporter_id,handler_id,scrm_customer_name');
        }
    }

	function schema() 
    {
        return SCrmSchema::getSchema();
	}

    /*HOOKS impl*/

    /*Menu events*/
    function menu() 
    {
        if (access_has_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD)))
        {
            //Icon names: https://fontawesome.com/v4/icons/
            $menu[] = 
            [
                'title' => $this->name,
                'url' => plugin_page( 'main_page'),
                'icon' => 'fa-vcard-o'
            ];
        }
        return $menu;
    }

    /*Columns and fields*/
	function event_filter_columns() 
    {
		return [
            'ColumnCustomer', 
            'ColumnGroup'
        ];
	}
	
	function event_filter_fields() 
    {
		return [
            'FilterCustomer',
            'FilterGroup'
        ];
	}

    /*css theme events*/
	function add_css( $p_event )
	{
		if( $this->is_theme_enabled() )
        {
			echo '<link rel="stylesheet" type="text/css" href="' . plugin_file( 'SCrm.css' ) . '" />\n';
        }
	}

	function is_theme_enabled()
	{
		return auth_is_user_authenticated() && config_get( self::CFG_KEY_THEME_ENABLED, false, auth_get_current_user_id(), ALL_PROJECTS );
	}

	function account_update_form( $p_event, $p_user_id )
	{
        $darkModeLabel = plugin_lang_get('configuration_theme');
		echo '<tr>' .
            '<td class="category">' .$darkModeLabel.'</td>' .
            '<td>' .
            '<label class="inline">'.
            '<input id="'. self::CFG_KEY_THEME_ENABLED .'" class="ace input-sm" type="checkbox" name="' . self::CFG_KEY_THEME_ENABLED . '" value="1" ' . ( $this->is_theme_enabled() ? 'checked' : '' ) . '/>' .
            '<span class="lbl"></span>'.
            '</label>'.
            '</td>' .
        '</tr>';
	}

	function account_update( $p_event, $p_user_id )
	{
		config_set( self::CFG_KEY_THEME_ENABLED, gpc_get_bool( self::CFG_KEY_THEME_ENABLED, false ), $p_user_id, ALL_PROJECTS );
	}

    /*
    bug data events
    */
	public function event_report_bug_form_top ( $event, $project_id ) 
    {
        SCrmHooks::event_report_bug_form_top(false);
	}

	public function event_update_bug_form ( $event, $bug_id ) 
    {
        SCrmHooks::event_report_bug_form_top(false, $bug_id);
	}
	
	public function event_update_bug_status_form( $event, $bug_id ) 
    {
        SCrmHooks::event_report_bug_form_top(false, $bug_id);
	}
    
	public function event_update_bug( $p_event, $p_bug_data_old,  $p_bug_data_new ) 
    {
        SCrmHooks::event_update_bug($p_event, $p_bug_data_old,  $p_bug_data_new);
	}

    public function event_report_bug( $p_event, $p_bug_data_new, $p_bug_id ) 
    {
        SCrmHooks::event_update_bug($p_event, null,  $p_bug_data_new);
    }

	public function event_bug_deleted( $p_event, $p_bug_id ) 
    {
        SCrmHooks::event_delete_bug($p_event, $p_bug_id);
	}

	public function event_view_bug_details( $p_event, $p_bug_id ) 
    {
        SCrmHooks::event_view_bug_details($p_event, $p_bug_id);
	}

    /*
    bug note events
    */
    public function event_view_bugnote($p_event, $p_bug_id, $p_activitiy_id, $is_private)
    {
        SCrmHooks::event_view_bugnote($p_event, $p_bug_id, $p_activitiy_id, $is_private);
    }

    public function event_bugnote_edit_form($p_event, $p_bug_id, $p_bugnote_id)
    {
        SCrmHooks::event_bugnote_edit_form($p_event, $p_bug_id, $p_bugnote_id);
    }

    public function event_bugnote_add_form($p_event, $p_bug_id)
    {
        SCrmHooks::event_bugnote_edit_form($p_event, $p_bug_id, -1);
    }

    public function event_bugnote_add($p_event, $p_bug_id, $p_bugnote_id, $param_array)
    {
        SCrmHooks::event_bugnote_edit($p_event, $p_bug_id, $p_bugnote_id);
    }

    public function event_bugnote_edit($p_event, $p_bug_id, $p_bugnote_id)
    {
        SCrmHooks::event_bugnote_edit($p_event, $p_bug_id, $p_bugnote_id);
    }

    public function event_bugnote_deleted($p_event, $p_bug_id, $p_bugnote_id)
    {
        SCrmHooks::event_bugnote_deleted($p_event, $p_bug_id, $p_bugnote_id);
    }

    public function event_view_bugnotes_end($p_event, $p_bug_id)
    {
        SCrmHooks::event_view_bugnotes_end($p_event, $p_bug_id);
    }
}
