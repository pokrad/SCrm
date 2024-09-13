<?php
access_ensure_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD));
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
SCrmTools::print_main_menu("edit_customers");

$date_format = config_get( 'short_date_format' );
$page_callback_url = gpc_get_string('page_callback_url','');
$page_go_back_enabled = false;
if ($page_callback_url == '')
{
	$page_callback_url = plugin_page('edit_customers',true);
}
else
{
	$page_go_back_enabled = true;
}

$form_action_type = gpc_get_string('action','edit');
$submit_go_back = gpc_get_string('submit_go_back','');
$submit_save = gpc_get_string('submit_save','');
$submit_delete = gpc_get_string('submit_delete','');
$submit_add_contact = gpc_get_string('submit_add_contact','');
$submit_remove_contact_id = gpc_get_int('submit_remove_contact_id',0);
$submit_add_vault_item = gpc_get_string('submit_add_vault_item','');

if ($submit_go_back != '')
{
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_save != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_group_id = gpc_get_int('field_group_id');
	$field_normative_id = gpc_get_int('field_normative_id');
	$field_customer_name = gpc_get_string('field_customer_name');
	$field_ident_number = gpc_get_string('field_ident_number');
	$field_email = gpc_get_string('field_email');
	$field_phone = gpc_get_string('field_phone');
	$field_address = gpc_get_string('field_address');
	$field_notes = gpc_get_string('field_notes','');
	$field_active = gpc_get_string('field_active','unchecked');
	$field_active_val = false;
	if ($field_active == 'checked')
	{
		$field_active_val = true;
	}

	/*
	echo $field_id . "<br/>";
	echo $field_group_id . "<br/>";
	echo $field_customer_name . "<br/>";
	echo $field_ident_number . "<br/>";
	echo $field_email . "<br/>";
	echo $field_phone . "<br/>";
	echo $field_address . "<br/>";
	echo $field_notes . "<br/>";
	echo $field_active . "<br/>";
	echo $field_active_val . "<br/>";
	return;
	*/

	if ($field_id ==  0)
	{
		DAOCustomer::insert_record(
			$field_group_id,
			$field_normative_id,
			$field_customer_name,
			$field_ident_number,
			$field_email,
			$field_phone,
			$field_address,
			$field_notes,
			$field_active_val
		);
		print_header_redirect( $page_callback_url );
		return;
	}
	else{
		DAOCustomer::update_record(
			$field_id,
			$field_group_id,
			$field_normative_id,
			$field_customer_name,
			$field_ident_number,
			$field_email,
			$field_phone,
			$field_address,
			$field_notes,
			$field_active_val
		);
	}

}
else if ($submit_delete != '')
{
	$field_id = gpc_get_int('field_id',0);
	DAOCustomer::delete_record(
		$field_id
	);
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_add_contact != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_contact_add_id = gpc_get_int('field_contact_add_id',-1);
	DAOCustomer::addContact($field_id,$field_contact_add_id);
}
else if ($submit_remove_contact_id>0)
{
	$field_id = gpc_get_int('field_id',0);
	DAOCustomer::removeContact($field_id, $submit_remove_contact_id);
}
else if ($submit_add_vault_item!='')
{
	$field_id = gpc_get_int('field_id',0);
	$this_callback_url = urlencode(plugin_page('edit_customer_record',true) . "&action=edit&id=".$field_id);
	$add_vault_item_url = plugin_page("edit_customer_vault_record",true). "&page_callback_url={$this_callback_url}&customer_id={$field_id}";
	print_header_redirect( $add_vault_item_url );
}
else
{
	$field_id = gpc_get_int('id',0);
}



$record = DAOCustomer::get_record($field_id);
$row = db_fetch_array( $record );
$field_group_id = $row['group_id'];
$field_normative_id = $row['normative_id'];
$field_customer_name = $row['customer_name'];
$field_ident_number= $row['ident_number'];
$field_email= $row['email'];
$field_phone= $row['phone'];
$field_address= $row['address'];
$field_notes = $row['notes'];
$field_created_at = date($date_format,$row['created_at']);
$field_modified_at = date($date_format,$row['modified_at']);
$field_active = $row['active'];

