<?php
require_api("config_api.php");
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
SCrmTools::print_main_menu("main_page");

$stats = DAOStatistics::getTotalCounts();
$resStat = db_fetch_array( $stats );


$available_columns_array = columns_get_all();
$available_columns = implode( ', ', $available_columns_array );


$submit_save = gpc_get_string('submit_save','');
if ($submit_save != '')
{
	$field_MANAGE_TABLES_TRESHOLD = gpc_get_int('MANAGE_TABLES_TRESHOLD');
	$field_MANAGE_CUSTOMER_VAULT_TRESHOLD = gpc_get_string('MANAGE_CUSTOMER_VAULT_TRESHOLD');

	$field_CUSTOMER_REQUIRED = false;
	if (gpc_get_string('CUSTOMER_REQUIRED','') == 'checked')
	{
		$field_CUSTOMER_REQUIRED = true;
	}

	$field_SERVICE_REQUIRED = false;
	if (gpc_get_string('SERVICE_REQUIRED','') == 'checked')
	{
		$field_SERVICE_REQUIRED = true;
	}

	$field_TIME_SPENT_REQUIRED = false;
	if (gpc_get_string('TIME_SPENT_REQUIRED','') == 'checked')
	{
		$field_TIME_SPENT_REQUIRED = true;
	}

    $field_TABLE_ROWS_PER_PAGE = gpc_get_int('TABLE_ROWS_PER_PAGE');

	$field_PRINT_ISSUE_COLUMN_LIST = gpc_get_string('PRINT_ISSUE_COLUMN_LIST','');

    config_set(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD, $field_MANAGE_TABLES_TRESHOLD);
    config_set(SCrmPlugin::CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD, $field_MANAGE_CUSTOMER_VAULT_TRESHOLD);
    config_set(SCrmPlugin::CFG_KEY_CUSTOMER_REQUIRED, $field_CUSTOMER_REQUIRED);
    config_set(SCrmPlugin::CFG_KEY_SERVICE_REQUIRED, $field_SERVICE_REQUIRED);
    config_set(SCrmPlugin::CFG_KEY_TIME_SPENT_REQUIRED,$field_TIME_SPENT_REQUIRED);
    config_set(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE, $field_TABLE_ROWS_PER_PAGE);
    config_set(SCrmPlugin::CFG_KEY_PRINT_ISSUE_COLUMN_LIST, $field_PRINT_ISSUE_COLUMN_LIST);
}
else
{
    $field_MANAGE_TABLES_TRESHOLD = config_get(SCrmPlugin::CFG_KEY_MANAGE_TABLES_TRESHOLD,UPDATER);
    $field_MANAGE_CUSTOMER_VAULT_TRESHOLD = config_get(SCrmPlugin::CFG_KEY_MANAGE_CUSTOMER_VAULT_TRESHOLD,UPDATER);
    $field_CUSTOMER_REQUIRED = config_get(SCrmPlugin::CFG_KEY_CUSTOMER_REQUIRED,false);
    $field_SERVICE_REQUIRED = config_get(SCrmPlugin::CFG_KEY_SERVICE_REQUIRED,false);
    $field_TIME_SPENT_REQUIRED = config_get(SCrmPlugin::CFG_KEY_TIME_SPENT_REQUIRED,false);
    $field_TABLE_ROWS_PER_PAGE = config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE,25);
	$field_PRINT_ISSUE_COLUMN_LIST = config_get(SCrmPlugin::CFG_KEY_PRINT_ISSUE_COLUMN_LIST,'');
}

