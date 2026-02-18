<?php
$server = 'localhost:3306';
$uname = 'root';
$password = '';
$db = 'user';

$conn = mysqli_connect( $server, $uname, $password, $db );

if ( !$conn ) {
    die( 'Connection Error: ' . mysqli_connect_error() );
}
