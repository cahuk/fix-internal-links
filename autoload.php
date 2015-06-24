<?php
/**
 * Startup classes
 */

function fix_internal_links_autoload( $className )
{


  $filePath = dirname(__FILE__) . '/class/' . $className . '.php';

  if ( file_exists($filePath) ) {

    require_once($filePath);

  } else {

	throw new Exception("File $className is not exists");
  }
}

spl_autoload_register('fix_internal_links_autoload');
