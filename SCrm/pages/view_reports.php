<?php
header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");
auth_ensure_user_authenticated();
compress_enable();

layout_page_header( plugin_lang_get( 'title' ) );
echo '<script type="text/javascript" src="' . plugin_file('view_reports.js') . '"></script>' .

layout_page_begin( plugin_page('main_page'));

SCrmTools::print_main_menu("view_reports");

$this_page = plugin_page('view_reports');

$reset_query = gpc_get('reset_query',null);
$source_query_id = gpc_get('source_query_id',null);
$apply_filter = gpc_get('apply_filter',null);
$period_select = gpc_get_string('report_period_select',null);

if ($reset_query != null)
{
	$filter = [];
	$filter['hide_status'] = ['none'];
	$f_filter_id = -1;
	$period_select = null;
}
else if ($period_select != null || $source_query_id != null)
{

	if ($source_query_id != null)
	{
		//Load existing saved filter !!!
		$f_filter_id = gpc_get_int('source_query_id',-1);
		$filter = filter_get( $f_filter_id, null );
		if($filter ===null) 
		{
			//Non existing ? reset filter
			$filter = [];
			$f_filter_id = -1;
		}
	}
	else
	{
		//Get temp filter from post...
		$filter = filter_gpc_get();	
		$f_filter_id = -1;
	}

	if ($period_select != null)
	{
		$date_current = new DateTime('now');

		if ($period_select == DAOStatistics::REPORT_PERIOD_CREATED_THIS_MONTH || $period_select == DAOStatistics::REPORT_PERIOD_MODIFIED_THIS_MONTH || $period_select == DAOStatistics::REPORT_PERIOD_CLOSED_THIS_MONTH)
		{
			//this month
			$date_first = new DateTime($date_current->format('Y').'/'.$date_current->format('m').'/'.'1'); 
			$date_last = $date_current;
			$date_last->modify('last day of ' . $date_current->format('Y').'-'.$date_current->format('m'));
		}
		else
		{
			//Last month
			$date_current = new DateTime('now');
			$date_first = new DateTime($date_current->format('Y').'/'.$date_current->format('m')-1 . '/'.'1'); 
			$date_last = $date_current;
			$date_last->modify('last day of ' . $date_current->format('Y').'-'.$date_current->format('m')-1);
		}

		if ($period_select == DAOStatistics::REPORT_PERIOD_CREATED_THIS_MONTH || $period_select == DAOStatistics::REPORT_PERIOD_CREATED_LAST_MONTH)
		{
			//Date creted
			$filter['filter_by_date'] = true;
			$filter['start_year'] = $date_first->format('Y');
			$filter['start_month'] = $date_first->format('m');
			$filter['start_day'] = $date_first->format('d');
		
			$filter['end_year'] = $date_last->format('Y');
			$filter['end_month'] = $date_last->format('m');
			$filter['end_day'] = $date_last->format('d');
		}
		else
		{
			//Date modified
			$filter['filter_by_last_updated_date'] = true;
			$filter['last_updated_start_year'] = $date_first->format('Y');
			$filter['last_updated_start_month'] = $date_first->format('m');
			$filter['last_updated_start_day'] = $date_first->format('d');
			
			$filter['last_updated_end_year'] = $date_last->format('Y');
			$filter['last_updated_end_month'] = $date_last->format('m');
			$filter['last_updated_end_day'] = $date_last->format('d');
		}

		$filter['hide_status'] = -2;
		if ($period_select == DAOStatistics::REPORT_PERIOD_CLOSED_THIS_MONTH || $period_select == DAOStatistics::REPORT_PERIOD_CLOSED_LAST_MONTH)
		{
			$filter['status'] = 90;
		}
	}
}
else
{
	//Get temp filter from post...
	$filter = filter_gpc_get();	
	$f_filter_id = -1;
}

$filter = filter_ensure_valid_filter( $filter );

$f_for_screen = gpc_get_bool( 'for_screen', true );
$action  = $this_page;
$view_report_action = plugin_page('view_reports_page.php');

