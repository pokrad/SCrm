<?php
require_api("config_api.php");
plugin_require_api('core/MailParser.php');
plugin_require_api('core/DAOMailListItem.php');

header("Cache-Control: no cache");
session_cache_limiter("private_no_expire");

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( plugin_page('main_page'));
SCrmTools::print_main_menu("view_email");

$this_page = plugin_page('view_email_record');

$create_from_mail_uid = gpc_get_int('create_from_mail_uid',null);
$create_from_mail_hash = gpc_get_string('create_from_mail_hash',null);
$create_from_mail_customer_id = gpc_get_int('create_from_mail_customer_id',null);

if ($create_from_mail_uid != null)
{
	if (!DAOBugData::mail_hash_exists($create_from_mail_hash))
	{
		$bug_id = DAOMailListItem::create_bug_from_mail($create_from_mail_uid,$create_from_mail_hash);
		DAOBugData::update_record($bug_id,$create_from_mail_customer_id,$create_from_mail_hash);
		$link = "view.php?&id=" . strval($bug_id);
		print_header_redirect( $link );
		return;
	}
	else
	{
		trigger_error( "Email already imported ! Refresh page.", ERROR );
	}
}

$mail_uid = gpc_get_int('mail_uid',null);
if ($mail_uid == null)
{
	layout_page_end();
	return;	
}
$mail = DAOMailListItem::get_message($mail_uid);
if (!$mail)
{
	layout_page_end();
	return;	
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

			<?php
				if ($mail->bug_id == null) {
					$customer_select_rec = DAOCustomer::get_lookup_list();
					$customer_label = plugin_lang_get('table_bug_data_col_customer_id');
					$select_attributes_customer = 'id="create_from_mail_customer_id" name="create_from_mail_customer_id" value="'.$customer_id.'"';
					$required = "";
					if (config_get(SCrmPlugin::CFG_KEY_CUSTOMER_REQUIRED,false,true))
					{
						$select_attributes_customer .= "required";
						$required = '<span class="required">*</span>';
					}
					$customer_id  = DaoCustomer::get_customer_id_by_email($mail->email_from);
			
			?>
			<div class="widget-toolbox padding-8 clearfix">
				<form id="create-issue-from-email" method="post" action="<?php echo $this_page; ?>" class="form-inline">
					<input type="hidden" id="create_from_mail_uid" name="create_from_mail_uid" value="<?php echo $mail_uid;?>">
					<input type="hidden" id="create_from_mail_hash" name="create_from_mail_hash" value="<?php echo $mail->hash;?>">

					<fieldset>
						<div class="form-container">
							<div class="pull-left">
								<?php echo $customer_label . " : " .ScrmTools::format_select($customer_select_rec, $select_attributes_customer, "id", "customer_name", $customer_id); ?>
								<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo plugin_lang_get('email_import_as_issue_label');?>">							
							</div>
					</fieldset>
				</form>
			</div>
			<?php } ?>

			<!--Widget main-->
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
                        <thead>
                            <tr>
                                <th class="small-caption">From: <?php echo htmlspecialchars($mail->from)?></th>
                                <th class="small-caption">To: <?php echo htmlspecialchars($mail->to)?></th>
                                <th class="small-caption">Subject: <?php echo htmlspecialchars($mail->subject)?></th>
                                <th class="small-caption">Date: <?php echo $mail->date?></th>
								<th class="small-caption">
									<?php
										if ($mail->bug_id != null)
										{
											echo '<a href="view.php?&id=' . strval($mail->bug_id). '"> #'.strval($mail->bug_id) ." : ".  $mail->bug_summary ."</a>";
										}
									?>
								</th>
                            </tr>
                        </thead>

						<!--DATA-->
						<tbody>
							<?php 
								$attachement_list = "";
								foreach($mail->parts as $part)
								{
									if ($part->attachement_filename != null)
									{
										$attachement_list .= '<span class="label label-default">' . $part->attachement_filename . '</span>&nbsp;';
									}
								}


								if ($attachement_list != "")
								{
									echo '<tr>
										<th class="small-caption" colspan="5">Attachements: ' . $attachement_list . '</th>
									</tr>';
								}


								echo '<tr>
									<td colspan="5">
										<pre class = "page-content">';
										foreach($mail->parts as $part)
										{
											//echo "Part:".$part->part_number. PHP_EOL;
											//echo "Subtype:".$part->subtype . PHP_EOL;
											//echo "Encoding:".$part->encoding.":".$part->encoding_name. PHP_EOL;
											echo "Mail from:".$mail->email_from. PHP_EOL;
											//print_r($part->parameters);

											if ($part->attachement_filename == null)
											{
												if ($part->subtype == 'html')
												{
													echo MailParser::html_to_text($part->data);
												}
												else
												{
													echo htmlentities($part->data);
												}
												//echo strip_tags($part->data) ."\r\n";
												//echo htmlentities($part->data);
											}
										}
										echo '</pre>
									</td>
								</tr>';
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