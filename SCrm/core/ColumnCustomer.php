<?php
plugin_require_api('core/DAOBugData.php');

class ColumnCustomer extends MantisColumn
{
	
	public function __construct( ) 
	{
		plugin_push_current( 'SCrm' );
	
		$this->title = plugin_lang_get( 'table_bug_data_col_customer_id');
		$this->column = 'customer_name';
	
		plugin_pop_current();
	}
	
	/*
	DISTINCT problem, does not WORK !!!
	public $sortable = true;

	public function sortquery( $p_direction ) {
		plugin_push_current( 'SCrm' );

		$bug_table = db_get_table('bug');
		$data_table = plugin_table('bug_data');
		$table_customer = plugin_table('customer');
		$query_filter = [
			'join' => "LEFT JOIN $data_table ON $bug_table.id = $data_table.bug_id LEFT JOIN $table_customer ON $table_customer.id = $data_table.customer_id",
			'order' => "scrm_customer_name $p_direction"
		];
		plugin_pop_current();
		return $query_filter;
	}
	*/

	public function display( $p_bug, $p_columns_target ) 
	{
		plugin_push_current( 'SCrm' );
		
		$customerRec = DAOBugData::get_customer($p_bug->id);
		$customer = db_fetch_array( $customerRec );
		if ($customer)
		{
			echo string_display_line( $customer['customer_name'] );
		}
		plugin_pop_current();
	}
}
