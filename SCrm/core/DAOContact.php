<?php

class DAOContact
{
	/*
	DAO Operations for contacts
	*/
	public static function get_list(
		$p_sort_field = "first_name",
		$p_sort_dir = "ASC",
		$p_hideinactive ="",
		$p_search ="",
		$p_page=0
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_contact = plugin_table('contact');

			$sql = "SELECT 
				id,
				first_name,
				second_name,
				email,
				phone,
				address,
				notes,
				created_at,
				modified_at,
				active
			FROM {$table_contact}";

			$p_search_str = str_replace("'","''",$p_search);

			//Conditions
			$condition = array();
			if ($p_hideinactive=="checked")
			{
				array_push($condition,"active=true");
			}
			if ($p_search != '')
			{
				array_push($condition,"first_name LIKE '%{$p_search_str}%' OR second_name LIKE '%{$p_search_str}%' OR email LIKE '%{$p_search_str}%'");
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
			$table_contact = plugin_table('contact');

			$sql = "SELECT 
				id,
				CONCAT(second_name , ' ' , first_name, ' - ', email) AS contact_name
			FROM {$table_contact}";

			//Conditions
			$condition = array();
			if ($p_skip_inactive)
			{
				array_push($condition,"active=true");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			$sql = $sql . $condition_sql . " ORDER BY second_name ,first_name";
			
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
			$table_contact = plugin_table('contact');

			$sql = "SELECT 
				id,
				first_name,
				second_name,
				email,
				phone,
				address,
				notes,
				created_at,
				modified_at,
				active
			FROM {$table_contact}
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
		$p_first_name,
		$p_second_name,
		$p_email,
		$p_phone,
		$p_address,
		$p_notes,
		$p_active
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_contact = plugin_table('contact');
			$active_str = $p_active ? 'true' : 'false';
			$fist_name_str = str_replace("'","''",$p_first_name);
			$second_name_str = str_replace("'","''",$p_second_name);
			$email_str = str_replace("'","''",$p_email);
			$phone_str = str_replace("'","''",$p_phone);
			$address_str = str_replace("'","''",$p_address);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "UPDATE {$table_contact} SET
				first_name = '{$fist_name_str}',
				second_name= '{$second_name_str}',
				email= '{$email_str}',
				phone= '{$phone_str}',
				address= '{$address_str}',
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
		$p_first_name,
		$p_second_name,
		$p_email,
		$p_phone,
		$p_address,
		$p_notes,
		$p_active
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_contact = plugin_table('contact');
			$active_str = $p_active ? 'true' : 'false';
			$fist_name_str = str_replace("'","''",$p_first_name);
			$second_name_str = str_replace("'","''",$p_second_name);
			$email_str = str_replace("'","''",$p_email);
			$phone_str = str_replace("'","''",$p_phone);
			$address_str = str_replace("'","''",$p_address);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();


			$sql = "INSERT INTO {$table_contact} (
				first_name,
				second_name,
				email,
				phone,
				address,
				notes,
				created_at,
				modified_at,
				active
			)
			VALUES (
				'{$fist_name_str}',
				'{$second_name_str}',
				'{$email_str}',
				'{$phone_str}',
				'{$address_str}',
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
			if (SCrmSchema::idExistsIn('customers_to_contacts','contact_id',$p_id))
			{
				trigger_error( "Contacts is connected to customers and cannot be deleted !", ERROR );
			}
			if (SCrmSchema::idExistsIn('bug_note','contact_id',$p_id))
			{
				trigger_error( "Contacts is connected to bug note and cannot be deleted !", ERROR );
			}

			$query = new \DbQuery();
			$table_contact = plugin_table('contact');
			$sql = "DELETE FROM {$table_contact} WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}