$f_static = gpc_get_bool( 'static', false );
$f_view_type = gpc_get_string( 'view_type', $filter['_view_type'] );
$filter['_view_type'] = $f_view_type;

$stored_queries_arr = filter_db_get_available_queries();
$report_title = plugin_lang_get('report_label');

?>
<div class="space-10"></div>

<div class="col-md-12 col-xs-12">
	<div class="widget-box widget-color-blue2  hidden-print">
		<div class="widget-header widget-header-small hidden-print">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-filter', 'ace-icon' ); ?>
				<?php echo lang_get('filters') ?>
			</h4>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding">
				<form method="post" name="load_filters" id="load_filters" action="<?php echo $action;?>">
					<input type="hidden" name="type" value="<?php echo FILTER_ACTION_LOAD ?>" />

					<div>
						<label>
							<span class="lbl padding-6">&nbsp;</span>
						</label>
						<?php
						//Select Filter !!!
						if( count( $stored_queries_arr ) > 0 ) { ?>
							<label>
								<?php echo lang_get( 'load' ) ?>:
								<select class="input-s" name="source_query_id">
									<option value="-1"></option>
									<?php
									
									foreach( $stored_queries_arr as $query_id => $query_name ) {
										echo '<option value="' . $query_id . '" ';
										check_selected( $query_id, $f_filter_id );
										echo '>' . string_display_line( $query_name ) . '</option>';
									};?>
								</select>
							</label>
							<span class="lbl padding-8">&nbsp;</span>
						<?php
						}
						?>
						<label>
							<?php
								echo plugin_lang_get('report_period') . ":"; 
								echo DAOStatistics::get_report_period_select($period_select,$action); 
							?>
						</label>
						<span class="lbl padding-8">&nbsp;</span>

						<label>
							<input type="submit" id="reset_query" name = "reset_query" class="btn btn-primary btn-sm btn-white btn-round no-float" name="filter" value="<?php echo lang_get( 'reset_query' )?>" />
						</label>
					</div>
				</form>


				<form method="post" name="filters" id="filters_form_open">
					<input type="hidden" name="type" value="1" />
					<input type="hidden" name="view_type" value="<?php echo $filter['_view_type']; ?>" />
					<?php
						if( $f_for_screen == false ) {
							print '<input type="hidden" name="print" value="1" />';
							print '<input type="hidden" name="offset" value="0" />';
						};
						echo '<div class="table-responsive">';
							filter_form_draw_inputs( $filter, $f_for_screen, $f_static );
						echo '</div>';
					?>

					<div class="widget-toolbox clearfix hidden-print">
						<div class="btn-toolbar pull-left">
							<span class="lbl padding-6">&nbsp;</span>
						</div>
						<div class="btn-toolbar pull-left">
							<div class="form-inline">
								<input type="submit" formaction="<?php echo $action; ?>" class="btn btn-primary btn-sm btn-white btn-round no-float" name="apply_filter" value="<?php echo lang_get( 'use_query' )?>" />
								<input type="submit" formaction = "<?php echo $view_report_action; ?>" class="btn btn-primary btn-sm btn-white btn-round no-float" name="apply_filter_to_separate_page" value="<?php echo lang_get('print')?>"/>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="space-10"></div>

<?php

if ($apply_filter != null)
{
	$report_title .= DAOStatistics::get_description_from_filter($filter);
	$columns_str = config_get(SCrmPlugin::CFG_KEY_PRINT_ISSUE_COLUMN_LIST,'');
	$columns = array_map('trim', explode(',', $columns_str));

	$f_page_number = gpc_get_int( 'page_number', 1 );
	$per_page = -1;
	$bug_count = null;
	$page_count = null;
	$bug_rows = filter_get_bug_rows( $f_page_number, $per_page, $page_count, $bug_count,$filter );
	$row_count = count( $bug_rows );

	echo '<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="fa fa-columns ace-icon"></i>'.$report_title .
			'</h4>
		</div>

		<div class="widget-body">
			<div class="widget-main no-padding">';

	DaoStatistics::write_bug_rows($report_type, $columns, $bug_rows, $filter);

	echo '</div></div></div>';			
	echo "</div>";
}
layout_page_end();