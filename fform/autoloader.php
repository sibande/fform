<?php

/*
 * FForm - PHP form library
 *
 * @package fform
 * @author    Jose Sibande <jbsibande@gmail.com>
 */

class FForm_Autoloader
{
    /**
     * Registers FForm
     *
     * @return  void
     */
    public static function register()
    {
      spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Autoloads FForm class
     *
     * @param   string   class name.
     * @return  boolean
     */
    public static function autoload($class)
    {
      if (strpos($class, 'FForm') != 0) {
	return;
      }
      $file_name = dirname(dirname(__FILE__)).'/'.str_replace('_', '/', strtolower($class)).'.php';
      if (file_exists($file_name))
      {
	require($file_name);
      }
    }
}
