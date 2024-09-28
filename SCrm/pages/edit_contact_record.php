<?php
plugin_require_api('core/SCrmTools.php');
plugin_require_api('core/DAOContact.php');

access_ensure_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD));
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
SCrmTools::print_main_menu("edit_contacts");

$date_format = config_get( 'short_date_format' );
$page_callback_url = gpc_get_string('page_callback_url','');
$page_go_back_enabled = false;

if ($page_callback_url == '')
{
	$page_callback_url = plugin_page('edit_contacts',true);
}
else
{
	$page_go_back_enabled = true;
}

$form_action_type = gpc_get_string('action','edit');
$submit_go_back = gpc_get_string('submit_go_back','');
$submit_save = gpc_get_string('submit_save','');
$submit_delete = gpc_get_string('submit_delete','');

if ($submit_go_back != '')
{
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_save != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_first_name = gpc_get_string('field_first_name');
	$field_second_name = gpc_get_string('field_second_name');
	$field_email = gpc_get_string('field_email');
	$field_phone = gpc_get_string('field_phone');
	$field_address = gpc_get_string('field_address','');
	$field_notes = gpc_get_string('field_notes','');
	$field_active = gpc_get_string('field_active','unchecked');
	$field_active_val = false;
	if ($field_active == 'checked')
	{
		$field_active_val = true;
	}

	if ($field_id ==  0)
	{
		DAOContact::insert_record(
			$field_first_name,
			$field_second_name,
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
		DAOContact::update_record(
			$field_id,
			$field_first_name,
			$field_second_name,
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
	DAOContact::delete_record(
		$field_id
	);
	print_header_redirect( $page_callback_url );
	return;
}
else
{
	$field_id = gpc_get_int('id',0);
}


$record = DAOContact::get_record($field_id);
$row = db_fetch_array( $record );
$field_first_name = $row['first_name'];
$field_second_name= $row['second_name'];
$field_email= $row['email'];
$field_phone= $row['phone'];
$field_address= $row['address'];
$field_notes = $row['notes'];
$field_created_at = date($date_format,$row['created_at']);
$field_modified_at = date($date_format,$row['modified_at']);
$field_active = $row['active'];

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form id="edit_contact_record" method="post" enctype="multipart/form-data">
		<input type="hidden" id="field_id" name="field_id" maxlength="40" style="width:100%;" value="<?php echo $field_id;?>">
		<input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="fa fa-user-circle-o ace-icon"></i><?php echo plugin_lang_get('edit_contact_record_label_edit_contact')?>
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
										<label for="field_first_name">
										<?php echo plugin_lang_get('table_contact_col_first_name')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_first_name" name="field_first_name" maxlength="128" style="width:100%;" value="<?php echo $field_first_name;?>" required>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<span class="required">*</span> 			
										<label for="field_second_name">
										<?php echo plugin_lang_get('table_contact_col_second_name')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_second_name" name="field_second_name" maxlength="128" style="width:100%;" value="<?php echo $field_second_name;?>" required>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_email">
										<?php echo plugin_lang_get('table_contact_col_email')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_email" name="field_email" maxlength="128" style="width:100%;" value="<?php echo $field_email;?>">
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_phone">
										<?php echo plugin_lang_get('table_contact_col_phone')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_phone" name="field_phone" maxlength="128" style="width:100%;" value="<?php echo $field_phone;?>">
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_address">
										<?php echo plugin_lang_get('table_contact_col_address')?>
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
										<input type="checkbox" class="ace input-sm" id="field_active" name="field_active" value="checked" <?php if ($field_active=='checked') echo 'checked'; ?>> <span class="lbl"></span>
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
</div>

<?php
layout_page_end();