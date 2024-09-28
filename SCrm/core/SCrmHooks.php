<?php

class SCrmHooks
{

	public static function event_report_bug_form_top( $verticalLayout, $bug_id = 0 ) 
	{
		$customer_id = 0;
		$service_id = 0;
		$contact_id = 0;
		$is_billable = 0;
		
		if ( $bug_id ) {
			$bug = bug_get( $bug_id );
			$bug_data_rec = DAOBugData::get_record( $bug_id );
			$bug_data = db_fetch_array($bug_data_rec);
			if ( $bug_data ) {
				$customer_id = $bug_data['customer_id'];
			}
		}
		
		$customer_label = plugin_lang_get('table_bug_data_col_customer_id');
		$customer_select_rec = DAOCustomer::get_lookup_list();

		if ($verticalLayout)
		{
			$TH_class = "bug-status category";
		}
		else
		{
			$TH_class = "bug-status category";
		}

		$select_attributes_customer = 'id="crm_plugin_customer_id" name="crm_plugin_customer_id" value="'.$customer_id.'"';
		$required = "";
		if (config_get(SCrmPlugin::CFG_KEY_CUSTOMER_REQUIRED,false,true))
		{
			$select_attributes_customer .= "required";
			$required = '<span class="required">*</span>';
		}

		echo '<tr>' . 
			'<th class="'.$TH_class.'">' . $required .
				'<label for="crm_plugin_customer_id">'.$customer_label.'</label>' .
			'</th>' .
			'<td colspan="5">' .
				ScrmTools::format_select($customer_select_rec, $select_attributes_customer, "id", "customer_name", $customer_id) .
			'</td>';
		'</tr>';
	}

	public static function event_view_bug_details( $p_event, $p_bug_id ) 
	{
		$bug_data_rec = DAOBugData::get_record( $p_bug_id );
		$bug_data = db_fetch_array($bug_data_rec);
		if ( !$bug_data )
		{
			return;
		}
		
		$customer_label = plugin_lang_get('table_bug_data_col_customer_id');
		$group_label = plugin_lang_get('table_customer_col_group_id');
		$customer_rec = DAOCustomer::get_record( $bug_data['customer_id']);
		$customer = db_fetch_array($customer_rec);
		
		if ( $bug_data ) 
		{
			echo '<tr>' .
				'<th class="bug-status category">'.$customer_label.'</th>' .
				'<td>'.$customer["customer_name"]. SCrmTools::format_mail_link($customer['email'],true) .SCrmTools::format_phone_link($customer['phone'],true) . '</td>' .
				'<th class="bug-status category">'.$group_label.'</th>' .
				'<td colspan="3">'.$customer["group_name"].'</td>' .
			'</tr>';
		}
	}

	public static function event_update_bug( $p_event, $p_bug_data_old, $p_bug_data_new ) 
    {
		$customer_id = gpc_get_int('crm_plugin_customer_id', null);
		$p_bug_id = $p_bug_data_new->id;

		if ( $customer_id ) 
        {
			DAOBugData::update_record($p_bug_id, $customer_id);
		}
			
		return $p_bug_data_new;
	}

	public static function event_delete_bug($p_event, $p_bug_id)
	{
		DAOBugData::delete_record($p_bug_id);
	}
	
	public static function event_view_bugnote($p_event, $p_bug_id, $p_activitiy_id, $is_private)
	{
		$bugnote_rec = DAOBugNote::get_record( $p_activitiy_id);
		$bugnote = db_fetch_array($bugnote_rec);
		$time_spent_str = DaoBugNote::minutes_to_duration($bugnote["time_spent"]);

		if ($bugnote)
		{
			echo '<tr>';
				$totalPoints = round($bugnote["time_spent"]/60.0*$bugnote['points_per_hour'],2);
				$billablePoiunts = 0;
				if ($bugnote['is_billable'])
				{
					$billablePoiunts = $totalPoints;
				}

				echo '<td colspan="2" style = "border: 0px !important; padding:0px !important;">' .
					'<table class = "table table-condensed table-striped" style="padding:0px;border:0px;">' .
						'<tr>' .
							'<td class="category">' .
							plugin_lang_get('bug_issues_view_label_activity_status') .
							'</td>' .
							'<td style="text-align:right;width:25%;">'.$bugnote['service_name'] . '</td>' .
							'<td style="text-align:right;width:10%;">'.plugin_lang_get('table_service_col_points_per_hour'). ":" . $bugnote['points_per_hour'] . '</td>' .
							'<td style="text-align:right;width:10%;">'.plugin_lang_get('table_service_col_time_spent') . ":" . $time_spent_str . '</td>' .
							'<td style="text-align:right;width:10%;">'.plugin_lang_get('table_service_col_is_billable'). ":" . SCrmTools::format_checkmark($bugnote['is_billable']).'</td>' .
							'<td style="text-align:right;width:10%;">'.plugin_lang_get('bug_note_label_total_points'). ":" . $totalPoints . '</td>' .
							'<td style="text-align:right;width:10%;">'.plugin_lang_get('bug_note_label_billable_points'). ":" . $billablePoiunts . '</td>' .
						'</tr>' .
					'</table>'.
				'</td>' .
			'</tr>';
		}
	}

