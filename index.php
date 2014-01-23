<?php
/*
Plugin Name:  Editor PrevNext Navigation
Description:  Open the next or the previous post in the editor without having to go back to the overview.
Author:       Chris Doerr
Version:      1.0.0
Author URI:   http://www.meomundo.com/
*/
global $wpdb;

if( !class_exists( 'EditorPrevNextNavigation' ) ) {
  
  include_once 'EditorPrevNextNavigation.php';
  
}

$EditorPrevNextNavigation = new EditorPrevNextNavigation( $wpdb );

?>