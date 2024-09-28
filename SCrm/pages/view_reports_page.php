<?php
plugin_require_api('core/DAOStatistics.php');

header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");
auth_ensure_user_authenticated();
compress_enable();

$report_title = plugin_lang_get('report_label');
$filter = filter_gpc_get();	
$filter = filter_ensure_valid_filter( $filter );
$f_for_screen = gpc_get_bool( 'for_screen', true );
$report_title .= DAOStatistics::get_description_from_filter($filter);

$columns_str = config_get(SCrmPlugin::CFG_KEY_PRINT_ISSUE_COLUMN_LIST,'');
$columns = array_map('trim', explode(',', $columns_str));

$f_page_number = gpc_get_int( 'page_number', 1 );
$per_page = -1;
$bug_count = null;
$page_count = null;
$bug_rows = filter_get_bug_rows( $f_page_number, $per_page, $page_count, $bug_count,$filter );
$row_count = count( $bug_rows );

layout_page_header($report_title);

layout_page_content_begin();

echo '<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="fa fa-columns ace-icon"></i>'.$report_title.				
		'</h4>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">';

DaoStatistics::write_bug_rows($report_type, $columns, $bug_rows, $filter);

echo '</div></div></div>';	

layout_page_content_end();
html_body_end();
html_end();
