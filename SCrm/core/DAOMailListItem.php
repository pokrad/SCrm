<?php
plugin_require_api('core/MailParser.php');
require_once( 'api/soap/mc_file_api.php' );

class DAOMailListItem
{
	public int $uid;
	public ?string $from;
	public ?string $email_from;
	public ?string $to;
	public ?string $email_to;
	public string $subject;
	public int $udate;
	public string $date;
	public int $size;
	public $hash;
	public ?int $bug_id=null;
	public ?string $bug_summary=null;
	public ?int $customer_id=null;
	public ?string $customer_name=null;
	public $parts = [];

	public function __construct(int $p_uid, string $p_from,string $p_to, string $p_subject, int $p_udate, int $p_size)
	{
		$this->uid = $p_uid;
		$this->from = mb_decode_mimeheader($p_from);
		$this->to = mb_decode_mimeheader($p_to);
		$this->subject = mb_decode_mimeheader($p_subject);
		$this->udate = $p_udate;
		$this->size = $p_size;

		$this->email_from = MailParser::strip_mail_address($p_from);
		$this->email_to = MailParser::strip_mail_address($p_to);
		$this->date = date("Y.m.d H:m:s",$p_udate);
		$this->hash = md5($p_from . $p_to . $p_subject . strval($p_udate) . strval($p_size));
	}

