<?php

session_start();

require_once __DIR__ . '/../config/database.php' ;
require_once __DIR__ . '/../models/Household.php' ;

if ( !isset( $_SESSION['user_id'] ) || ( $_SESSION['role'] ?? '' ) !== 'admin' ) {
  header( "Location: /BIS/views/login.php" ) ; exit() ;
}

$id = (int)( $_GET['id'] ?? 0 ) ;
if ( $id <= 0 ) {
    $_SESSION['error'] = "Invalid household id." ;
    header( "Location: /BIS/controller/households_manage.php" ) ; exit() ;
    }

$model = new Household( $conn ) ;

if( $model->dissolve( $id ) ) {
 $_SESSION['success'] = "Household dissolved successfully." ;
} else {
 $_SESSION['error'] = "Failed to dissolve household." ;
}

header( "Location: /BIS/controller/households_manage.php" ) ; exit() ;
exit() ;