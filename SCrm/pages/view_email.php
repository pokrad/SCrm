<?php
require_api("config_api.php");
plugin_require_api('core/DAOMailListItem.php');

header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
SCrmTools::print_main_menu("view_email");

$this_page = plugin_page('view_email');
$p_grid_pager_current=gpc_get_int('grid_pager_current',1);
$p_grid_pager_prev_page = gpc_get_string('grid_pager_prev_page','');
$p_grid_pager_next_page = gpc_get_string('grid_pager_next_page','');

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

$search_res = DAOMailListItem::get_message_list($p_grid_pager_current);
if (count($search_res) == 0 && $p_grid_pager_current>1)
{
	$p_grid_pager_current = $p_grid_pager_current-1;
	$search_res = DAOMailListItem::get_message_list($p_grid_pager_current);
}

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">

		<!--Widget header-->
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-envelope"></i> 
				<?php echo plugin_lang_get('main_menu_import_emails');?>	
			</h4>
		</div>

		<!--Widget body-->
		<div class="widget-body">
			<!--Widget toolbox-->
			<form id="manage-group-filter" method="post" action="<?php echo $this_page; ?>" class="form-inline">
				<fieldset>
					<input type="hidden" id="grid_pager_current" name="grid_pager_current" value="<?php echo $p_grid_pager_current; ?>">
					<div class="widget-toolbox padding-8 clearfix">
						<div id="manage-user-div" class="form-container">
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
									if (count($search_res) < config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE) )
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
                                <th class="small-caption">From</th>
                                <th class="small-caption">To</th>
                                <th class="small-caption">Subject</th>
                                <th class="small-caption">Date</th>
                                <th class="small-caption">Issue</th>
                            </tr>
                        </thead>

						<!--DATA-->
						<tbody>
							<?php 
								$mail_edit_page = plugin_page('view_email_record');

                                foreach ($search_res as $mail)
                                {
                                    $mail_link_detail = $mail_edit_page . "&mail_uid=" . $mail->uid;
                                    echo '<tr>
                                        <td class="small-caption"> <a href = "'.$mail_link_detail.'">' .htmlspecialchars($mail->from). '</a></td>
                                        <td class="small-caption"> <a href = "'.$mail_link_detail.'">' .htmlspecialchars($mail->to) . '</a></td>
                                        <td class="small-caption"> <a href = "'.$mail_link_detail.'">' .htmlspecialchars($mail->subject) . '</a></td>
                                        <td class="small-caption">' . $mail->date . '</td>
										<td class="small-caption">';
										if ($mail->bug_id != null)
										{
											echo '<a href="view.php?&id=' . strval($mail->bug_id). '"> #'.strval($mail->bug_id) ." : ".  $mail->bug_summary ."</a>";
										}
										echo '</th>
									</tr>';
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