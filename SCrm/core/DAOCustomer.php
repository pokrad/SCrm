<?php

class DAOCustomer
{
	/*
	DAO Operations for customers
	*/
	public static function get_list(
		$p_sort_field = "customer_name",
		$p_sort_dir = "ASC",
		$p_hideinactive = "",
		$p_search = "",
		$p_page=0
	) 
	{
		try
		{
			$query = new DbQuery();
			$table_customer = plugin_table('customer');
			$table_group = plugin_table('group');

			$sql = "SELECT 
				CU.id,
				CU.group_id,
				GR.group_name,
				CU.customer_name,
				CU.ident_number,
				CU.email,
				CU.phone,
				CU.address,
				CU.notes,
				CU.normative_id,
				CU.created_at,
				CU.modified_at,
				CU.active
			FROM {$table_customer} CU LEFT JOIN $table_group GR ON GR.ID = CU.group_id";

			$p_search_str = str_replace("'","''",$p_search);

			//Conditions
			$condition = array();
			if ($p_hideinactive=="checked")
			{
				array_push($condition,"GR.active=true");
			}
			if ($p_search != '')
			{
				array_push($condition,"CU.customer_name LIKE '%{$p_search_str}%' OR CU.ident_number LIKE '%{$p_search_str}%' OR CU.email LIKE '%{$p_search_str}%' OR GR.group_name LIKE '%{$p_search_str}%'");
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
			$query = new DbQuery();
			$table_customer = plugin_table('customer');

			$sql = "SELECT 
				id,
				CONCAT(customer_name, ' - ', address) AS customer_name 
			FROM {$table_customer}";

			//Conditions
			$condition = array();
			if ($p_skip_inactive)
			{
				array_push($condition,"active=true");
			}
			$condition_sql = SCrmSchema::buildCondition($condition);

			$sql = $sql . $condition_sql . " ORDER BY customer_name";
			
			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_contacts_list($p_customer_id) 
	{
		try
		{
			$query = new DbQuery();
			$table_customer = plugin_table('contact');
			$table_customers_to_contacts = plugin_table('customers_to_contacts');

			$sql = "SELECT 
				CO.id,
				CO.first_name,
				CO.second_name,
				CONCAT (CO.first_name, ' ' , CO.second_name) as contact_name,
				CO.email,
				CO.phone,
				CO.active
			FROM {$table_customer} CO JOIN {$table_customers_to_contacts} CU ON CU.contact_id = CO.ID
			WHERE CU.customer_id = {$p_customer_id} ORDER BY CO.first_name, CO.second_name" ;

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_add_contacts_lookup_list($p_customer_id) 
	{
		try
		{
			$query = new DbQuery();
			$table_customer = plugin_table('contact');
			$table_customers_to_contacts = plugin_table('customers_to_contacts');

			$sql = "SELECT 
				CO.id,
				CONCAT(second_name , ' ' , first_name, ' - ', email) AS contact_name
			FROM {$table_customer} CO LEFT JOIN {$table_customers_to_contacts} CU ON CU.contact_id = CO.ID AND CU.customer_id = {$p_customer_id}
			WHERE CO.Active=true AND CU.customer_id IS NULL ORDER BY CO.first_name, CO.second_name" ;

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
	
	public static function add_contact($p_customer_id, $p_contact_id)
	{
		$query = new DbQuery();
		$table_customers_to_contacts = plugin_table('customers_to_contacts');

		$sql = "INSERT INTO  {$table_customers_to_contacts} (customer_id, contact_id) VALUES ($p_customer_id, $p_contact_id)";

		$query->sql( $sql );
		return $query->execute();
	}

	public static function remove_contact($p_customer_id, $p_contact_id)
	{
		$query = new DbQuery();
		$table_customers_to_contacts = plugin_table('customers_to_contacts');

		$sql = "DELETE FROM {$table_customers_to_contacts} WHERE customer_id = {$p_customer_id} AND contact_id={$p_contact_id}";

		$query->sql( $sql );
		return $query->execute();
	}

	public static function get_record($p_id) 
	{
		try
		{
			$query = new DbQuery();
			$table_customer = plugin_table('customer');
			$table_group = plugin_table('group');
			$table_normative = plugin_table('normative');

			$sql = "SELECT 
				CU.id,
				CU.group_id,
				GR.group_name,
				CU.customer_name,
				CU.ident_number,
				CU.email,
				CU.phone,
				CU.address,
				CU.notes,
				CU.normative_id,
				NO.normative_name,
				CU.created_at,
				CU.modified_at,
				CU.active
			FROM {$table_customer} CU 
			LEFT JOIN $table_group GR ON GR.ID = CU.group_id
			LEFT JOIN $table_normative NO ON NO.ID = CU.normative_id
			WHERE CU.id = {$p_id}";

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
		$p_group_id,
		$p_normative_id,
		$p_customer_name,
		$p_ident_number,
		$p_email,
		$p_phone,
		$p_address,
		$p_notes,
		$p_active
	) 
	{
		try
		{
			if (!SCrmSchema::idExistsIn('group','id',$p_group_id))
			{
				trigger_error( "Invalid group !", ERROR );
			}

			$query = new DbQuery();
			$table_customer = plugin_table('customer');
			$active_str = $p_active ? 'true' : 'false';
			$customer_name_str = str_replace("'","''",$p_customer_name);
			$ident_number_str = str_replace("'","''",$p_ident_number);
			$email_str = str_replace("'","''",$p_email);
			$phone_str = str_replace("'","''",$p_phone);
			$address_str = str_replace("'","''",$p_address);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "UPDATE {$table_customer} SET
				group_id = {$p_group_id},
				normative_id = {$p_normative_id},
				customer_name = '{$customer_name_str}',
				ident_number= '{$ident_number_str}',
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
		$p_group_id,
		$p_normative_id,
		$p_customer_name,
		$p_ident_number,
		$p_email,
		$p_phone,
		$p_address,
		$p_notes,
		$p_active
	) 
	{
		try
		{
			if (!SCrmSchema::idExistsIn('group','id',$p_group_id))
			{
				trigger_error( "Invalid group !", ERROR );
			}

			$query = new DbQuery();
			$table_customer = plugin_table('customer');
			$active_str = $p_active ? 'true' : 'false';
			$customer_name_str = str_replace("'","''",$p_customer_name);
			$ident_number_str = str_replace("'","''",$p_ident_number);
			$email_str = str_replace("'","''",$p_email);
			$phone_str = str_replace("'","''",$p_phone);
			$address_str = str_replace("'","''",$p_address);
			$notes_str = str_replace("'","''",$p_notes);
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_customer} (
				group_id,
				normative_id,
				customer_name,
				ident_number,
				email,
				phone,
				address,
				notes,
				created_at,
				modified_at,
				active
			)
			VALUES (
				{$p_group_id},
				{$p_normative_id},
				'{$customer_name_str}',
				'{$ident_number_str}',
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
			if (SCrmSchema::idExistsIn('customers_to_contacts','customer_id',$p_id))
			{
				trigger_error( "Customer has connected contacts and cannot be deleted !", ERROR );
			}
			if (SCrmSchema::idExistsIn('customer_ault','customer_id',$p_id))
			{
				trigger_error( "Customer has connected vaults and cannot be deleted !", ERROR );
			}


			$query = new DbQuery();
			$table_customer = plugin_table('customer');
			$sql = "DELETE FROM {$table_customer} WHERE id = {$p_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_customer_id_by_email($p_email)
	{
		$query = new DbQuery();
		$table_customer = plugin_table('customer');
		$table_contact = plugin_table('contact');
		$table_customers_to_contacts = plugin_table('customers_to_contacts');
		$email_str = str_replace("'","''",$p_email);


		$sql = "SELECT CU.id 
		FROM {$table_customer} CU
		JOIN {$table_customers_to_contacts} CUCO ON CUCO.customer_id = CU.ID
		JOIN {$table_contact} CO ON CO.ID = CUCO.contact_id
		WHERE CU.email = '$email_str' OR CO.email = '$email_str'
		ORDER BY CU.id";
		
		$query->sql( $sql );
		$query->set_limit(1);
		$rec=$query->execute();

		if (!$rec)
		{
			return null;
		}
		else
		{
			$resStat = db_fetch_array( $rec );
			return $resStat['id'];
		}
	}
}