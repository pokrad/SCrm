<?php

class DAONormative
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
			$table_normative = plugin_table('normative');

			$sql = "SELECT 
				id,
				normative_name,
				notes,
				created_at,
				modified_at,
				active
			FROM {$table_normative}";

			//Conditions
			$condition = array();
			if ($p_hideinactive=="checked")
			{
				array_push($condition,"active=true");
			}
			if ($p_search != '')
			{
				array_push($condition,"normative_name LIKE '%" . str_replace("'","''",$p_search) . "%'");
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

	public static function get_lookup_list($p_skip_inactive = false) 
	{
		try
		{
			$query = new \DbQuery();
			$table_normative = plugin_table('normative');

			$sql = "SELECT 
				id,
				normative_name
			FROM {$table_normative}";

			//Conditions
			$condition = array();
			if ($p_skip_inactive)
			{
				array_push($condition,"active=true");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			$sql = $sql . $condition_sql . " ORDER BY normative_name";
			
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
			$table_normative = plugin_table('normative');

			$sql = "SELECT 
				id,
				normative_name,
				notes,
				created_at,
				modified_at,
				active
			FROM {$table_normative}
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
			$table_normative = plugin_table('normative');
			$active_str = $p_active ? 'true' : 'false';
			$name_str = str_replace("'","''",$p_name);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "UPDATE {$table_normative} SET
				normative_name = '{$name_str}',
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
			$table_normative = plugin_table('normative');
			$active_str = $p_active ? 'true' : 'false';
			$name_str = str_replace("'","''",$p_name);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_normative} (
				normative_name,
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
			if (SCrmSchema::idExistsIn('customer','normative_id',$p_id))
			{
				trigger_error( "Normative is connected to customers and cannot be deleted !", ERROR );
			}

			$query = new \DbQuery();
			$table_normative = plugin_table('normative');
			$sql = "DELETE FROM {$table_normative} WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_services_list($p_normative_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_service = plugin_table('service');
			$table_normatives_to_services = plugin_table('normatives_to_services');

			$sql = "SELECT 
				SV.id,
				SV.service_name,
				NT.is_billable,
				NT.points_per_hour
			FROM {$table_service} SV JOIN {$table_normatives_to_services} NT ON NT.service_id = SV.ID
			WHERE NT.normative_id = {$p_normative_id} ORDER BY SV.service_name" ;

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_add_service_lookup_list($p_normative_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_service = plugin_table('service');
			$table_normatives_to_services = plugin_table('normatives_to_services');

			$sql = "SELECT 
				CO.id,
				CO.service_name
			FROM {$table_service} CO LEFT JOIN {$table_normatives_to_services} CU ON CU.service_id = CO.ID AND CU.normative_id = {$p_normative_id}
			WHERE CO.Active=true AND CU.normative_id IS NULL ORDER BY CO.service_name" ;

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function add_service($p_normative_id, $p_service_id)
	{
		$query = new \DbQuery();
		$table_normatives_to_services = plugin_table('normatives_to_services');
		$table_service = plugin_table('service');
		$current_timestamp = db_now();

		$sql = "INSERT INTO  {$table_normatives_to_services} (
			normative_id, 
			service_id, 
			is_billable,
			points_per_hour,
			created_at,
			modified_at
		) SELECT 
			$p_normative_id, 
			$p_service_id, 
			SER.is_billable, 
			SER.points_per_hour,  
			{$current_timestamp},
			{$current_timestamp}
		FROM {$table_service} SER WHERE SER.id = {$p_service_id}";

		$query->sql( $sql );
		return $query->execute();
	}

	public static function remove_service($p_normative_id, $p_service_id)
	{
		
		$query = new \DbQuery();
		$table_normatives_to_services = plugin_table('normatives_to_services');

		$sql = "DELETE FROM {$table_normatives_to_services} WHERE normative_id = {$p_normative_id} AND service_id={$p_service_id}";

		$query->sql( $sql );
		return $query->execute();
	}
}