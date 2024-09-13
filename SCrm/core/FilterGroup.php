<?php

class FilterGroup extends MantisFilter 
{
	public function __construct() 
	{
		plugin_push_current( 'SCrm' );

		$this->title = plugin_lang_get('table_bug_data_col_group_id');
		$this->field = 'customer_group';
		$this->type = FILTER_TYPE_MULTI_INT;

		plugin_pop_current();
	}

	function display( $p_filter_value ) 
	{
		plugin_push_current( 'SCrm' );
		
		if( is_numeric ( $p_filter_value) ) {
			$group_rec = DAOGroup::get_record( (int) $p_filter_value);
			$group = db_fetch_array( $group_rec );
			plugin_pop_current();
			return string_display_line( $group['group_name'] );
		}
		else
		{
			plugin_pop_current();
			return null;
		}
	}
	
	function query( $p_filter_input ) 
	{
		$group_id = $p_filter_input[0];
		
		if ( ! is_numeric ( $group_id ) ) 
		{
			return array();
		}
		
		plugin_push_current( 'SCrm' );
		
		$bug_table = db_get_table('bug');
		$data_table = plugin_table('bug_data');
		$customer_table = plugin_table('customer');
		$query_filter = [
			'join' => "LEFT JOIN $data_table dta ON $bug_table.id = dta.bug_id LEFT JOIN $customer_table ON $customer_table.id = dta.customer_id",
			'where' => "$customer_table.group_id = $group_id",
		];
		
		plugin_pop_current();
		
		return $query_filter;
	}
	
	function options() 
	{
		plugin_push_current( 'SCrm' );
		
		$group_rec = DAOGroup::get_lookup_list();
		$options = ScrmTools::get_select_options_array ($group_rec, "id", "group_name");
		
		plugin_pop_current();
		
		return $options;
	}
}