	private static function create_parser()
	{
		return new MailParser(
			p_host: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_HOST,''),
			p_port: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_PORT,0),
			p_protocol: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_TYPE,'imap'),
			p_user: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_USER,''),
			p_pass: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_PWD,''),
			p_use_ssl: config_get(SCrmPlugin::CFG_KEY_MAIL_IMPORT_SSL,false)
		);
	}

	/*
	Get message list from mailbox
	*/
	public static function get_message_list($p_page_number=1) 
	{
		try{
			$parser = static::create_parser();
			$parser->open();
			$search_res = $parser->list($p_page_number);
			$parser->close();

			$res = [];
			foreach($search_res as $mail)
			{
				$mail_item = new DAOMailListItem
				(
					$mail->uid,
					$mail->from,
					$mail->to,
					$mail->subject,
					$mail->udate,
					$mail->size,
				);
				$res[] = $mail_item;
			}
			static::fill_issue_details($res);

			return $res;
		}
		catch (Exception $e) 
		{
			if ($parser!=null)
			{
				$parser->close();
			}
			trigger_error( $e->getMessage(), ERROR );
		}
	}


	public static function get_message(int $p_message_num)
	{
		try{
			$parser = static::create_parser();
			$parser->open();
			$search_res = $parser->list(1,$p_message_num);

			if (count($search_res)<1)
			{
				return false;
			}

			$mail = $search_res[0];
			$mail_item = new DAOMailListItem
			(
				$search_res[0]->uid,
				$mail->from,
				$mail->to,
				$mail->subject,
				$mail->udate,
				$mail->size,
			);
			$mail_item->parts = $parser->fetch_message($mail->uid);
			$parser->close();

			$res[] = $mail_item;
			static::fill_issue_details($res);
			return $res[0];
		}
		catch (Exception $e) 
		{
			if ($parser!=null)
			{
				$parser->close();
			}
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	/*
	Pair mail message with issue using mail hash
	*/
	private static function fill_issue_details(&$p_item_list)
	{
		$hash_list = "";
		foreach($p_item_list as $item)
		{
			if ($hash_list!='')
			{
				$hash_list .= ",'{$item->hash}'";
			}
			else
			{
				$hash_list .= "'{$item->hash}'";
			}
		}

		if ($hash_list=="")
		{
			//No hashes at the current page - nothing to do
			return;
		}

		$query = new DbQuery();
		$table_bug_data = db_get_table('bug');
		$table_scrm_bug_data = plugin_table('bug_data');
		$table_scrm_customer = plugin_table('customer');

		$sql = "SELECT 
			BT.id,
			BT.summary,
			BDT.mail_hash,	
			CU.id AS customer_id,
			CU.customer_name
		FROM {$table_scrm_bug_data} BDT
		JOIN {$table_bug_data} BT ON BT.id = BDT.bug_id
		LEFT JOIN {$table_scrm_customer} CU ON CU.id = BDT.customer_id
		WHERE BDT.mail_hash IN ({$hash_list})";
		
		$query->sql( $sql );
		$list_rec = $query->execute();
		while( $row = db_fetch_array( $list_rec ) ) 
		{
			foreach ($p_item_list as $mail_item)
			{
				if ($mail_item->hash == $row["mail_hash"])
				{
					$mail_item->bug_id = $row["id"];
					$mail_item->bug_summary = $row["summary"];
					$mail_item->customer_id = $row["customer_id"];
					$mail_item->customer_name = $row["customer_name"];
				}
			}
		}
	}


	public static function create_bug_from_mail(int $p_message_num, string $p_check_hash )
	{
		$mail = static::get_message($p_message_num); 
		if (!$mail)
		{
			return false;
		}

		if ($p_check_hash != $mail->hash)
		{
			trigger_error("xxx" , ERROR );
		}

		$user_id = auth_get_current_user_id();
		$project_id = helper_get_current_project();

		$t_bug_data = new BugData();
		$t_bug_data->project_id				= $project_id;
		$t_bug_data->reporter_id			= $user_id;
		$t_bug_data->profile_id				= 0;
		$t_bug_data->handler_id				= 0;
		$t_bug_data->view_state				= (int) config_get( 'default_bug_view_status' );
		$t_bug_data->reproducibility		= (int) config_get( 'default_bug_reproducibility' );
		$t_bug_data->severity				= (int) config_get( 'default_bug_severity' );
		$t_bug_data->projection				= (int) config_get( 'default_bug_projection' );
		$t_bug_data->eta					= (int) config_get( 'default_bug_eta' );
		$t_bug_data->steps_to_reproduce		= config_get( 'default_bug_steps_to_reproduce' );
		$t_bug_data->additional_information	= config_get( 'default_bug_additional_info' );
		$t_bug_data->resolution				= config_get( 'default_bug_resolution' );
		$t_bug_data->status					= config_get( 'bug_submit_status' );
		$t_bug_data->due_date				= date_get_null();
		$t_bug_data->summary				= $mail->subject;

		$description = "";
		foreach($mail->parts as $part)
		{
			if ($part->attachement_filename == null)
			{
				if ($part->subtype == 'html')
				{
					$description .= MailParser::html_to_text($part->data) . PHP_EOL . PHP_EOL;
				}
				else
				{
					$description .=  htmlentities($part->data) . PHP_EOL . PHP_EOL;
				}
			}
		}
		$t_bug_data->description			= $description;
		$t_bug_id = $t_bug_data->create();


		foreach($mail->parts as $part)
		{
			if ($part->attachement_filename != null)
			{
				static::add_attachement_file($t_bug_id,$part,$p_check_hash);
			}
		}


		return $t_bug_id;



		/*
		$t_bug_data->build					= '';
		$t_bug_data->platform				= '';
		$t_bug_data->os						= '';
		$t_bug_data->os_build				= '';
		$t_bug_data->version				= '';

		$t_bug_data->category_id			= (int) $this->_mailbox[ 'global_category_id' ];
		$t_priority = $this->verify_priority( $p_email[ 'Priority' ] );
		$t_bug_data->priority				= (int) $t_priority;
		*/
	}

	private static function add_attachement_file( $p_bug_id, &$p_part, $p_check_hash)
	{
		//$t_file_name = $p_check_hash."-".$p_part->attachement_filename;
		$t_file_name = $p_part->attachement_filename;

		$t_max_length = ( ( defined( 'DB_FIELD_SIZE_FILENAME' ) ) ? DB_FIELD_SIZE_FILENAME : 250 );
		if ( strlen( $t_file_name ) > $t_max_length )
		{
			$t_ext = ".".pathinfo($t_file_name, PATHINFO_EXTENSION);
			$t_file_name = substr( $t_file_name, 0, ( $t_max_length - strlen( $t_ext )) ) .$t_ext;
		}		
		$t_attachment_id = mci_file_add( $p_bug_id, $t_file_name, $p_part->data, $p_part->subtype, 'bug' );
		return $t_attachment_id;
	}

}