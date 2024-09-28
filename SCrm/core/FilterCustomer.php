<?php
plugin_require_api('core/DAOCustomer.php');
plugin_require_api('core/SCrmTools.php');

class FilterCustomer extends MantisFilter
{
	public function __construct() 
	{
		plugin_push_current( 'SCrm' );

		$this->title = plugin_lang_get('table_bug_data_col_customer_id');
		$this->field = 'customer';
		$this->type = FILTER_TYPE_MULTI_INT;

		plugin_pop_current();
	}

	function display( $p_filter_value ) 
	{
		plugin_push_current( 'SCrm' );
		
		if( is_numeric ( $p_filter_value) ) 
		{
			$customerRec = DAOCustomer::get_record( (int) $p_filter_value);
			$customer = db_fetch_array( $customerRec );
			plugin_pop_current();
			return string_display_line( $customer['customer_name'] );
		}
		else
		{
			plugin_pop_current();
			return null;
		}
	}
	
	function query( $p_filter_input ) 
	{
		$customer_id = $p_filter_input[0];
		
		if ( ! is_numeric ( $customer_id ) ) 
		{
			return array();
		}
		
		plugin_push_current( 'SCrm' );
		
		$bug_table = db_get_table('bug');
		$data_table = plugin_table('bug_data');
		$query_filter = [
			'join' => "LEFT JOIN $data_table ON $bug_table.id = $data_table.bug_id",
			'where' => "$data_table.customer_id = $customer_id",
		];
		
		plugin_pop_current();
		
		return $query_filter;
	}
	
	function options() {
		plugin_push_current( 'SCrm' );
		
		$customer_rec = DAOCustomer::get_lookup_list();
		$options = ScrmTools::get_select_options_array ($customer_rec, "id", "customer_name");
		
		plugin_pop_current();
		
		return $options;
	}
}