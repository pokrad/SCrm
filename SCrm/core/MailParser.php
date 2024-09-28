<?php

class MailMessagePart
{
	public $parameters = [];
	public $subparts = [];
	public string $data;
	public ?string $attachement_filename = null;
	public string $subtype;
	public int $encoding;
	public string $encoding_name;
	public string $part_number;
}

class MailParser
{
	private string $host = '';
	private int $port = 0;
	private string $user = '';
	private string $protocol = 'imap';
	private string $pass = '';
	private bool $use_ssl=false;
	private string $folder="INBOX";
	private $f_connection = null;

	public function __construct($p_host='', $p_port=0,$p_protocol='imap', $p_user='', $p_pass='', $p_use_ssl=false) 
	{
		$this->host = $p_host;
		$this->port = $p_port;
		$this->protocol = $p_protocol;
		$this->user = $p_user;
		$this->pass = $p_pass;
		$this->use_ssl=$p_use_ssl;
	}

	public function __get( $p_name ) {
		return $this->{$p_name};
	}

	public function __isset( $p_name ) {
		return isset( $this->{$p_name} );
	}

	public function __set( $p_name, $p_value ) {
		$this->$p_name = $p_value;
	}


	public function open($p_folder="INBOX")
	{
		$protocol = $this->protocol;
		if ($this->use_ssl==false)
		{
			$protocol .= "/novalidate-cert";
		}
		$t_mailbox = "{"."$this->host:$this->port/$protocol"."}$p_folder";
		$this->f_connection=(imap_open($t_mailbox,$this->user,$this->pass));
		if (!$this->f_connection)
		{
			trigger_error( "Error opening mailbox:".$t_mailbox, ERROR );
		}
	}

	public function close()
	{
		if ($this->f_connection != null)
		{
			imap_close($this->f_connection);
		}
	}

	public function list($p_page_number=1, ?int $p_mesage_num = null)
	{
		$_rows_per_page = config_get(SCrmPlugin::CFG_KEY_TABLE_ROWS_PER_PAGE);
		$info = imap_check($this->f_connection);
		if ($p_mesage_num ==null)
		{
			$_range_end = $info->Nmsgs - ($p_page_number-1)*$_rows_per_page;
			if ($_range_end>$info->Nmsgs){
				$_range_end = $info->Nmsgs;
			}
			$_range_start = $_range_end-$_rows_per_page+1;
			if ($_range_start <1)
			{
				$_range_start = 1;
			}
			$range = "{$_range_start}:{$_range_end}";
		}
		else
		{
			$range = "{$p_mesage_num}";
		}
		$search_res= imap_fetch_overview($this->f_connection,$range);
		usort(
			$search_res, 
	function($a, $b) {
				return($b->udate>$a->udate);
			}
		);
		return $search_res;
	}

	public function search($p_subject_pattern,$p_only_unseen=false)
	{
		$search_criteria = "";
		if ($p_only_unseen)
		{
			$search_criteria .= "UNSEEN ";
		}
		$search_criteria .= 'SUBJECT "'.$p_subject_pattern.'"';
		$response = imap_search($this->f_connection, $search_criteria);
		return $response;
	}

	public static function strip_mail_address($address) 
	{
		$str = 'before-str-after';
		preg_match('/<([^>]+)>/', $address, $matches);
		return $matches[1];
		/*
		if (preg_match('/<([^>]+)>/', $address, $match) == 1) 
		{
    		return  $match[1];
		}
		else
		{
			return "";
		}
		*/
	}	

	public function fetch_message($p_mesasage_id) 
	{
		// Structure
		$structure = imap_fetchstructure($this->f_connection,$p_mesasage_id);
		$result_parts = [];
		if ($structure->parts)
		{
			//multipart
			foreach ($structure->parts as $p_part_no0=>$p)
			{
				$result_parts[$p_part_no0] = $this->fetch_message_part($p_mesasage_id,$p,$p_part_no0+1);
			}
		}
		else
		{
			//simple
			$result_parts[0] = $this->fetch_message_part($p_mesasage_id,$structure,0);  // pass 0 as part-number
		}
		return $result_parts;
	}