$p_this_page_url = plugin_page('edit_customer_record') . "&field_id={$field_id}&page_callback_url={$page_callback_url}";

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<!-- Customer detail -->
	<form id="edit_customer_record" method="post" enctype="multipart/form-data">
		<input type="hidden" id="field_id" name="field_id" maxlength="40" style="width:100%;" value="<?php echo $field_id;?>">
		<input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="fa fa-id-card ace-icon"></i><?php echo plugin_lang_get('edit_customer_record_label_edit_customer')?>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_id') .":". $field_id;?></span>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_created_at') .":". $field_created_at;?></span>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_modified_at') .":". $field_modified_at;?></span>

					<?php 
						echo SCrmTools::format_mail_link($field_email, true);
						echo SCrmTools::format_phone_link($field_phone, true);
					?>
				</h4>
			</div>
			<div class="widget-body dz-clickable">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-bordered table-condensed">
							<tbody>
								<tr>
									<th class="category width-20">
										<span class="required">*</span> 			
										<label for="field_customer_name">
										<?php echo plugin_lang_get('table_customer_col_customer_name')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_customer_name" name="field_customer_name" maxlength="128" style="width:100%;" value="<?php echo $field_customer_name;?>" required>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<span class="required">*</span> 			
										<label for="field_group_id">
										<?php echo plugin_lang_get('table_customer_col_group_id')?>
										</label>
									</th>
									<td>
										<?php 
											$recordset = DAOGroup::get_lookup_list();
											$selectAttributes = "class=\"input-sm\" id=\"field_group_id\" name=\"field_group_id\" value=\"{$field_group_id}\" required";
											echo ScrmTools::format_select($recordset, $selectAttributes, "id", "group_name", $field_group_id);
										?>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_normative_id">
										<?php echo plugin_lang_get('table_customer_col_normative_id')?>
										</label>
									</th>
									<td>
										<?php 
											$recordset = DAONormative::get_lookup_list();
											$selectAttributes = "class=\"input-sm\" id=\"field_normative_id\" name=\"field_normative_id\" value=\"{$field_normative_id}\"";
											echo ScrmTools::format_select($recordset, $selectAttributes, "id", "normative_name", $field_normative_id);
										?>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_ident_number">
										<?php echo plugin_lang_get('table_customer_col_ident_number')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_ident_number" name="field_ident_number" maxlength="30" style="width:100%;" value="<?php echo $field_ident_number;?>">
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_email">
										<?php echo plugin_lang_get('table_customer_col_email')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_email" name="field_email" maxlength="128" style="width:100%;" value="<?php echo $field_email;?>">
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_phone">
										<?php echo plugin_lang_get('table_customer_col_phone')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_phone" name="field_phone" maxlength="128" style="width:100%;" value="<?php echo $field_phone;?>">
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_address">
										<?php echo plugin_lang_get('table_customer_col_address')?>
										</label>
									</th>
									<td>
										<textarea name="field_address" id="field_address" class="form-control" rows="4" maxlength="512" style="width:100%;"><?php echo $field_address;?></textarea>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_notes">
										<?php echo plugin_lang_get('table_common_col_notes')?>
										</label>
									</th>
									<td>
										<textarea name="field_notes" id="field_notes" class="form-control" rows="7" maxlength="2048" style="width:100%;"><?php echo $field_notes;?></textarea>
									</td>
								</tr>

								<tr>
									<th class="category width-20">
										<label for="field_active">
										<?php echo plugin_lang_get('table_common_col_active')?>
										</label>
									</th>
									<td>
										<input type="checkbox" class="ace input-sm" id="field_active" name="field_active" value="checked" <?php if ($field_active=='checked') echo 'checked'; ?>><span class="lbl"></span>
									</td>
								</tr>

							</tbody>
						</table>
					</div>

				</div>
				<div class="widget-toolbox padding-8 clearfix">
					<?php if ($page_go_back_enabled){?>
						<input type="submit" id="submit_go_back" name ="submit_go_back" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_go_back')?>" >
					<?php }?>
					<input type="submit" id="submit_save" name ="submit_save" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_save_record')?>" >
					<?php if ($field_id!=0){?>
						<input type="submit" id="submit_delete" name ="submit_delete" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_delete_record')?>" >
					<?php }?>
					<span class="required pull-right"> * <?php echo plugin_lang_get('global_cmd_required_field')?></span>
				</div>
			</div>
		</div>
	</form>


	<?php
		$this_callback_url = urlencode(plugin_page('edit_customer_record',true) . "&action=edit&id=".$field_id);

		$collapse_block = is_collapsed( 'customer_contact_list' );
		$block_css = $collapse_block ? 'collapsed' : '';
		$block_icon = $collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
	?>

	<!-- Customer contacts -->
	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2 <?php echo $block_css ?>" id = "customer_contact_list">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-user-circle-o"></i> 
				<?php echo plugin_lang_get('edit_customer_record_label_edit_customer_contacts');?>	
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<?php print_icon( $block_icon, 'ace-icon 1 bigger-125' ); ?>
				</a>
			</div>
		</div>
		<div class="widget-body">
			<form id="edit_customers_contacts_record" method="post" enctype="multipart/form-data">
				<input type="hidden" id="field_id" name="field_id" value="<?php echo $field_id;?>">
				<input type="hidden" name="field_contact_add_id" id = "field_contact_add_id" value = "">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th style="width:3%;">
									<?php echo plugin_lang_get('table_common_col_id');?>
								</th>
								<th>
									<?php echo plugin_lang_get('table_contact_col_first_name');?>
								</th>
								<th>
										<?php echo plugin_lang_get('table_contact_col_second_name');?>
								</th>
								<th style="width:15%;">
									<?php echo plugin_lang_get('table_contact_col_email');?>
								</th>
								<th style="width:15%;">
									<?php echo plugin_lang_get('table_contact_col_phone');?>
								</th>
								<th style="width:3%;">
									<?php echo plugin_lang_get('table_common_col_active');?>
								</th>
								<th style="width:3%;"></th>
							</tr>
						</thead>

						<?php 
							$contact_records = DAOCustomer::getContactsList($field_id);
							$page_edit_contact = plugin_page('edit_contact_record',true) . "&action=edit&page_callback_url=" . $this_callback_url;
							while( $row = db_fetch_array( $contact_records ) ) 
							{
								$row_link = "<a href=" . $page_edit_contact . "&id=" . $row['id'] . ">";
								$remove_this_contact_url = "{$p_this_page_url}&submit_remove_contact_id=" . $row['id'];
								echo "<tr>" .
									"<td>" . $row_link . $row['id'] . "</a>" .
									"<td>" . $row_link . $row['first_name'] . "</a>" .
									"<td>" . $row_link . $row['second_name'] . "</a>" .
									"<td> " . SCrmTools::format_mail_link($row['email']) ."</td>" .
									"<td>". SCrmTools::format_phone_link($row['phone'])."</td>" .
									"<td>". SCrmTools::format_checkmark($row['active'])."</td>" .
									"<td>" .
										"<a class=\"fa fa-unlink btn btn-primary btn-white btn-round\" href =\"{$remove_this_contact_url}\">" .
									"</td>" .
								"</tr>" ;
							}

							$contact_lookup = DAOCustomer::getAddContactsLookupList($field_id);
							$selectAttributes = "class=\"input-sm\" id=\"field_contact_add_id\" name=\"field_contact_add_id\" value=\"\" required";
							$action_title = plugin_lang_get('edit_customer_record_label_add_contact');
							echo "<tr>" .
								"<td></td>" .
								"<td colspan='6'>" .
									ScrmTools::format_select($contact_lookup, $selectAttributes, "id", "contact_name", $p_field_contact_add_id) .
									"&nbsp;<input type=\"submit\" id=\"submit_add_contact\" name =\"submit_add_contact\" class=\"btn btn-primary btn-white btn-round\" value=\"{$action_title}\" >" .
								"</td>" .
							"</tr>";
						?>
					</table>
				</div>
			</form>
		</div>
	</div>

	<!-- Customer vault -->
	<?php 
		if (access_has_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD))) { 
			
			$collapse_block = is_collapsed( 'customer_vault_item_list' );
			$block_css = $collapse_block ? 'collapsed' : '';
			$block_icon = $collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
			
	?>

	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2 <?php echo $block_css ?>" id = "customer_vault_item_list">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-key"></i> 
				<?php echo plugin_lang_get('edit_customer_record_label_edit_customer_vault');?>	
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<?php print_icon( $block_icon, 'ace-icon 1 bigger-125' ); ?>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<form id="edit_customer_vault_record" method="post" enctype="multipart/form-data">
				<input type="hidden" id="field_id" name="field_id" value="<?php echo $field_id;?>">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th style="width:3%;">
									<?php echo plugin_lang_get('table_common_col_id');?>
								</th>
								<th>
									<?php echo plugin_lang_get('table_customer_vault_item_name');?>
								</th>
								<th style="width:5%;">
									<?php echo plugin_lang_get('table_common_col_created_at');?>
								</th>
								<th style="width:5%;">
									<?php echo plugin_lang_get('table_common_col_modified_at');?>
								</th>
							</tr>
						</thead>

						<?php 
							$bugs_list_rec = DAOCustomerVault::get_list($field_id);
							$page_edit_service = plugin_page('edit_customer_vault_record',true) . "&action=edit&page_callback_url=" . $this_callback_url;
							while( $row = db_fetch_array( $bugs_list_rec ) ) 
							{
								$row_link = "<a href=" . $page_edit_service . "&customer_id=".$field_id."&id=" . $row['id'] . ">";
								echo "<tr>" .
									"<td>" . $row_link . $row['id'] . "</a></td>" .
									"<td>" . $row_link . $row['item_name'] . "</a></td>" .
									"<td>" . $row['created_at'] . "</td>" .
									"<td>" . $row['modified_at'] . "</td>" .
								"</tr>";
							}

							$action_title = plugin_lang_get('customer_vault_label_add_item');
							echo "<tr>" .
								"<td></td>" .
								"<td colspan='4'>" .
									"&nbsp;<input type=\"submit\" id=\"submit_add_vault_item\" name =\"submit_add_vault_item\" class=\"btn btn-primary btn-white btn-round\" value=\"{$action_title}\" >" .
								"</td>" .
							"</tr>";
						?>
					</table>
				</div>
			</form>
		</div>
	</div>
	<?php } ?>



	<!-- Customer issues -->
	<?php 
		$collapse_block = is_collapsed( 'customer_issue_list' );
		$block_css = $collapse_block ? 'collapsed' : '';
		$block_icon = $collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';


		$p_search = gpc_get_string('search','');
		$p_issue_list_grid_pager_current=gpc_get_int('issue_list_grid_pager_current',1);
		$p_issue_list_grid_pager_prev_page = gpc_get_string('issue_list_grid_pager_prev_page','');
		$p_issue_list_grid_pager_next_page = gpc_get_string('issue_list_grid_pager_next_page','');
		
		if ($p_issue_list_grid_pager_prev_page != '')
		{
			if ($p_issue_list_grid_pager_current>1)
			{
				$p_issue_list_grid_pager_current=$p_issue_list_grid_pager_current-1;
			}
		}
		else if ($p_issue_list_grid_pager_next_page != '')
		{
			$p_issue_list_grid_pager_current=$p_issue_list_grid_pager_current+1;
		}
		

		$bugs_list_rec = DAOBugData::get_detailed_bugs_list(
			"date_submitted",
			"DESC",
			$p_search,
			$p_issue_list_grid_pager_current,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			$field_id,
			null,
		);

	?>

	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2 <?php echo $block_css ?>" id = "customer_issue_list">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-list-alt"></i> 
				<?php echo plugin_lang_get('edit_customer_record_label_view_issues');?>	
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<?php print_icon( $block_icon, 'ace-icon 1 bigger-125' ); ?>
				</a>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-toolbox padding-8 clearfix">
				<form id="manage-group-filter" method="post" action="<?php echo $this_page; ?>" class="form-inline">
				<input type="hidden" id="field_id" name="field_id" value="<?php echo $field_id;?>">
					<input type="hidden" id="issue_list_grid_pager_current" name="issue_list_grid_pager_current" value="<?php echo $p_issue_list_grid_pager_current; ?>">
					<input type="hidden" id="sort_field" name="sort_field" value="<?php echo $p_sort_field; ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo $p_sort_dir; ?>">
					<fieldset>
						<div id="manage-user-div" class="form-container">
							<div class="pull-left">
								<label for="search">
									<input id="search" type="text" size="45" name="search" class="input-sm" value="<?php echo $p_search; ?>" placeholder="<?php echo plugin_lang_get('global_cmd_search_filter_text');?>">
								</label>
								<span class="lbl padding-6">&nbsp;</span>
								<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_search_filter_apply');?>">							
							</div>
							<div class="pull-right">
								<?php 
									if ($p_issue_list_grid_pager_current >1)
									{
										echo '<input type="submit" id="issue_list_grid_pager_prev_page" name="issue_list_grid_pager_prev_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf060">';
									}
									else
									{
										echo '<input type="submit" id="issue_list_grid_pager_prev_page" name="issue_list_grid_pager_prev_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf04d" disabled>';
									}
									echo "&nbsp;" . plugin_lang_get('global_cmd_grid_pager'). $p_issue_list_grid_pager_current . "&nbsp;";
									if ($bugs_list_rec->RecordCount() < config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE) )
									{
										echo'<input type="submit" id="issue_list_grid_pager_next_page" name="issue_list_grid_pager_next_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf04d" disabled>';
									}
									else{
										echo'<input type="submit" id="issue_list_grid_pager_next_page" name="issue_list_grid_pager_next_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf061">';
									}
								?>
							</div>
					</fieldset>
				</form>
			</div>
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th style="width:3%;">
									<?php echo plugin_lang_get('bug_issues_view_label_bug_id');?>
								</th>
								<th style="width:10%;">
									<?php echo plugin_lang_get('bug_issues_view_label_summary');?>
								</th>
								<th style="width:10%;">
									<?php echo plugin_lang_get('bug_issues_view_label_description');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_project_name');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_category_name');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_date_submitted');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_reporter_username');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_handler_username');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_priority');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_status');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_time_spent');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_total_points');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_time_spent_billable');?>
								</th>
								<th style="width:7%;">
									<?php echo plugin_lang_get('bug_issues_view_label_total_points_billable');?>
								</th>
							</tr>
						</thead>

						<?php 
							$date_format = config_get( 'short_date_format' );
							while( $row = db_fetch_array( $bugs_list_rec ) ) 
							{
								echo "<tr>" .
									"<td>" . $row['bug_id'] . "</td>" .
									"<td>" . $row['summary'] . "</td>" .
									"<td>" . $row['description'] . "</td>" .
									"<td>" . $row['project_name'] . "</td>" .
									"<td>" . $row['category_name'] . "</td>" .
									"<td>" . date($date_format, $row['date_submitted']) . "</td>" .
									"<td>" . $row['reporter_username'] . "</td>" .
									"<td>" . $row['handler_username'] . "</td>" .
									"<td>" . get_enum_element( 'priority', $row['priority']) . "</td>" .
									"<td>" . get_enum_element( 'status', $row['status']) . "</td>" .
									"<td>" . DAOBugNote::minutes_to_duration($row['time_spent']) . "</td>" .
									"<td>" . $row['total_points'] . "</td>" .
									"<td>" . DAOBugNote::minutes_to_duration($row['time_spent_billable']) . "</td>" .
									"<td>" . $row['total_points_billable'] . "</td>" .						
								"</tr>";
							}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
layout_page_end();