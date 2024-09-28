<?php
require_api("config_api.php");
plugin_require_api('core/SCrmTools.php');
plugin_require_api('core/DAOStatistics.php');

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
SCrmTools::print_main_menu("main_page");

$stats = DAOStatistics::getTotalCounts();
$resStat = db_fetch_array( $stats );

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
                                    <td><?php echo SCrmPlugin::get_version();?></td>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
layout_page_end();