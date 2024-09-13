<?php

class DAOBugNote
{
    static function get_record( $bugnote_id ) 
    {
        try
        {
            $query = new \DbQuery();
            $table_bug_note = plugin_table('bug_note');
            $table_service = plugin_table('service');
            $table_contact = plugin_table('contact');

            $sql = "SELECT 
                BN.bugnote_id,
                BN.service_id,
                BN.contact_id,
                BN.is_billable,
                BN.points_per_hour,
                BN.time_spent,
                S.service_name,
                CONCAT(c.first_name, ' ', c.second_name) AS contact_name,
                C.email AS contact_email,
                C.phone AS contact_phone
            FROM {$table_bug_note} BN 
            LEFT JOIN {$table_service} S ON S.ID = BN.service_id
            LEFT JOIN {$table_contact} C ON C.ID = BN.contact_id
            WHERE bugnote_id = $bugnote_id";

            $query->sql( $sql );
            return $query->execute();
        }
        catch (Exception $e) 
        {
            trigger_error( $e->getMessage(), ERROR );
        }
    }

    public static function update_record(
        $p_bugnote_id, 
        $p_contact_id,
        $p_service_id, 
        $p_is_billable,
        $p_points_per_hour,
        $p_time_spent
	) 
	{
		try
		{
			$query = new \DbQuery();
			$table_bug_note = plugin_table('bug_note');

			$p_is_billable_str = $p_is_billable ? 'true' : 'false';

            if (SCrmSchema::idExistsIn('bug_note', 'bugnote_id', $p_bugnote_id))
            {
                $sql = "UPDATE {$table_bug_note} SET
                    contact_id = $p_contact_id,
                    service_id = $p_service_id, 
                    is_billable = $p_is_billable_str,
                    points_per_hour = $p_points_per_hour,
                    time_spent = $p_time_spent
                WHERE bugnote_id = {$p_bugnote_id}";
            }
            else
            {
                $sql = "INSERT INTO {$table_bug_note} (
                    bugnote_id, 
                    contact_id,
                    service_id,
                    is_billable,
                    points_per_hour,
                    time_spent
                ) 
                VALUES (
                    $p_bugnote_id, 
                    $p_contact_id,
                    $p_service_id, 
                    $p_is_billable_str,
                    $p_points_per_hour,
                    $p_time_spent
                )";
            }

			$query->sql( $sql  );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function delete_record($bugnote_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_bug_note = plugin_table('bug_note');
			$sql = "DELETE FROM {$table_bug_note} WHERE bugnote_id = {$bugnote_id}";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

    public static function duration_to_minutes( $p_hhmm ) 
    {
        if( is_blank( $p_hhmm ) ) {
            return 0;
        }
    
        $a = explode( ':', $p_hhmm );
        $min = 0;
        $p_field = "time_spent";

        # time can be composed of max 2 parts (hh:mm)
        if( count( $a ) > 2 ) 
        {
            trigger_error( sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ), ERROR );
        }
    
        $count = count( $a );
        for( $i = 0;$i < $count;$i++ ) {
            # all time parts should be integers and non-negative.
            if( !is_numeric( $a[$i] ) || ( (integer)$a[$i] < 0 ) ) 
            {
                trigger_error( sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ), ERROR );
            }
    
            # minutes and seconds are not allowed to exceed 59.
            if( ( $i > 0 ) && ( $a[$i] > 59 ) ) 
            {
                trigger_error( sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ), ERROR );
            }
        }
    
        switch( $count ) {
            case 1:
                $min = (integer)$a[0];
                break;
            case 2:
                $min = (integer)$a[0] * 60 + (integer)$a[1];
                break;
        }
    
        return (int)$min;
    }

    public static function minutes_to_duration( $p_min = 0 ) 
    {
        return sprintf( '%03d:%02d', $p_min / 60, $p_min % 60 );
    }    

	public static function get_total_points($bugnote_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_plugin_bug_note = plugin_table('bug_note');
            $table_bugnote = db_get_table('bugnote');
			$sql = "SELECT 
                count(1) AS cnt,
                SUM(time_spent) AS time_spent,
                SUM(
                    ROUND(time_spent/60.0*points_per_hour,2)
                ) AS total_points
                FROM {$table_bugnote} MBN
                INNER JOIN {$table_plugin_bug_note} BNT ON BNT.bugnote_id = MBN.id
                WHERE MBN.bug_id = {$bugnote_id};
            ";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}

	public static function get_total_billable_points($bugnote_id) 
	{
		try
		{
			$query = new \DbQuery();
			$table_plugin_bug_note = plugin_table('bug_note');
            $table_bugnote = db_get_table('bugnote');
			$sql = "SELECT 
                count(1) AS cnt,
                SUM(time_spent) AS time_spent,
                SUM(
                    ROUND(time_spent/60.0*points_per_hour,2)
                ) AS total_points
                FROM {$table_bugnote} MBN
                INNER JOIN {$table_plugin_bug_note} BNT ON BNT.bugnote_id = MBN.id
                WHERE MBN.bug_id = {$bugnote_id} AND BNT.is_billable";

			$query->sql( $sql );
			return $query->execute();
		}
		catch (Exception $e) 
		{
			trigger_error( $e->getMessage(), ERROR );
		}
	}
}