	private function fetch_message_part($p_mesasage_id, $p_structure, $p_part_no ) 
	{
		// $p_part_no = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		$msg_part_object = new MailMessagePart();
		$msg_part_object->part_number = $p_part_no;
	
		// PARAMETERS
		if ($p_structure->parameters)
		{
			foreach ($p_structure->parameters as $x)
			{
				$param_name = strtolower($x->attribute);
				$msg_part_object->parameters[$param_name] = $x->value;
				if ($param_name == "filename" || $param_name == "name")
				{
					$msg_part_object->attachement_filename = $x->value;
				}
			}
		}
		if ($p_structure->dparameters){
			foreach ($p_structure->dparameters as $x)
			{
				$param_name = strtolower($x->attribute);
				$msg_part_object->parameters[$param_name] = $x->value;
				if ($param_name == "filename" || $param_name == "name")
				{
					$msg_part_object->attachement_filename = $x->value;
				}
			}
		}

		// DECODE DATA
		$data = "";
		if ($p_part_no)
		{
			$data = imap_fetchbody($this->f_connection,$p_mesasage_id,$p_part_no);  // multipart
		}
		else
		{
			$data = imap_body($this->f_connection,$p_mesasage_id);  // simple
		}
		$msg_part_object->encoding = $p_structure->encoding;
		$msg_part_object->encoding_name = $this->get_encoding_name($p_structure->encoding);

		// Any part may be encoded, even plain text messages, so check everything.
		if ((!$p_structure->encoding) || ($p_structure->encoding == ENC7BIT))
		{
			$body = imap_qprint($data);
			$body = $this->to_utf_8($body, $msg_part_object->parameters['charset']);
		} 
		else if ($p_structure->encoding == ENC8BIT)
		{
			$body = imap_8bit($data); //imap_utf8
			$body = $this->to_utf_8($body,$msg_part_object->parameters['charset']);
		} 
		else if ($p_structure->encoding == ENCBINARY)
		{
			$body = imap_binary($data);
		} 
		else if ($p_structure->encoding == ENCBASE64)
		{
			$body = imap_base64($data,);
		} 
		else if ($p_structure->encoding == ENCQUOTEDPRINTABLE)
		{
			$body = imap_qprint($data);
			$body = $this->to_utf_8($body,$msg_part_object->parameters['charset']);
		}
		$msg_part_object->data = $body;

		//Subtype;
		$msg_part_object->subtype = strtolower($p_structure->subtype);

		// SUBPART RECURSION
		//if ($p_structure->parts) {
		//	foreach ($p_structure->parts as $p_part_no0=>$p2){
		//		$msg_part_object->subparts = $this->fetch_message_part($p_mesasage_id,$p2,$p_part_no.'.'.($p_part_no0+1));  // 1.2, 1.2.1, etc.
		//	}
		//}

		return $msg_part_object;
	}

	private function to_utf_8($data, $p_character_encoding=null)
	{
		$encoding = $p_character_encoding;
		if ($encoding == null) $encoding = mb_detect_encoding($data, mb_detect_order(),true);
		return iconv($encoding, "UTF-8", $data);
	}

	public function get_encoding_name($p_encoding) {
		// See imap_fetchstructure() documentation for explanation.
		$encodings = [
			0 => 'ENC7BIT',
			1 => 'ENC7BIT',
			2 => 'ENCBINARY',
			3 => 'ENCBASE64',
			4 => 'ENCQUOTEDPRINTABLE',
			5 => 'OTHER',
		];
		return $encodings[$p_encoding];
	}


	/*HtmlToText
	Simplified version from  https://github.com/masroore/php-html2text/tree/master/src
	*/
    private static array $nbspCodes = ["\xc2\xa0", '\\u00a0'];
    private static array $zwnjCodes = ["\xe2\x80\x8c", '\\u200c'];
	private static array $doubleNewlineTags = ['p', 'h[1-6]', 'dl', 'dt', 'dd', 'ol', 'ul','dir', 'address', 'blockquote', 'center', 'hr', 'pre', 'form',	'textarea', 'table',];
	private static array $singleNewlineTags = ['div', 'li', 'fieldset', 'legend', 'tr', 'th', 'caption','thead', 'tbody', 'tfoot',];

