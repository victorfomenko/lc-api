<?php
    $dbIP = '127.0.0.1';
    $dbUser = 'root';
    $dbPass = 'TwofRag83';
    $dbName = 'lovecanvas';
    $connection = mysqli_connect ( $dbIP ,$dbUser, $dbPass, $dbName ) or DIE( "ERROR: " . mysqli_error($connection) );
    mysqli_set_charset($connection,"utf8");
    $_SERVER['connection'] = $connection;

    function getDataFromDB ( $q ) {
        $result = mysqli_query($_SERVER['connection'], $q);
        $dataReader = dbArray($result);
        return $dataReader;
    }
    function insertDataToDB ( $q ) {
        $result = mysqli_query($_SERVER['connection'], $q);
        return $result;
    }
    function dbArray( $recordset ) {
            if ( ! $recordset ) { return false; }

            $res = array();
            $i = 0;
            while ( $row = mysqli_fetch_assoc( $recordset ) )
            {
                    $res[ $i ] = $row;
                    $i++;
            }
            return $res;
    }
?>