	public static function event_bugnote_edit_form($p_event, $p_bug_id, $p_bugnote_id)
	{
		if (str_contains($_SERVER['REQUEST_URI'], 'bug_update_page.php')) 
		{ 
			return;
		}

		$bugdat_rec = DAOBugData::get_record($p_bug_id);
		$budata = db_fetch_array($bugdat_rec);
		$field_customer_id = $budata['customer_id'];

		$bugnote_rec = DAOBugNote::get_record( $p_bugnote_id);
		$bugnote = db_fetch_array($bugnote_rec);
		$field_contact_id = $bugnote['contact_id'];
		$field_service_id = $bugnote['service_id'];
		$field_time_spent = DAOBugNote::minutes_to_duration($bugnote["time_spent"]);

		if ($field_customer_id)
		{
			$recordsetContact = DAOCustomer::get_contacts_list($field_customer_id);
			$attributes = "class=\"input-sm\" id=\"field_contact_id\" name=\"field_contact_id\" value=\"{$field_contact_id}\"";
			echo '<tr>' .
				'<th class = "category">'.plugin_lang_get('bug_note_label_contact').'</th>' .
				'<td>' .
					ScrmTools::format_select($recordsetContact, $attributes, "id", "contact_name", $field_contact_id) .
				'</td>' .
			'</tr>';
		}

		$recordsetService = DAOService::get_list("service_name","ASC","checked");
		$attributes = "class=\"input-sm\" id=\"field_service_id\" name=\"field_service_id\" value=\"{$field_service_id}\"";
		$required = "";
		if (config_get(SCrmPlugin::CFG_KEY_SERVICE_REQUIRED,false,true))
		{
			$attributes .= "required";
			$required = '<span class="required">*</span>';
		}
		echo '<tr>'.
		'<th class = "category">' . $required . plugin_lang_get('bug_note_label_service').'</th>' .
			'<td>' . 
				ScrmTools::format_select($recordsetService, $attributes, "id", "service_name", $field_service_id) .
			'</td>'.
		'</tr>';

		$attributes = "";
		$required = "";
		if (config_get(SCrmPlugin::CFG_KEY_TIME_SPENT_REQUIRED,false,true))
		{
			$attributes .= "required";
			$required = '<span class="required">*</span>';
		}
		echo '<tr>' .
		'<th class = "category">' . $required .plugin_lang_get('bug_note_label_time_spent').'</th>' .
			'<td>' .
				'<input type="text" id="field_time_spent" name="field_time_spent" class="input-sm" size="5" pattern="\d{3}:\d{2}" placeholder="hhh:mm" value = "' .$field_time_spent . '"' .$attributes. '>' .
			'</td>' .
		'</tr>';

	}

	public static function event_bugnote_edit($p_event, $p_bug_id, $p_bugnote_id)
	{
		$contact_id = gpc_get_int('field_contact_id', null);
		$service_id = gpc_get_int('field_service_id', null);
		$time_spent = gpc_get_string('field_time_spent', '');
		$time_spent_min = DAOBugNote::duration_to_minutes($time_spent);
		$service_data = DAOBugData::get_service_data($p_bug_id,$service_id);

		DAOBugNote::update_record(
			$p_bugnote_id, 
			$contact_id,
			$service_id, 
			$service_data['is_billable'],
			$service_data['points_per_hour'],
			$time_spent_min
		);
	}

	public static function event_bugnote_deleted($p_event, $p_bug_id, $p_bugnote_id)
	{
		DAOBugNote::delete_record($p_bugnote_id);
	}

    public static function event_view_bugnotes_end($p_event, $p_bug_id)
	{
		$total_rec = DAOBugNote::get_total_points($p_bug_id);
		$total = db_fetch_array($total_rec);

		$billable_rec = DAOBugNote::get_total_billable_points($p_bug_id);
		$billable = db_fetch_array($billable_rec);

		echo '<tr><td colspan="2"> <div class="space-10"></div> </td></tr>';
		echo '<tr>' .
			'<th colspan="2" style = "border: 0px !important; padding:0px !important;">' .
				'<table class = "table table-condensed table-striped" style="padding:0px;border:0px;">' .
					'<tr>' .
						'<td style="text-align:right;" width="24%"><label class = "label" style="width:100%;">' . plugin_lang_get('global_cmd_total') . ':</label></td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_total_services') . ":" . $total['cnt'] . '</td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_total_billable_services') . ":" . $billable['cnt'] . '</td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_total_time'). ":" . DAOBugNote::minutes_to_duration($total['time_spent']) . '</td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_total_billable_time'). ":" . DAOBugNote::minutes_to_duration($billable['time_spent']) . '</td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_total_points') . ":" . $total['total_points'] . '</td>' .
						'<td style="text-align:right;" width="12%">' . plugin_lang_get('bug_note_label_billable_points') . ":" . $billable['total_points'] . '</td>' .
					'</tr>' .
				'</table>' .
			'</th>' .
        '</tr>' ;
	}
}