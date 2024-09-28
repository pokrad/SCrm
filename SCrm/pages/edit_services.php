<?php
plugin_require_api('core/SCrmTools.php');
plugin_require_api('core/DAOStatistics.php');
plugin_require_api('core/DAOService.php');

header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
SCrmTools::print_main_menu("edit_services");

$this_page = plugin_page('edit_services');
$date_format = config_get( 'short_date_format' );
$this_page_create = plugin_page('edit_service_record') . '&action=create';
$this_page_edit = plugin_page('edit_service_record') . '&action=edit';

//Parameters
$p_sort_field = gpc_get_string('sort_field','id');
$p_sort_dir = gpc_get_string('sort_dir','ASC');
$p_hideinactive = gpc_get_string('hideinactive','unchecked');
$p_search = gpc_get_string('search','');
$p_grid_pager_current=gpc_get_int('grid_pager_current',1);
$p_grid_pager_prev_page = gpc_get_string('grid_pager_prev_page','');
$p_grid_pager_next_page = gpc_get_string('grid_pager_next_page','');

$sort_icon = '';
$sort_dir_new = '';
if ($p_sort_dir == 'ASC')
{
	$sort_icon = '<i class="fa fa-caret-up fa-lg blue"></i>';
	$sort_dir_new = 'DESC';
}
else{
	$sort_icon = '<i class="fa fa-caret-down fa-lg blue"></i>';
	$sort_dir_new = 'ASC';
}

$stats = DAOStatistics::getTotalCounts();
$resStat = db_fetch_array( $stats );

if ($p_grid_pager_prev_page != '')
{
	if ($p_grid_pager_current>1)
	{
		$p_grid_pager_current=$p_grid_pager_current-1;
	}
}
else if ($p_grid_pager_next_page != '')
{
	$p_grid_pager_current=$p_grid_pager_current+1;
}


