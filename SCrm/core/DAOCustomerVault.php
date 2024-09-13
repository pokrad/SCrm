<?php

include_once("config/config_inc.php");

class DAOCustomerVault
{
	const ENCRYPT_METHOD = 'aes-256-ctr';

	/*
	DAO Operations for normatives
	*/
	public static function get_list($p_customer_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_customer_vault = plugin_table('customer_vault');
			$table_customer = plugin_table('customer');

			$sql = "SELECT 
				CS.id,
				CS.customer_id,
				CU.customer_name,
				CS.item_name,
				CS.created_at,
				CS.modified_at
			FROM {$table_customer_vault} CS
			INNER JOIN {$table_customer} CU on CU.id = CS.Customer_id
			WHERE CS.customer_id = {$p_customer_id} ORDER BY item_name ASC";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_record($p_customer_vault_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_customer_vault = plugin_table('customer_vault');
			$table_customer = plugin_table('customer');

			$sql = "SELECT 
				CS.id,
				CS.customer_id,
				CU.customer_name,
				CS.item_name,
				CS.item_value,
				CS.created_at,
				CS.modified_at
			FROM {$table_customer_vault} CS
			INNER JOIN {$table_customer} CU on CU.id = CS.Customer_id
			WHERE CS.id = {$p_customer_vault_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function update_record(
		$p_customer_vault_id,
		$p_item_name,
		$p_item_value,
	) 
	{
		try
		{

			$query = new \DbQuery();
			$table_customer_vault = plugin_table('customer_vault');
			$item_name_str = str_replace("'","''",$p_item_name);
			$item_value_encripted = DAOCustomerVault::encrypt($p_item_value,true);
			$item_value_str = str_replace("'","''",$item_value_encripted);
			$current_timestamp = db_now();

			$sql = "UPDATE {$table_customer_vault} SET
				item_name= '{$item_name_str}',
				item_value = '{$item_value_str}',
				modified_at = {$current_timestamp}
			WHERE id = {$p_customer_vault_id}";

			$query->sql( $sql  );
			return $query->execute();
		} 
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function insert_record(
		$p_customer_id,
		$p_item_name,
		$p_item_value
	) 
	{
		try
		{
			if (!SCrmSchema::idExistsIn('customer','id',$p_customer_id))
			{
				trigger_error( "Invalid customer !", ERROR );
			}

			$query = new \DbQuery();
			$table_customer_vault = plugin_table('customer_vault');
			$item_name_str = str_replace("'","''",$p_item_name);
			$item_value_encripted = DAOCustomerVault::encrypt($p_item_value,true);
			$item_value_str = str_replace("'","''",$item_value_encripted);
			$current_timestamp = db_now();

			$sql = "INSERT INTO {$table_customer_vault} (
				customer_id,
				item_name,
				item_value,
				created_at,
				modified_at
			)
			VALUES (
				{$p_customer_id},
				'{$item_name_str}',
				'{$item_value_str}',
				{$current_timestamp},
				{$current_timestamp}
			)";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function delete_record($p_customer_vault_id) 
	{
		try
		{
			$existing_rec = DAOCustomerVault::get_record($p_customer_vault_id);
			$existing = db_fetch_array( $existing_rec );
			$val = DAOCustomerVault::decrypt($existing['item_value'], true);
			if ($val <> '')
			{
				trigger_error( "Vault item contains data ! Empty the item data, save, and then You can delete it.", ERROR );
			}


			$query = new \DbQuery();
			$table_customer_vault = plugin_table('customer_vault');
			$sql = "DELETE FROM {$table_customer_vault} WHERE id = {$p_customer_vault_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
    
    public static function encrypt($message, $encode = false)
    {
		$key = config_get("crypto_master_salt");	
        $nonceSize = openssl_cipher_iv_length(self::ENCRYPT_METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);
        
        $ciphertext = openssl_encrypt(
            $message,
            self::ENCRYPT_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        if ($encode) {
	        //Concat $nonce.$ciphertext and return encoded string
			return base64_encode($nonce.$ciphertext);
        }
		//Concat $nonce.$ciphertext
        return $nonce.$ciphertext;
    }
    
    public static function decrypt($message, $encoded = false)
    {
		$key = config_get("crypto_master_salt");	
		if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::ENCRYPT_METHOD);
        $nonce = mb_substr($message, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($message, $nonceSize, null, '8bit');
        
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ENCRYPT_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
        
        return $plaintext;
    }
}