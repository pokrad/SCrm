<?php

use Mantis\Exceptions\ClientException;

class DAOService
{
	/*
	DAO Operations for services
	*/
	public static function get_list(
		$p_sort_field = "service_name",
		$p_sort_dir = "ASC",
		$p_hideinactive = "",
		$p_search = "",
		$p_page = 0
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_service = plugin_table('service');

			$sql = "SELECT 
				id,
				service_name,
				is_billable,
				points_per_hour,
				created_at,
				modified_at,
				active
			FROM {$table_service}";

			//Conditions
			$condition = array();
			if ($p_hideinactive=="checked")
			{
				array_push($condition,"active=true");
			}
			if ($p_search != '')
			{
				array_push($condition,"service_name LIKE '%" . str_replace("'","''",$p_search) . "%'");
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
			$table_service = plugin_table('service');

			$sql = "SELECT 
				id,
				service_name
			FROM {$table_service}";

			//Conditions
			$condition = array();
			if ($p_skip_inactive)
			{
				array_push($condition,"active=true");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			$sql = $sql . $condition_sql . " ORDER BY service_name";
			
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
		try
		{
			$query = new \DbQuery();
			$table_service = plugin_table('service');

			$sql = "SELECT 
				id,
				service_name,
				notes,
				is_billable,
				points_per_hour,
				created_at,
				modified_at,
				active
			FROM {$table_service}
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
		$p_is_billable,
		$p_points_per_hour,
		$p_active
	) 
	{
		$query = new \DbQuery();
		$table_service = plugin_table('service');
		$active_str = $p_active ? 'true' : 'false';
		$is_billable_str = $p_is_billable ? 'true' : 'false';
		$name_str = str_replace("'","''",$p_name);
		$notes_str = str_replace("'","''",$p_notes);
		$current_timestamp = db_now();

		$sql = "UPDATE {$table_service} SET
			service_name = '{$name_str}',
			notes = '{$notes_str}',
			is_billable = {$is_billable_str},
			points_per_hour = {$p_points_per_hour},
			modified_at = {$current_timestamp},
			active = {$active_str}
		WHERE id = {$p_id}";

		$query->sql( $sql  );
		return $query->execute();
	}

	public static function insert_record(
		$p_name,
		$p_notes,
		$p_is_billable,
		$p_points_per_hour,
		$p_active
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_service = plugin_table('service');
			$active_str = $p_active ? 'true' : 'false';
			$is_billable_str = $p_is_billable ? 'true' : 'false';
			$name_str = str_replace("'","''",$p_name);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_service} (
				service_name,
				notes,
				is_billable,
				points_per_hour,
				created_at,
				modified_at,
				active
			)
			VALUES (
				'{$name_str}',
				'{$notes_str}',
				{$is_billable_str},
				{$p_points_per_hour},
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
			if (SCrmSchema::idExistsIn('normatives_to_services','service_id',$p_id))
			{
				trigger_error( "Service is connected to normatives and cannot be deleted !", ERROR );
			}
			if (SCrmSchema::idExistsIn('bug_note','service_id',$p_id))
			{
				trigger_error( "Service is connected to bug note and cannot be deleted !", ERROR );
			}

			$query = new \DbQuery();
			$table_service = plugin_table('service');
			$sql = "DELETE FROM {$table_service} WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}