    public static function html_to_text(string $html): string
    {
        $text = $html;
        // replace line breaks
        $text = preg_replace("/\n|\r\n|\r/", ' ', $text);
        // replace spaces
        $text = preg_replace('/&nbsp;/i', ' ', $text);
        // remove content in script tags.
        $text = preg_replace('%<\s*script[^>]*>[\s\S]*?</script>%im', '', $text);
        // remove content in style tags.
        $text = preg_replace('%<\s*style[^>]*>[\s\S]*?</style>%im', '', $text);
        // remove content in comments.
        $text = preg_replace('/<!--.*?-->/m', '', $text);
        // remove !DOCTYPE
        $text = preg_replace('/<!DOCTYPE.*?>/i', '', $text);
        foreach (static::$doubleNewlineTags as $tag) {
            $text = preg_replace('%</?\s*' . $tag . '[^>]*>%i', "\n\n", $text);
        }
        foreach (static::$singleNewlineTags as $tag) {
            $text = preg_replace('%<\s*' . $tag . '[^>]*>%i', "\n\n", $text);
        }
        // Replace <br> and <br/> with a single newline
        $text = preg_replace('%<\s*br[^>]*/?\s*>%i', "\n", $text);
        // Remove all remaining tags.
        $text = preg_replace('/(<([^>]+)>)/', '', $text);
        // Trim rightmost whitespaces for all lines
        $text = preg_replace("/([^\n\\S]+)\n/", "\n", $text);
        $text = preg_replace("/([^\n\\S]+)$/", '', $text);
        // Make sure there are never more than two consecutive linebreaks.
        $text = preg_replace("/\n{2,}/", "\n\n", $text);
        // Remove newlines at the beginning of the text.
        $text = preg_replace("/^\n+/", '', $text);
        // Remove newlines at the end of the text.
        $text = preg_replace("/\n+$/", '', $text);
        // Decode HTML entities.
        $text = preg_replace_callback('/&([^;]+);/', fn ($m) => html_entity_decode($m[0]), $text);
        // return $text;
        return self::html_to_text_process_whitespace_newlines($text);
    }

    /**
     * Unify newlines; in particular, \r\n becomes \n, and
     * then \r becomes \n. This means that all newlines (Unix, Windows, Mac)
     * all become \ns.
     */
    private static function html_to_text_fix_newlines(string $text): string
    {
        // replace \r\n to \n
        $text = str_replace("\r\n", "\n", $text);
        // remove \rs
        $text = str_replace("\r", "\n", $text);
        return $text;
    }

    /**
     * Remove leading or trailing spaces and excess empty lines from provided multiline text.
     */
    private static function html_to_text_process_whitespace_newlines(string $text): string
    {
        // remove excess spaces around tabs
        $text = preg_replace("/ *\t */m", "\t", $text);
        // remove leading whitespace
        $text = ltrim($text);
        // remove leading spaces on each line
        $text = preg_replace("/\n[ \t]*/m", "\n", $text);
        // convert non-breaking spaces to regular spaces to prevent output issues,
        // do it here so they do NOT get removed with other leading spaces, as they
        // are sometimes used for indentation
        $text = static::html_to_text_fix_newlines($text);
        // remove trailing whitespace
        $text = rtrim($text);
        // remove trailing spaces on each line
        $text = preg_replace("/[ \t]*\n/m", "\n", $text);
        // unarmor pre blocks
        $text = static::html_to_text_fix_newlines($text);
        // remove unnecessary empty lines
        $text = preg_replace("/\n\n+/m", "\n\n", $text);
        // merge blank spaces
        $text = preg_replace("/[ \t]{2,}/", ' ', $text);
        return $text;
    }
}