$list_query = DAOService::get_list(
	$p_sort_field,
	$p_sort_dir,
	$p_hideinactive,
	$p_search,
	$p_grid_pager_current
);
if ($list_query->RecordCount() == 0 && $p_grid_pager_current>1)
{
	$p_grid_pager_current = $p_grid_pager_current-1;
	$list_query = DAOService::get_list(
		$p_sort_field,
		$p_sort_dir,
		$p_hideinactive,
		$p_search,
		$p_grid_pager_current
	);
}
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">

		<!--Widget header-->
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-cog ace-icon"></i> 
				<?php echo plugin_lang_get('main_menu_services');?>	
				<span class="badge"><?php echo plugin_lang_get('global_cmd_total') . ":" .  $resStat['service_count'];?></span>
			</h4>
		</div>

		<!--Widget body-->
		<div class="widget-body">
			<!--Widget toolbox-->
			<form id="manage-group-filter" method="post" action="<?php echo $this_page; ?>" class="form-inline">
				<fieldset>
					<input type="hidden" id="grid_pager_current" name="grid_pager_current" value="<?php echo $p_grid_pager_current; ?>">
					<input type="hidden" id="sort_field" name="sort_field" value="<?php echo $p_sort_field; ?>">
					<input type="hidden" id="sort_dir" name="sort_dir" value="<?php echo $p_sort_dir; ?>">

					<div class="widget-toolbox padding-8 clearfix">
						<div id="manage-user-div" class="form-container">
							<div class="pull-left " >
								<?php 
									if (access_has_global_level(config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD)))
									{
										echo "<a class=\"btn btn-primary btn-white btn-round btn-sm\" href=\"{$this_page_create}\">";
											echo plugin_lang_get('edit_services_page_cmd_add_service');
										echo "</a><span class=\"lbl padding-8\">&nbsp;</span>";
									}
								?>
								<label>
									<input type="checkbox" class="ace input-sm" id="hideinactive" name="hideinactive" value="checked" <?php if ($p_hideinactive=='checked') echo 'checked'; ?>>
									<span class="lbl padding-6"><?php echo plugin_lang_get('global_cmd_hide_inactive_text');?></span>
								</label>
								<span class="lbl padding-6">&nbsp;</span>

								<label for="search">
									<input id="search" type="text" size="45" name="search" class="input-sm" value="<?php echo $p_search; ?>" placeholder="<?php echo plugin_lang_get('global_cmd_search_filter_text');?>">
								</label>
								<span class="lbl padding-6">&nbsp;</span>

								<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_search_filter_apply');?>">
								<span class="lbl padding-6">&nbsp;</span>
							</div>

							<div class="pull-right">
								<?php 
									if ($p_grid_pager_current >1)
									{
										echo '<input type="submit" id="grid_pager_prev_page" name="grid_pager_prev_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf060">';
									}
									else
									{
										echo '<input type="submit" id="grid_pager_prev_page" name="grid_pager_prev_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf04d" disabled>';
									}
									echo "&nbsp;" . plugin_lang_get('global_cmd_grid_pager'). $p_grid_pager_current . "&nbsp;";
									if ($list_query->RecordCount() < config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE) )
									{
										echo'<input type="submit" id="grid_pager_next_page" name="grid_pager_next_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf04d" disabled>';
									}
									else{
										echo'<input type="submit" id="grid_pager_next_page" name="grid_pager_next_page" class="fa fa-4x btn btn-primary btn-white btn-round btn-sm" value="&#xf061">';
									}
								?>
							</div>
						</div>
					</div>
				</fieldset>
			</form>

			<!--Widget main-->
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th style="width:3%;">
									<a href="<?php echo $this_page . '&sort_field=id&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_common_col_id');?>
									</a>
									<?php 
										if ($p_sort_field == 'id') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:60%;">
									<a href="<?php echo $this_page . '&sort_field=service_name&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_service_col_service_name');?>
									</a>
									<?php 
										if ($p_sort_field == 'service_name') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:3%;">
									<a href="<?php echo $this_page . '&sort_field=is_billable&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_service_col_is_billable');?>
									</a>
									<?php 
										if ($p_sort_field == 'is_billable') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:10%;">
									<a href="<?php echo $this_page . '&sort_field=points_per_hour&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_service_col_points_per_hour');?>
									</a>
									<?php 
										if ($p_sort_field == 'points_per_hour') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:10%;">
									<a href="<?php echo $this_page . '&sort_field=created_at&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_common_col_created_at');?>
									</a>
									<?php 
										if ($p_sort_field == 'created_at') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:10%;">
									<a href="<?php echo $this_page . '&sort_field=modified_at&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_common_col_modified_at');?>
									</a>
									<?php 
										if ($p_sort_field == 'modified_at') {
											echo $sort_icon;
										}
									?>
								</th>
								<th style="width:3%;">
									<a href="<?php echo $this_page . '&sort_field=active&amp;sort_dir=' . $sort_dir_new . '&amp;hideinactive=' . $p_hideinactive . '&amp;search=' . urlencode($p_search); ?> ">
										<?php echo plugin_lang_get('table_common_col_active');?>
									</a>
									<?php 
										if ($p_sort_field == 'active') {
											echo $sort_icon;
										}
									?>
								</th>
							</tr>
						</thead>

						<!--DATA-->
						<tbody>
							<?php 
								//Make a callback link so the user can return to the same page after save or go back...
								$p_search_str = urlencode($p_search);
								$page_callback_link_url = plugin_page('edit_services',true) .urlencode( 
									"&sort_field={$p_sort_field}&amp;sort_dir={$p_sort_dir}&amp;hideinactive={$p_hideinactive}&grid_pager_current={$p_grid_pager_current}&amp;search={$p_search_str}"
								);

								while( $row = db_fetch_array( $list_query ) ) 
								{
									$row_link = "<a href={$this_page_edit}&id={$row['id']}&page_callback_url={$page_callback_link_url}>";
									echo "<tr>" .
										"<td> {$row_link}" . $row['id'] . "</a></td>" .
										"<td> {$row_link}" . $row['service_name'] . "</a></td>" .
										"<td>" . SCrmTools::format_checkmark($row['is_billable']) . "</td>" .
										"<td>" . $row['points_per_hour'] . "</td>" .
										"<td>" . date($date_format,$row['created_at']) . "</td>" .
										"<td>" . date($date_format,$row['modified_at']) . "</td>" .
										"<td>" . SCrmTools::format_checkmark($row['active']) . "</td>" .
									"</tr>";
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>		
</div>

<?php
layout_page_end();