<?php

class DAONormativeService
{
	/*
	DAO Operations for normatives
	*/
	public static function get_list($p_normative_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_normative = plugin_table('normative');
			$table_normatives_to_services = plugin_table('normatives_to_services');
			$table_services = plugin_table('service');

			$sql = "SELECT 
				CS.normative_id,
				CS.service_id,
				CU.normative_name,
				SE.service_name
				CS.is_billable,
				CS.points_per_hour,
				CS.created_at,
				CS.modified_at,
				SE.is_billable AS global_is_billable,
				SE.points_per_hour AS global_points_per_hour
			FROM {$table_normatives_to_services} CS
				LEFT JOIN $table_services SE ON SE.ID = CS.service_id
				LEFT JOIN $table_normative CU ON CU.ID = CS.normative_id 
			WHERE CS.normative_id = {$p_normative_id} ORDER BY SE.service_name ASC";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_record(
		$p_normative_id,
		$p_service_id
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_normative = plugin_table('normative');
			$table_normatives_to_services = plugin_table('normatives_to_services');
			$table_services = plugin_table('service');

			$sql = "SELECT 
				CS.normative_id,
				CS.service_id,
				CU.normative_name,
				SE.service_name,
				CS.is_billable,
				CS.points_per_hour,
				CS.created_at,
				CS.modified_at,
				SE.is_billable AS global_is_billable,
				SE.points_per_hour AS global_points_per_hour
			FROM {$table_normatives_to_services} CS
				LEFT JOIN $table_services SE ON SE.ID = CS.service_id
				LEFT JOIN $table_normative CU ON CU.ID = CS.normative_id 
			WHERE CS.normative_id = {$p_normative_id} AND CS.service_id = {$p_service_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function update_record(
		$p_normative_id,
		$p_service_id,
		$p_is_billable,
		$p_points_per_hour
	) 
	{
		try
		{
			if (!SCrmSchema::idExistsIn('normative','id',$p_normative_id))
			{
				trigger_error( "Invalid normative !", ERROR );
			}
			if (!SCrmSchema::idExistsIn('service','id',$p_service_id))
			{
				trigger_error( "Invalid service !", ERROR );
			}

			$query = new \DbQuery();
			$p_table = plugin_table('normatives_to_services');
			$p_is_billable_str = $p_is_billable ? 'true' : 'false';
			$current_timestamp = db_now();

			$sql = "UPDATE {$p_table} SET
				is_billable= {$p_is_billable_str},
				points_per_hour = {$p_points_per_hour},
				modified_at = {$current_timestamp}
			WHERE normative_id = {$p_normative_id} AND service_id = {$p_service_id}";

			$query->sql( $sql  );
			return $query->execute();
		} 
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function insert_record(
		$p_normative_id,
		$p_service_id,
		$p_is_billable,
		$p_points_per_hour
	) 
	{
		try
		{
			if (!SCrmSchema::idExistsIn('normative','id',$p_normative_id))
			{
				trigger_error( "Invalid normative !", ERROR );
			}
			if (!SCrmSchema::idExistsIn('service','id',$p_service_id))
			{
				trigger_error( "Invalid service !", ERROR );
			}

			$query = new \DbQuery();
			$table_normatives_to_services = plugin_table('normatives_to_services');
			$is_billable_str = $p_is_billable ? 'true' : 'false';
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_normatives_to_services} (
				normative_id,
				service_id,
				is_billable,
				points_per_hour,
				created_at,
				modified_at
			)
			VALUES (
				{$p_normative_id},
				{$p_service_id},
				{$is_billable_str},
				{$p_points_per_hour},
				{$current_timestamp},
				{$current_timestamp}
			)";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function delete_record(
		$p_normative_id,
		$p_service_id
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_normatives_to_services = plugin_table('normatives_to_services');
			$sql = "DELETE FROM {$table_normatives_to_services} WHERE normative_id = {$p_normative_id} AND service_id = {$p_service_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}