?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
        <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
                <i class="fa fa-info ace-icon"></i>			<?php echo plugin_lang_get('main_page_info_title')?>
            </h4>
        </div>

        <form id="edit_config_record" method="post" enctype="multipart/form-data">
            <input type="hidden" id="field_id" name="field_id" maxlength="40" style="width:100%;" value="<?php echo $field_id;?>">
            <input type="hidden" name="page_callback_url" id = "page_callback_url" value = "<?php echo $page_callback_url;?>">

            <div class="widget-body">
                <div class="widget-main no-padding">
                    <div class="table-responsive">
                        <table id="manage-overview-table" class="table table-hover table-bordered table-condensed">
                            <tbody>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_version')?></th>
                                    <td><?php echo SCrmTools::get_version();?></td>
                                </tr>
                                <tr class="spacer">
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_service_count')?></th>
                                    <td><?php echo $resStat['service_count'];?></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_normative_count')?></th>
                                    <td><?php echo $resStat['normative_count'];?></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_contact_count')?></th>
                                    <td><?php echo $resStat['contact_count'];?></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_group_count')?></th>
                                    <td><?php echo $resStat['group_count'];?></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('main_page_info_customer_count')?></th>
                                    <td><?php echo $resStat['customer_count'];?></td>
                                </tr>

                                <?php if (access_has_global_level(ADMINISTRATOR)){ ?>

                                <tr class="spacer">
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_manage_tables_treshold')?></th>
                                    <td>
                                        <select id="MANAGE_TABLES_TRESHOLD" name="MANAGE_TABLES_TRESHOLD" class="input-sm">
                                        <?php
                                            print_project_access_levels_option_list( (int)$field_MANAGE_TABLES_TRESHOLD );
                                        ?>
                                        </select>
                                    </td>                                
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_manage_customer_vault')?></th>
                                    <td>
                                        <select id="MANAGE_CUSTOMER_VAULT_TRESHOLD" name="MANAGE_CUSTOMER_VAULT_TRESHOLD" class="input-sm">
                                        <?php
                                            print_project_access_levels_option_list( (int)$field_MANAGE_CUSTOMER_VAULT_TRESHOLD );
                                        ?>
                                        </select>
                                    </td>                                
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_customer_required')?></th>
                                    <td>
                                        <label class="inline">
                                        <?php 
                                            echo '<input id="CUSTOMER_REQUIRED" name = "CUSTOMER_REQUIRED" class="ace input-sm" type="checkbox" value="checked" ' . ( $field_CUSTOMER_REQUIRED ? 'checked' : '' ) . '/>';
                                        ?>
                                        <span class="lbl"></span>
                                    </td>                                
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_service_required')?></th>
                                    <td>
                                        <label class="inline">
                                        <?php 
                                            echo '<input id="SERVICE_REQUIRED" name = "SERVICE_REQUIRED" class="ace input-sm" type="checkbox" value="checked" ' . ( $field_SERVICE_REQUIRED ? 'checked' : '' ) . '/>';
                                        ?>
                                        <span class="lbl"></span>
                                    </td>                                
                                </tr>
                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_time_spent_required')?></th>
                                    <td>
                                        <label class="inline">
                                        <?php 
                                            echo '<input id="TIME_SPENT_REQUIRED" name = "TIME_SPENT_REQUIRED" class="ace input-sm" type="checkbox" value="checked" ' . ( $field_TIME_SPENT_REQUIRED ? 'checked' : '' ) . '/>';
                                        ?>
                                        <span class="lbl"></span>
                                    </td>                                
                                </tr>

                                <tr>
                                    <th class="category"><?php echo plugin_lang_get('configuration_table_rows_per_page')?></th>
                                    <td>

                                        <select id="TABLE_ROWS_PER_PAGE" name="TABLE_ROWS_PER_PAGE" class="input-sm">
                                            <?php 
                                                for ($x = 1; $x <= 20; $x++) 
                                                {
                                                    $tNumRows = $x*5;
                                                    if ($tNumRows == $field_TABLE_ROWS_PER_PAGE)
                                                    {
                                                        echo '<option value="'.$tNumRows.'" selected="selected">'.$tNumRows.'</option>';
                                                    }
                                                    else
                                                    {
                                                        echo '<option value="'.$tNumRows.'">'.$tNumRows.'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </td>                                
                                </tr>

								<tr>
									<th class="category">
										<?php echo lang_get('all_columns_title')?>
									</th>
									<td>
										<textarea readonly name="field_available_columns" id="field_available_columns" class="form-control" rows="7" style="width:100%;"><?php echo $available_columns;?></textarea>
									</td>
								</tr>

								<tr>
									<th class="category">
										<?php echo lang_get('print_issues_columns_title')?>
									</th>
									<td>
										<textarea name="PRINT_ISSUE_COLUMN_LIST" id="PRINT_ISSUE_COLUMN_LIST" class="form-control" rows="7" style="width:100%;"><?php echo $field_PRINT_ISSUE_COLUMN_LIST;?></textarea>
									</td>
								</tr>


                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                    <?php if (access_has_global_level(ADMINISTRATOR)){ ?>

                    <div class="widget-toolbox padding-8 clearfix">
                        <input type="submit" id="submit_save" name ="submit_save" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('global_cmd_save_record')?>" >
                    </div>

                    <?php } ?>

                </div>
            </div>
        </form>
    </div>
</div>

<?php
layout_page_end();