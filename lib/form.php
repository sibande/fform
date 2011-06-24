<?php
/**
 * Forms utilities
 *
 * @package   fuuze
 * @author    Jose Sibande
 * @license   (c) 2010 JB Sibande GNU GPL 3.  
 */

class Lib_Form
{
  /**
   * Filters forged fields from $_POST
   *
   * @param   array  expected fields
   * @param   array  default data
   * @return  array  default data + array(expected field => value)
   */
  public static function filter_data($expected_data, $valid_data = array())
  {
    foreach ($expected_data as $field)
    {
      $valid_data[$field] = array_key_exists($field, $_POST) ? $_POST[$field]: '';
    }
    
    return $valid_data;
  }

}