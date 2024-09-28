<?php
plugin_require_api('core/SCrmTools.php');
plugin_require_api('core/DAOCustomerVault.php');

access_ensure_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD));
auth_ensure_user_authenticated();
if (!access_has_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD))) 
{
	trigger_error( "Unauthorized !", ERROR );
	return;
}		
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
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

if ($submit_go_back != '')
{
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_save != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_customer_id = gpc_get_int('customer_id',0);

	$field_item_name = gpc_get_string('field_item_name');
	$field_item_value = gpc_get_string('field_item_value');

	if ($field_id ==  0)
	{
		DAOCustomerVault::insert_record(
			$field_customer_id,
			$field_item_name,
			$field_item_value
		);
		print_header_redirect( $page_callback_url );
		return;
	}
	else{
		DAOCustomerVault::update_record(
			$field_id,
			$field_item_name,
			$field_item_value
		);
	}
}
else if ($submit_delete != '')
{
	$field_id = gpc_get_int('field_id',0);
	DAOCustomerVault::delete_record(
		$field_id
	);
	print_header_redirect( $page_callback_url );
	return;
}
else
{
	$field_id = gpc_get_int('id',0);
}

$record = DAOCustomerVault::get_record($field_id);
$row = db_fetch_array( $record );
$field_customer_id = $row['customer_id'];
$field_customer_name = $row['customer_name'];
$field_item_name = $row['item_name'];
$field_item_value= DAOCustomerVault::decrypt($row['item_value'],true);
$field_created_at = date($date_format,$row['created_at']);
$field_modified_at = date($date_format,$row['modified_at']);

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form id="edit_contact_record" method="post" enctype="multipart/form-data">
		<input type="hidden" id="field_id" name="field_id" maxlength="40" style="width:100%;" value="<?php echo $field_id;?>">
		<input type="hidden" id="field_customer_id" name="field_customer_id" maxlength="40" style="width:100%;" value="<?php echo $field_customer_id;?>">
		<input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="fa fa-key ace-icon"></i><?php echo plugin_lang_get('customer_vault_label_edit_item')?>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_id') .":". $field_id;?></span>

					<span class="badge"><?php echo $field_customer_name ?></span>

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
										<span class="required">*</span> 			
										<label for="field_item_name">
										<?php echo plugin_lang_get('table_customer_vault_item_name')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_item_name" name="field_item_name" maxlength="255" style="width:100%;" value="<?php echo $field_item_name;?>" required>
									</td>
								</tr>
								<tr>
									<th class="category width-20">
										<label for="field_item_value">
										<?php echo plugin_lang_get('table_customer_vault_item_value')?>
										</label>
									</th>
									<td>
										<textarea name="field_item_value" id="field_item_value" class="form-control" rows="25" maxlength="5000" style="width:100%;"><?php echo $field_item_value;?></textarea>
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