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
	$page_callback_url = plugin_page('edit_normatives',true);
}
else
{
	$page_go_back_enabled = true;
}

$form_action_type = gpc_get_string('action','edit');
$submit_go_back = gpc_get_string('submit_go_back','');
$submit_save = gpc_get_string('submit_save','');
$submit_delete = gpc_get_string('submit_delete','');
$submit_add_service = gpc_get_string('submit_add_service','');
$submit_remove_service_id = gpc_get_int('submit_remove_service_id',0);

if ($submit_go_back != '')
{
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_save != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_normative_name = gpc_get_string('field_normative_name');
	$field_notes = gpc_get_string('field_notes','');
	$field_active = gpc_get_string('field_active','unchecked');
	$field_active_val = false;
	if ($field_active == 'checked')
	{
		$field_active_val = true;
	}

	/*
	echo $field_id . "<br/>";
	echo $field_normative_name . "<br/>";
	echo $field_address . "<br/>";
	echo $field_notes . "<br/>";
	echo $field_active . "<br/>";
	echo $field_active_val . "<br/>";
	return;
	*/

	if ($field_id ==  0)
	{
		DAOnormative::insert_record(
			$field_normative_name,
			$field_notes,
			$field_active_val
		);
		print_header_redirect( $page_callback_url );
		return;
	}
	else{
		DAOnormative::update_record(
			$field_id,
			$field_normative_name,
			$field_notes,
			$field_active_val
		);
	}

}
else if ($submit_delete != '')
{
	$field_id = gpc_get_int('field_id',0);
	DAOnormative::delete_record(
		$field_id
	);
	print_header_redirect( $page_callback_url );
	return;
}
else if ($submit_add_service != '')
{
	$field_id = gpc_get_int('field_id',0);
	$field_service_add_id = gpc_get_int('field_service_add_id',-1);
	//echo $submit_add_service ."<br/>";
	//echo $field_service_add_id;
	DAOnormative::add_service($field_id,$field_service_add_id);
	$this_callback_url = urlencode(plugin_page('edit_normative_record',true) . "&action=edit&id=".$field_id);
	$show_serviceurl = plugin_page('edit_normative_service_record',true) . "&normative_id={$field_id}&service_id=$field_service_add_id&&page_callback_url={$this_callback_url}";
	print_header_redirect( $show_serviceurl );
	return;
}
else if ($submit_remove_service_id>0)
{
	$field_id = gpc_get_int('field_id',0);
	echo "remove service:{$field_id},{$submit_remove_service_id}<br/>";
	DAOnormative::remove_service($field_id, $submit_remove_service_id);
}
else
{
	$field_id = gpc_get_int('id',0);
}



$record = DAOnormative::get_record($field_id);
$row = db_fetch_array( $record );
$field_normative_name = $row['normative_name'];
$field_notes = $row['notes'];
$field_created_at = date($date_format,$row['created_at']);
$field_modified_at = date($date_format,$row['modified_at']);
$field_active = $row['active'];

$p_this_page_url = plugin_page('edit_normative_record') . "&field_id={$field_id}&page_callback_url={$page_callback_url}";

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<!-- normative detail -->
	<form id="edit_normative_record" method="post" enctype="multipart/form-data">
		<input type="hidden" id="field_id" name="field_id" maxlength="40" style="width:100%;" value="<?php echo $field_id;?>">
		<input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="fa fa-id-card ace-icon"></i><?php echo plugin_lang_get('edit_normative_record_label_edit_normative')?>
					<span class="badge"><?php echo plugin_lang_get('table_common_col_id') .":". $field_id;?></span>
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
										<label for="field_normative_name">
										<?php echo plugin_lang_get('table_normative_col_normative_name')?>
										</label>
									</th>
									<td>
										<input type="text" id="field_normative_name" name="field_normative_name" maxlength="128" style="width:100%;" value="<?php echo $field_normative_name;?>" required>
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
		if ($field_id >0){
		$this_callback_url = urlencode(plugin_page('edit_normative_record',true) . "&action=edit&id=".$field_id);
	?>


	<!-- normative services -->
	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-cog"></i> 
				<?php echo plugin_lang_get('edit_normative_record_label_edit_services');?>	
			</h4>
		</div>
		<form id="edit_normatives_services_record" method="post" enctype="multipart/form-data">
			<input type="hidden" id="field_id" name="field_id" value="<?php echo $field_id;?>">
			<input type="hidden" name="field_service_add_id" id = "field_service_add_id" value = "">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-condensed table-hover">
					<thead>
						<tr>
							<th style="width:3%;">
								<?php echo plugin_lang_get('table_common_col_id');?>
							</th>
							<th>
								<?php echo plugin_lang_get('table_service_col_service_name');?>
							</th>
							<th style="width:15%;">
								<?php echo plugin_lang_get('table_service_col_points_per_hour');?>
							</th>
							<th style="width:3%;">
								<?php echo plugin_lang_get('table_service_col_is_billable');?>
							</th>
							<th style="width:3%;"></th>
						</tr>
					</thead>

					<?php 
						$bugs_list_rec = DAOnormative::get_services_list($field_id);
						$page_edit_service = plugin_page('edit_normative_service_record',true) . "&action=edit&page_callback_url=" . $this_callback_url;
						while( $row = db_fetch_array( $bugs_list_rec ) ) 
						{
							$row_link = "<a href=" . $page_edit_service . "&normative_id=".$field_id."&service_id=" . $row['id'] . ">";
							echo "<tr>";
								echo "<td>" . $row_link . $row['id'] . "</a></td>";
								echo "<td>" . $row_link . $row['service_name'] . "</a></td>";
								echo "<td>".$row['points_per_hour']."</td>";
								echo "<td>". SCrmTools::format_checkmark($row['is_billable'])."</td>";

								$remove_this_service_url = "{$p_this_page_url}&submit_remove_service_id=" . $row['id'];
								echo "<td>";
								echo "<a class=\"fa fa-unlink btn btn-primary btn-white btn-round\" href =\"{$remove_this_service_url}\">";
								echo "</td>";
							echo "</tr>";
						}
						echo "<tr>";
							echo "<td></td>";
							echo "<td colspan='4'>";
								$service_lookup = DAOnormative::get_add_service_lookup_list($field_id);
								$selectAttributes = "class=\"input-sm\" id=\"field_service_add_id\" name=\"field_service_add_id\" value=\"\" required";
								echo ScrmTools::format_select($service_lookup, $selectAttributes, "id", "service_name", $p_field_service_add_id);

								$action_title = plugin_lang_get('edit_normative_record_label_add_service');
								echo "&nbsp;<input type=\"submit\" id=\"submit_add_service\" name =\"submit_add_service\" class=\"btn btn-primary btn-white btn-round\" value=\"{$action_title}\" >";

							echo "</td>";
						echo "</tr>";
					?>
				</table>
			</div>
		</form>
	</div>
	<?php
	}

echo "</div>";

layout_page_end();