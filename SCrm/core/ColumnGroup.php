<?php
class ColumnCustomerGroup extends MantisColumn
{
	public function __construct( ) 
	{
		plugin_push_current( 'SCrm' );

		$this->title = plugin_lang_get( 'table_bug_data_col_group_id');
		$this->column = 'customer_group';
        
		plugin_pop_current();
	}
	
	public function display( $p_bug, $p_columns_target ) 
	{
		plugin_push_current( 'SCrm' );

		$groupRec = DAOBugData::get_customer_group($p_bug->id);
		$group = db_fetch_array( $groupRec );
		if ($group)
		{
			echo string_display_line( $group['group_name'] );
		}
	}
}