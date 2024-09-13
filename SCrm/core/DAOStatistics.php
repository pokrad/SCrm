<?php

class DAOStatistics
{
	public const REPORT_PERIOD_CREATED_THIS_MONTH = "report_period_created_this_month";
	public const REPORT_PERIOD_CREATED_LAST_MONTH = "report_period_created_last_month";
	public const REPORT_PERIOD_MODIFIED_THIS_MONTH = "report_period_modified_this_month";
	public const REPORT_PERIOD_MODIFIED_LAST_MONTH = "report_period_modified_last_month";
	public const REPORT_PERIOD_CLOSED_THIS_MONTH = "report_period_closed_this_month";
	public const REPORT_PERIOD_CLOSED_LAST_MONTH = "report_period_closed_last_month";
	
	/*
	DAO Operations for statistics
	*/
	public static function getTotalCounts() 
	{
		try
		{
			$query = new \DbQuery();

			$table_group = plugin_table('group');
			$table_customer = plugin_table('customer');
			$table_contact = plugin_table('contact');
			$table_service = plugin_table('service');
			$table_normative = plugin_table('normative');

			$sql = "SELECT 1 AS rn,
				(SELECT count(1) from {$table_group}) AS group_count,
				(SELECT count(1) from {$table_service}) AS service_count,
				(SELECT count(1) from {$table_normative}) AS normative_count,
				(SELECT count(1) from {$table_contact}) AS contact_count,
				(SELECT count(1) from {$table_customer}) AS customer_count";
			
			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_report_period_enum()
	{
		return [
			DAOStatistics::REPORT_PERIOD_CREATED_THIS_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_CREATED_THIS_MONTH),
			DAOStatistics::REPORT_PERIOD_CREATED_LAST_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_CREATED_LAST_MONTH),
			DAOStatistics::REPORT_PERIOD_MODIFIED_THIS_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_MODIFIED_THIS_MONTH),
			DAOStatistics::REPORT_PERIOD_MODIFIED_LAST_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_MODIFIED_LAST_MONTH),
			DAOStatistics::REPORT_PERIOD_CLOSED_THIS_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_CLOSED_THIS_MONTH),
			DAOStatistics::REPORT_PERIOD_CLOSED_LAST_MONTH => plugin_lang_get(DAOStatistics::REPORT_PERIOD_CLOSED_LAST_MONTH)
		];
	}

	public static function get_report_period_select($current_value, $form_action)
	{
		$arr = DAOStatistics::get_report_period_enum();
		$res = '<select class="input-s" id="report_period_select" name = "report_period_select" formaction="'.$form_action.'"> <option></option>';
		foreach ($arr as $key => $value) 
		{
            if ($key != $current_value)
            {
                $res .= "<option value=\"{$key}\">{$value}</option>";
            }
            else{
                $res .= "<option value=\"{$key}\" selected>{$value}</option>";
            }			
		}
		$res .= '</select>'; 
		return $res;
	}


	public static function get_description_from_filter($p_filter)
	{
		$res = "";
		$filter_customer = $p_filter['scrm_customer'];
		$customer_id = $filter_customer[0];
		if ($customer_id > 0)
		{
			$customer_rec = DAOCustomer::get_record($customer_id);
			$customer_row = db_fetch_array($customer_rec);
			$res .= "&nbsp;:&nbsp;".$customer_row['customer_name'];
		}
		
		if ($p_filter['filter_by_date'] == 1)
		{
			$res .= "&nbsp;&nbsp;".lang_get('email_date_submitted')." ". lang_get('from') ." ". $p_filter['start_year'] . "." . $p_filter['start_month'] . "." . $p_filter['start_day']; 
			$res .= " " . lang_get('to') ." ". $p_filter['end_year'] . "." . $p_filter['end_month'] . "." . $p_filter['end_day']; 
		} 
		if ($p_filter['filter_by_last_updated_date'] == 1)
		{
			$res .= "&nbsp;&nbsp;".lang_get('email_last_modified')." ".  lang_get('from') ." ". $p_filter['last_updated_start_year'] . "." . $p_filter['last_updated_start_month'] . "." . $p_filter['last_updated_start_day']; 
			$res .= " " . lang_get('to') ." ". $p_filter['last_updated_end_year'] . "." . $p_filter['last_updated_end_month'] . "." . $p_filter['last_updated_end_day']; 
		} 
		return $res;
	}

	public static function write_bug_rows($p_report_type, $p_columns,  array $p_rows, array $p_filter) 
	{
		//print_r($p_filter);
		$row_count = count( $p_rows );
		$col_count = count($p_columns);
		$date_format = config_get( 'short_date_format' );

		$col_activity_title = lang_get('activities_title');
		$col_note_title = lang_get('bugnote');
		$col_reporter_title = lang_get('reporter');


		$col_modified_at_label = plugin_lang_get('table_common_col_modified_at');
		$col_created_at_label = plugin_lang_get('table_common_col_created_at');
		$col_service_name_label = plugin_lang_get('table_service_col_service_name');
		$col_points_per_hour_label = plugin_lang_get('table_service_col_points_per_hour');
		$col_time_spent_label = plugin_lang_get('bug_issues_view_label_time_spent');
		$col_total_points_label = plugin_lang_get('bug_issues_view_label_total_points');
		$col_time_spent_billable_label = plugin_lang_get('bug_issues_view_label_time_spent_billable');
		$col_total_points_billable_label = plugin_lang_get('bug_issues_view_label_total_points_billable');

		$service_table_header = "<tr>".
			"<th width=\"3%\"> # </th>".
			"<th width=\"5%\">{$col_created_at_label}</th>".
			"<th width=\"5%\">{$col_modified_at_label}</th>".
			"<th width=\"5%\">{$col_reporter_title}</th>".
			"<th width=\"40%\">{$col_note_title}</th>".
			"<th width=\"10%\">{$col_service_name_label}</th>".
			"<th width=\"5%\">{$col_points_per_hour_label}</th>".
			"<th width=\"7%\">{$col_time_spent_label}</th>".
			"<th width=\"7%\">{$col_total_points_label}</th>".
			"<th width=\"7%\">{$col_time_spent_billable_label}</th>".
			"<th width=\"7%\">{$col_total_points_billable_label}</th>".
		"</tr>";

		$recap = [];
		$detail_colspan = $col_count-1;

		# Loop over bug rows
		for( $i = 0; $i < $row_count; $i++ ) 
		{

			DaoStatistics::print_bug_table_headers($p_columns);
	
			echo '<tbody>'; 
			$current_row = $p_rows[$i];
			echo '<tr>';
			foreach( $p_columns as $t_column ) 
			{
				helper_call_custom_function( 'print_column_value', array( $t_column, $current_row,COLUMNS_TARGET_PRINT_PAGE ) );
			}
			echo '</tr>';

			$services_rec = DaoBugData::get_services_list_for_bug($current_row->id);
			if ($services_rec->RecordCount() > 0)
			{
				echo "<tr>";
				echo "<th>{$col_activity_title} </th>";

				echo "<td class=\"no-margin no-padding\" colspan={$detail_colspan}>";
					echo "<table border=1 width=\"100%\">";
						echo $service_table_header;
						while( $row_service = db_fetch_array( $services_rec ) ) 
						{
							$date_submitted = date($date_format,$row_service['date_submitted']);
							$last_modified = date($date_format,$row_service['last_modified']);
							$duration = DAOBugNote::minutes_to_duration($row_service['time_spent']);
							$activity_link = "bugnote_edit_page.php?&bugnote_id=".$row_service['bugnote_id'];

							echo "<tr>";
							echo "<td> <a href=\"{$activity_link}\">{$row_service['bugnote_id']}</a></td>";
							echo "<td> <a href=\"{$activity_link}\">{$date_submitted}</a></td>";
							echo "<td> <a href=\"{$activity_link}\">{$last_modified}</a></td>";
							echo "<td> {$row_service['reporter_name']}</td>";
							echo "<td> {$row_service['note_text']}</td>";
							echo "<td> {$row_service['service_name']}</td>";
							echo "<td> {$row_service['points_per_hour']}</td>";
							echo "<td> {$duration}</td>";
							echo "<td> {$row_service['total_points']}</td>";

							$recap['bug_issues_view_label_time_spent'] += $row_service['time_spent'];
							$recap['bug_issues_view_label_total_points'] += $row_service['total_points'];

							if ($row_service['is_billable'])
							{
								echo "<td> {$duration}</td>";
								echo "<td> {$row_service['total_points']}</td>";
								$recap['bug_issues_view_label_time_spent_billable'] += $row_service['time_spent'];
								$recap['bug_issues_view_label_total_points_billable'] += $row_service['total_points'];
							}
							else
							{
								echo "<td></td>";
								echo "<td></td>";
							}
							echo "</tr>";
						};
					echo "</table>";
				echo "</td></tr>";	
			}
			echo '</tbody>';
			echo '</table>';
			echo '<br/>';
			echo '<br/>';
		}

		echo "<table border=1 class=\"table table-bordered table-condensed table-hover table-striped\" width=\"100%\"><thead><tr>";

		$totalCap = lang_get('total');
		$colspan = count($recap);
		echo "<tr><th colspan={$colspan}>" . $totalCap . "</th></tr>";

		foreach($recap as $key => $value)
		{
			echo "<th>" . plugin_lang_get($key) . "</th>";
		}
		echo "</tr></thead>";

		echo "<tbody>";
		foreach($recap as $key => $value)
		{
			echo "<td>";
			if (str_contains($key,'time_spent'))
			{
				echo DAOBugNote::minutes_to_duration($value);
			}
			else
			{
				echo $value;
			}
			echo "</td>";
		}
		echo "</tbody></table>";
	}



	private static function print_bug_table_headers($p_columns)
	{
		echo '<table id="buglist" class="table table-bordered table-condensed table-hover table-striped">'.
		'<thead>'.
			'<tr class="buglist-headers">';
				foreach( $p_columns as $t_column ) 
				{
					$t_view_state_text = column_get_title( $t_column );
					echo '<th>'.$t_view_state_text. '</th>'; 
				}
			echo '<tr>'.
		'</thead>';
	}
}