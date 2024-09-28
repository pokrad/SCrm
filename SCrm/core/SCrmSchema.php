<?php

require_api("install_helper_functions_api.php");
require_api("database_api.php");


class SCrmSchema
{
	/*
	Return schema for tables
	*/
	public static function getSchema() 
	{
		return 
		[
			// version 1.0.0
			[
				"CreateTableSQL", 
				[
					plugin_table("group"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					group_name C(128) NOTNULL,
					notes C(2048) NULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0,
					active L NOTNULL"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("service"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					service_name C(128) NOTNULL,
					notes C(2048) NULL,
					is_billable L NOTNULL,
					points_per_hour N(10,2),
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0,
					active L NOTNULL"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("normative"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					normative_name C(128) NOTNULL,
					notes C(2048) NULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0,
					active L NOTNULL"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("normatives_to_services"), 
					"normative_id I NOTNULL UNSIGNED,
					service_id I NOTNULL UNSIGNED,
					is_billable L NOTNULL,
					points_per_hour N(10,2) NOTNULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0" 
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("customer"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					group_id I NOTNULL UNSIGNED,
					customer_name C(128) NOTNULL,
					ident_number C(30) NULL,
					email C(128) NULL,
					phone C(128) NULL,
					address C(512) NULL,
					notes C(2048) NULL,
					normative_id I NULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0,
					active L NOTNULL"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("customer_vault"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					customer_id I NOTNULL UNSIGNED,
					item_name C(255) NOTNULL,
					item_value C(8192) NOTNULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0" 
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("contact"), 
					"id I NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
					first_name C(128) NOTNULL,
					second_name C(128) NOTNULL,
					email C(128) NULL,
					phone C(128) NULL,
					address C(512) NULL,
					notes C(2048) NULL,
					created_at I NOTNULL DEFAULT 0,
					modified_at I NOTNULL DEFAULT 0,
					active L NOTNULL"
				]
			],
			[
				"CreateTableSQL",
				[
					plugin_table("customers_to_contacts"), 
					"customer_id I NOTNULL UNSIGNED,
					contact_id I NOTNULL UNSIGNED"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("bug_data"), 
					"bug_id I NOTNULL UNSIGNED,
					customer_id I NOTNULL UNSIGNED"
				]
			],
			[
				"CreateTableSQL", 
				[
					plugin_table("bug_note"), 
					"bugnote_id I NOTNULL UNSIGNED,
					time_spent I NOTNULL UNSIGNED DEFAULT 0,
					service_id I NOTNULL UNSIGNED,
					contact_id I NULL UNSIGNED,
					is_billable L NOTNULL,
					points_per_hour N(10,2) NOTNULL"
				]
			],
			[
				"CreateIndexSQL",
				[
					"idx_customer_vault_customer_id",
					plugin_table("customer_vault"), 
					"customer_id"
				]
			],
			[
				"CreateIndexSQL",
				[
					"idx_customer_to_contacts_customer_id",
					plugin_table("customers_to_contacts"), 
					"customer_id"
				]
			],
			[
				"CreateIndexSQL", 
				[
					"idx_group_id",
					plugin_table("customer"),
					"group_id"
				]
			],
			[
				"CreateIndexSQL", 
				[
					"idx_normative_id",
					plugin_table("customer"),
					"normative_id"
				]
			],
			[
				"CreateIndexSQL",
				[
					"idx_bug_note_service_id",
					plugin_table("bug_note"), 
					"service_id"
				]
			],
			[
				"CreateIndexSQL",
				[
					"idx_bug_note_contact_id",
					plugin_table("bug_note"), 
					"contact_id"
				]
			],

			//Version 1.0.1
			[
				'AddColumnSQL', [
					 plugin_table("bug_data"), 
					 "mail_hash C(32) NULL"
				 ]
			]
			,

		];
	}

	public static function buildCondition($condition_array)
	{
		if (sizeof($condition_array) == 0)
		{
			return "";
		}
		$tCounter = 0;
		$tResult = "";
		foreach ($condition_array as &$value) 
		{
			if ($tCounter == 0)
			{
				$tResult = $tResult . " WHERE " . $value;
			}
			else
			{
				$tResult = $tResult . " AND " . $value;
			}
			$tCounter++;
		}
		return $tResult;
	}

	public static function idExistsIn($table, $field, $id)
	{
		try
		{
			$query = new \DbQuery();
			$plugin_table_name = plugin_table($table);
			$sql = "SELECT 1 as record_exists FROM {$plugin_table_name} WHERE {$field} = {$id}";
			$query->sql( $sql );
			$query->set_offset(0);
			$query->set_limit(1);
			$exists_row = $query->fetch();
			if ($exists_row )
			{
				return true;
			}
			else{
				return false;
			}
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}