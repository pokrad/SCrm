<?php

class DAOBugData
{
    static function get_record( $p_bug_id ) 
    {
        try
        {
            $query = new \DbQuery();
            $table_bug_data = plugin_table('bug_data');

            $sql = "SELECT 
                bug_id,
                customer_id
            FROM {$table_bug_data} WHERE bug_id = $p_bug_id";

            $query->sql( $sql );
            return $query->execute();
        }
        catch (Exception $e) 
        {
            trigger_error( $e->getMessage(), ERROR );
        }
    }

    public static function update_record(
        $p_bug_id, 
        $p_customer_id
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_bug_data = plugin_table('bug_data');

            if (SCrmSchema::idExistsIn('bug_data', 'bug_id', $p_bug_id))
            {
                $sql = "UPDATE {$table_bug_data} SET
                    customer_id = $p_customer_id
                WHERE bug_id = {$p_bug_id}";
            }
            else
            {
                $sql = "INSERT INTO {$table_bug_data} (
                    bug_id, 
                    customer_id
                ) 
                VALUES (
                    $p_bug_id, 
                    $p_customer_id
                )";
            }

			$query->sql( $sql  );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function delete_record($p_bug_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_bug_data = plugin_table('bug_data');
			$sql = "DELETE FROM {$table_bug_data} WHERE id = {$p_bug_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

    static function get_customer( $p_bug_id ) 
    {
        try
        {
            $query = new \DbQuery();
            $table_bug_data = plugin_table('bug_data');
            $table_customer = plugin_table('customer');

            $sql = "SELECT 
                CU.id,
                CU.group_id,
                CU.customer_name,
                CU.ident_number,
                CU.email,
                CU.phone,
                CU.address,
                CU.notes,
                CU.created_at,
                CU.modified_at,
                CU.active
            FROM {$table_bug_data} BG LEFT JOIN {$table_customer} CU ON CU.id = BG.customer_id WHERE bug_id = $p_bug_id";

            $query->sql( $sql );
            return $query->execute();
        }
        catch (Exception $e) 
        {
            trigger_error( $e->getMessage(), ERROR );
        }
    }

    static function get_customer_group( $p_bug_id ) 
    {
        try
        {
            $query = new \DbQuery();
            $table_bug_data = plugin_table('bug_data');
            $table_group = plugin_table('group');

            $sql = "SELECT 
				GR.id,
				GR.group_name,
				GR.created_at,
				GR.modified_at,
				GR.active
            FROM {$table_bug_data} BG LEFT JOIN {$table_group} GR ON GR.id = BG.group_id WHERE bug_id = $p_bug_id";

            $query->sql( $sql );
            return $query->execute();
        }
        catch (Exception $e) 
        {
            trigger_error( $e->getMessage(), ERROR );
        }
    }

	public static function get_service_data($p_bug_id, $p_service_id)
	{
		$query = new \DbQuery();
		$table_bug_data = plugin_table('bug_data');
		$table_customer = plugin_table('customer');
		$table_normative_service = plugin_table('normatives_to_services');

		$sql = "SELECT 
			TS.is_billable,
			TS.points_per_hour
		FROM {$table_bug_data} BUG 
		INNER JOIN {$table_customer} CU on CU.ID = BUG.customer_id
		INNER JOIN {$table_normative_service} TS ON TS.normative_id = CU.normative_id AND TS.service_id = {$p_service_id}
        WHERE BUG.bug_id = {$p_bug_id}";

		$query->sql( $sql );
		$service_data_rec = $query->execute();
		$exists_row = db_fetch_array( $service_data_rec );
		if ($exists_row )
		{
			return $exists_row;
		}
		else
		{
            $p_table_service = plugin_table('service');
			$sql = "SELECT 
				TS.is_billable,
				TS.points_per_hour
			FROM {$p_table_service} TS WHERE TS.id = {$p_service_id}";

			$query->sql( $sql );
			$service_data_rec = $query->execute();
			$exists_row = db_fetch_array( $service_data_rec );
            return $exists_row;
		}
	}

	public static function get_services_list_for_bug($p_bug_id)
	{
		$query = new \DbQuery();
        $table_bug_data = db_get_table('bug');
        $table_user = db_get_table('user');
        $table_bug_note = db_get_table('bugnote');
        $table_bug_note_text = db_get_table('bugnote_text');

        $table_scrm_bug_note = plugin_table('bug_note');
        $table_scrm_service = plugin_table('service');

		$sql = "SELECT 
            BT.id,
            BNT.id AS bugnote_id,
            BNT.reporter_id,
            UT.username AS reporter_name,
            BNT.note_attr,
            BNTT.note AS note_text,
            BNT.last_modified,
            BNT.date_submitted,
            PBNT.service_id,
            ST.service_name,
            PBNT.time_spent,
            PBNT.is_billable,
            PBNT.points_per_hour,
            ROUND(PBNT.points_per_hour/60.0*PBNT.time_spent,2) as total_points
        FROM 
            {$table_bug_data} BT
            INNER JOIN {$table_bug_note} BNT ON BNT.bug_id = BT.id
            INNER JOIN {$table_user} UT ON UT.id = BNT.reporter_id 
            LEFT JOIN {$table_bug_note_text} BNTT ON BNTT.id = BNT.bugnote_text_id
            LEFT JOIN {$table_scrm_bug_note} PBNT ON PBNT.bugnote_id = BNT.id
            LEFT JOIN {$table_scrm_service} ST ON ST.id = PBNT.service_id
        WHERE BT.id = {$p_bug_id}";

		$query->sql( $sql );
		return $query->execute();
	}

    public static function get_detailed_bugs_list(
		$p_sort_field = "date_submitted",
		$p_sort_dir = "DESC",
		$p_search = "",
		$p_page=0,
        $p_date_from = null,
        $p_date_to = null,
        $p_project_id = null,
        $p_category_id = null,
        $p_reporter_id = null,
        $p_handler_id = null,
        $p_prority = null,
        $p_status = null,
        $p_customer_id = null,
        $p_customer_group_id = null,
    )
    {

		$query = new \DbQuery();

        $table_bug_data = db_get_table('bug');
        $table_project = db_get_table('project');
        $table_bug_text = db_get_table('bug_text');
        $table_user = db_get_table('user');
        $table_category = db_get_table('category');
        $table_bug_note = db_get_table('bugnote');

        $table_scrm_bug_data = plugin_table('bug_data');
        $table_scrm_bug_note = plugin_table('bug_note');
        $table_scrm_customer = plugin_table('customer');
        $table_scrm_customer_group_note = plugin_table('group');

        $p_search_str = str_replace("'","''",$p_search);

        //Conditions
        $condition = array();
        if ($p_search != '')
        {
            array_push($condition,"(
                cast(BT.id as varchar(10)) LIKE '%{$p_search_str}%'
                OR BT.summary LIKE '%{$p_search_str}%' 
                OR BTT.description LIKE '%{$p_search_str}%'
                OR PR.name LIKE '%{$p_search_str}%'
                OR CAT.name LIKE '%{$p_search_str}%'
                OR UT.username LIKE '%{$p_search_str}%'
                OR UTH.username LIKE '%{$p_search_str}%'
            )");
        }
        if ($p_date_from != null)
        {
            array_push($condition,"BT.date_submitted >= {$p_date_from}");
        }
        if ($p_date_to != null)
        {
            array_push($condition,"BT.date_submitted <= {$p_date_to}");
        }
        if ($p_project_id != null)
        {
            array_push($condition,"BT.project_id = {$p_project_id}");
        }
        if ($p_category_id != null)
        {
            array_push($condition,"BT.category_id = {$p_category_id}");
        }
        if ($p_reporter_id != null)
        {
            array_push($condition,"BT.reporter_id = {$p_reporter_id}");
        }
        if ($p_handler_id != null)
        {
            array_push($condition,"BT.handler_id = {$p_handler_id}");
        }
        if ($p_prority != null)
        {
            array_push($condition,"BT.prority = {$p_prority}");
        }
        if ($p_status != null)
        {
            array_push($condition,"BT.status = {$p_status}");
        }
        if ($p_customer_id != null)
        {
            array_push($condition,"CU.id = {$p_customer_id}");
        }
        if ($p_customer_group_id != null)
        {
            array_push($condition,"CU.id = {$p_customer_group_id}");
        }



        $condition_sql = SCrmSchema::buildCondition($condition);


        $sql = "SELECT 
            BT.id AS bug_id,
            BT.summary,
            BTT.description,
            BT.project_id AS project_id,
            PR.name AS project_name,
            BT.category_id,
            CAT.name AS category_name, 
            BT.date_submitted,
            BT.reporter_id,
            UT.username AS reporter_username,
            BT.handler_id,
            UTH.username AS handler_username,
            BT.priority,
            BT.status,
            CU.id AS customer_id,
            CU.customer_name,
            CU.group_id,
            CGR.group_name,
            SUM(SBNT.time_spent) AS time_spent,
            SUM(ROUND(SBNT.points_per_hour/60.0*SBNT.time_spent,2)) as total_points,
            SUM(SBNTB.time_spent) AS time_spent_billable,
            SUM(ROUND(SBNTB.points_per_hour/60.0*SBNTB.time_spent,2)) as total_points_billable
        FROM {$table_bug_data} BT
        INNER JOIN {$table_project} PR ON PR.ID = BT.project_id
        INNER JOIN {$table_bug_text} BTT ON BTT.id = BT.bug_text_id 
        INNER JOIN {$table_user} UT ON UT.id = BT.reporter_id
        INNER JOIN {$table_user} UTH ON UTH.id = BT.handler_id
        INNER JOIN {$table_category} CAT ON CAT.id = BT.category_id
        LEFT JOIN {$table_scrm_bug_data} SBDT ON SBDT.bug_id = BT.id
        LEFT JOIN {$table_scrm_customer} CU ON CU.id = SBDT.customer_id
        LEFT JOIN {$table_scrm_customer_group_note} CGR ON CGR.id = CU.group_id
        LEFT JOIN {$table_bug_note} BNT ON BNT.bug_id = BT.id
        LEFT JOIN {$table_scrm_bug_note} SBNT ON SBNT.bugnote_id = BNT.id
        LEFT JOIN {$table_scrm_bug_note} SBNTB ON SBNTB.bugnote_id = BNT.id AND SBNT.is_billable = true
        {$condition_sql}
        GROUP BY 
            BT.ID,
            BT.summary,
            BTT.description,
            PR.ID,
            PR.name,
            BT.category_id,
            CAT.name, 
            BT.date_submitted,
            BT.reporter_id,
            UT.username,
            BT.handler_id,
            UTH.username,
            BT.priority,
            BT.status,
            CU.id,
            CU.customer_name,
            CU.group_id,
            CGR.group_name";
            
        //Order
        $order_sql = "";
        if ($p_sort_field != "")
        {
            $order_sql = " ORDER BY " . $p_sort_field . " " . $p_sort_dir;
        }

        $sql = $sql .$order_sql ;
        
        $query->sql( $sql );
        if ($p_page>0)
        {
            $rows_per_page = config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE);
            $query->set_offset(($p_page-1)*$rows_per_page);
            $query->set_limit($rows_per_page);
        }
        return $query->execute();
    }
}
