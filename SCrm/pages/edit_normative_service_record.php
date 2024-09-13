<?php
access_ensure_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD));
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
SCrmTools::print_main_menu("edit_normatives");

$date_format = config_get( 'short_date_format' );
$page_callback_url = gpc_get_string('page_callback_url','');
$page_go_back_enabled = false;
if ($page_callback_url == '')
{
	$page_callback_url = plugin_page('edit_services',true);
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
	$field_normative_id = gpc_get_int('field_normative_id',0);
	$field_service_id = gpc_get_int('field_service_id',0);
	$field_is_billable = gpc_get_string('field_is_billable','unchecked');
	$field_points_per_hour = gpc_get_string('field_points_per_hour','0.00');
	$field_is_billable_val = false;
	if ($field_is_billable == 'checked')
	{
		$field_is_billable_val = true;
	}

	if ($field_normative_id ==  0)
	{
		DAONormativeService::insert_record(
			$field_normative_id,
			$field_service_id,
			$field_is_billable_val,
			$field_points_per_hour
		);
		print_header_redirect( $page_callback_url );
		return;
	}
	else{
		DAONormativeService::update_record(
			$field_normative_id,
			$field_service_id,
			$field_is_billable_val,
			$field_points_per_hour
		);
	}
}
else if ($submit_delete != '')
{
	$field_normative_id = gpc_get_int('field_normative_id',0);
	$field_service_id = gpc_get_int('field_service_id',0);
	DAONormativeService::delete_record(
		$field_normative_id,
		$field_service_id
	);
	print_header_redirect( $page_callback_url );
	return;
}
else
{
	$field_normative_id = gpc_get_int('normative_id',0);
	$field_service_id = gpc_get_int('service_id',0);
}


$record = DAONormativeService::get_record($field_normative_id, $field_service_id);
$row = db_fetch_array( $record );
$field_normative_name = $row['normative_name'];
$field_service_name = $row['service_name'];
$field_is_billable = $row['is_billable'];
$field_points_per_hour = $row['points_per_hour'];
$field_created_at = date($date_format,$row['created_at']);
$field_modified_at = date($date_format,$row['modified_at']);
$field_global_is_billable = $row['global_is_billable'];
$field_global_points_per_hour = $row['global_points_per_hour'];


?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form id="edit_service_record" method="post" enctype="multipart/form-data">
		<input type="hidden" id="field_normative_id" name="field_normative_id" maxlength="40" style="width:100%;" value="<?php echo $field_normative_id;?>">
		<input type="hidden" id="field_service_id" name="field_service_id" maxlength="40" style="width:100%;" value="<?php echo $field_service_id;?>">
		<input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="fa fa-cog ace-icon"></i><?php echo plugin_lang_get('edit_normative_record_label_manage_service')?>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_id') .":". $field_normative_id.".".$field_service_id;?></span>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_created_at') .":". $field_created_at;?></span>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_modified_at') .":". $field_modified_at;?></span>
				</h4>
			</div>
			<div class="widget-body dz-clickable">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-bordered table-condensed">
							<tbody>

								<tr>
									<th class="category width-20">
										<label for="field_normative_name">
										<?php echo plugin_lang_get('table_normative_col_normative_name')?>
										</label>
									</th>
									<td>
										<?php echo $field_normative_name;?>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_service_name">
										<?php echo plugin_lang_get('table_service_col_service_name')?>
										</label>
									</th>
									<td>
										<?php 
											echo $field_service_name . "<br/>"; 
											echo plugin_lang_get('table_service_col_points_per_hour').":".$field_global_points_per_hour. "<br/>";
											echo plugin_lang_get('table_service_col_is_billable').":".SCrmTools::format_checkmark($field_global_is_billable);
										?>
									</td>
								</tr>

								<tr class="spacer">
									<td colspan="2"></td>
								</tr>								

								<tr>
									<th class="category width-20">
										<label for="field_points_per_hour">
										<?php echo plugin_lang_get('table_service_col_points_per_hour')?>
										</label>
									</th>
									<td>
										<input type="number" id="field_points_per_hour" name="field_points_per_hour" maxlength="20" style="width:100%;" min="0" step="0.01" value="<?php echo $field_points_per_hour;?>" required>
									</td>
								</tr>

								<tr>
									<th class="category width-20">
										<label for="field_is_billable">
										<?php echo plugin_lang_get('table_service_col_is_billable')?>
										</label>
									</th>
									<td>
										<input type="checkbox" class="ace input-sm" id="field_is_billable" name="field_is_billable" value="checked" <?php if ($field_is_billable=='checked') echo 'checked'; ?>><span class="lbl"></span>
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
					<?php if ($field_normative_id!=0){?>
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