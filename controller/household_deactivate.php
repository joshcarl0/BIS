<?php

session_start() ;

require_once __DIR__ . '/../config/database.php' ;
require_once __DIR__ . '/../models/Household.php' ;

if ( !isset( $_SESSION['user_id'] ) || ( $_SESSION['role'] ?? '' ) !== 'admin' ) {
  header( "Location: /BIS/views/login.php" ) ; exit() ;
}

$id = (int)( $_GET['id'] ?? 0 ) ;
if ( $id <= 0 ) {
  $_SESSION['error'] = "Invalid household id." ;
  header( "Location: /BIS/views/admin/households_manage.php" ) ; exit() ;
}

$model = new Household( $conn ) ;

if( $model->deactivate( $id ) ) {
 $_SESSION['success'] = "Household deactivated successfully." ;
} else {
 $_SESSION['error'] = "Failed to deactivate household." ;
}

header( "Location: /BIS/views/admin/households_manage.php" ) ; exit() ;
exit() ;
