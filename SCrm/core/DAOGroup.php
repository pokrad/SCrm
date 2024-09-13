<?php

class DAOGroup  
{
	/*
	DAO Operations for groups
	*/
	public static function get_list(
		$p_sort_field = "grpup_name",
		$p_sort_dir = "ASC",
		$p_hideinactive = "",
		$p_search = "",
		$p_page = 0
	) 
	{
		try{
			$query = new \DbQuery();
			$table_group = plugin_table('group');

			$sql = "SELECT 
				id,
				group_name,
				created_at,
				modified_at,
				active
			FROM {$table_group}";

			//Conditions
			$condition = array();
			if ($p_hideinactive=="checked")
			{
				array_push($condition,"active=true");
			}
			if ($p_search != '')
			{
				array_push($condition,"group_name LIKE '%" . str_replace("'","''",$p_search) . "%'");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			//Order
			$order_sql = "";
			if ($p_sort_field != "")
			{
				$order_sql = " ORDER BY " . $p_sort_field . " " . $p_sort_dir;
			}

			$sql = $sql . $condition_sql . $order_sql ;
			
			$query->sql( $sql );
			if ($p_page>0)
			{
				$rows_per_page = config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE);
				$query->set_offset(($p_page-1)*$rows_per_page);
				$query->set_limit($rows_per_page);
			}

			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_lookup_list(
		$p_skip_inactive = false
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_group = plugin_table('group');

			$sql = "SELECT 
				id,
				group_name
			FROM {$table_group}";

			//Conditions
			$condition = array();
			if ($p_skip_inactive)
			{
				array_push($condition,"active=true");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			$sql = $sql . $condition_sql . " ORDER BY group_name";
			
			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_record($p_id) 
	{
		try{
			$query = new \DbQuery();
			$table_group = plugin_table('group');

			$sql = "SELECT 
				id,
				group_name,
				notes,
				created_at,
				modified_at,
				active
			FROM {$table_group}
			WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function update_record(
		$p_id,
		$p_name,
		$p_notes,
		$p_active
	) 
	{
		try{
			$query = new \DbQuery();
			$table_group = plugin_table('group');
			$active_str = $p_active ? 'true' : 'false';
			$name_str = str_replace("'","''",$p_name);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "UPDATE {$table_group} SET
				group_name = '{$name_str}',
				notes = '{$notes_str}',
				modified_at = {$current_timestamp},
				active = {$active_str}
			WHERE id = {$p_id}";

			$query->sql( $sql  );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function insert_record(
		$p_name,
		$p_notes,
		$p_active
	) 
	{
		try{
			$query = new \DbQuery();
			$table_group = plugin_table('group');
			$active_str = $p_active ? 'true' : 'false';
			$name_str = str_replace("'","''",$p_name);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_group} (
				group_name,
				notes,
				created_at,
				modified_at,
				active
			)
			VALUES (
				'{$name_str}',
				'{$notes_str}',
				{$current_timestamp},
				{$current_timestamp},
				{$active_str}		
			)";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function delete_record($p_id) 
	{
		try
		{
			if (SCrmSchema::idExistsIn('customer','group_id',$p_id))
			{
				trigger_error( "Group is connected to customers and cannot be deleted !", ERROR );
			}

			$query = new \DbQuery();
			$table_group = plugin_table('group');
			$sql = "DELETE FROM {$table_group} WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}