<?php
/**
 * Forms library
 *
 * @package   fuuze.form
 * @author    Jose Sibande
 * @license   (c) 2010 JB Sibande GNU GPL 3.  
 */

class Lib_Form
{
  public function __construct($schema)
  {
    $this->schema = $schema;
    $this->data = $this->filter_data($schema);
  }
  
  /**
   * Filters forged fields from $_POST
   *
   * @param   array  form shema
   * @param   array  default data
   * @return  array  default data + array(expected field => key)
   */
  public static function filter_data($schema, $valid_data = array())
  {
    foreach ($schema as $field=>$rules)
    {
      $valid_data[$field]['value'] = array_key_exists($field, $_POST) ? $_POST[$field]: '';
	  $valid_data[$field]['errors'] = array();
    }
    return $valid_data;
  }
  
  /**
   * Validate form
   * TODO: find PHP equivalent of Python's **kwargs
   *
   * @param   array  form schema
   * @return  array  validated data
   */
  public function validate()
  {
    $is_valid = TRUE;
    foreach ($this->schema as $field=>$rules)
    {
      foreach ($rules as $validator => $args)
      {
	$arg_values = array_values($args);
	array_unshift($arg_values, $field);

	$valid = call_user_func_array(array($this, $validator), $arg_values);
	$is_valid = $valid ? $is_valid : $valid;
      }
    }
    return $is_valid;
  }
  

  /**
   * Checks key using custom callback function
   *
   * @param   string    the key name
   * @param   callback  callback function
   * @param   array     callback arguments
   * @param   array     error messages
   * @return  bool
   */
  public function custom($key, $callback, $args=array())
  {
    $args['that'] = $this;
    if ( ! $callback($key, $args)){
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Checks if key matches regex
   *
   * @param   string  the key name
   * @param   string  regular expression pattern
   * @param   array   error messages
   * @param   bool    pass empty keys
   * @return  bool
   */
  public function regex($key, $pattern, $error,  $allow_empty=False)
  {
    if ( ! preg_match($pattern, $this->data[$key]['value']))
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Checks required field
   *
   * @param   string  the key
   * @param   array   error messages
   * @return  bool
   */
  
  public function required($key, $error)
  {
    if ( ! (bool) $this->data[$key]['value'])
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Checks if value is a valid e-mail address
   *
   * @param   string  the key name
   * @param   array   error messages
   * @param   bool    pass empty keys
   * @return  bool
   */
  public function email($key, $error, $allow_empty=False)
  {
    // pattern stolen from kohana
    $pattern = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:'.
      '(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';
    if ( ! preg_match($pattern, $this->data[$key]['value']))
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Checks if value is in array
   *
   * @param   string  the key name
   * @param   array   the range that the key should be checked against
   * @param   array   error messages
   * @param   bool    pass empty keys
   * @return  bool
   */
  public function in($key, $range, $error, $allow_empty=False)
  {
    if ( ! in_array($this->data[$key]['value'], $range))
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }
  
  /**
   * Checks if the value is numerical
   *
   * @param   string  the key name
   * @param   array   array(min_key, max_key)
   * @param   array   error messages
   * @param   bool    pass empty keys
   * @return  bool
   */
  public function number($key, $range, $error, $allow_empty)
  {
    if ( ! is_numeric($this->data[$key]['value']))
    {
      $this->data[$key]['errors'][] = $error[0];
      return FALSE;
    }
    return TRUE;
  }

  
  /**
   * Checks length
   *
   * @param   string  the key name
   * @param   array   array($min_length, $max_length)
   * @param   array   error messages
   * @param   bool    pass empty keys
   * @return  bool
   */
  public function length($key, $range, $error, $allow_empty=False)
  {
    if (isset($range['min']))
    {
      if (strlen($this->data[$key]['value']) < $range['min'])
      {
	$this->data[$key]['errors'][] = $error['min'];
	return FALSE;
      }
    }
    if (isset($range['max']))
    {
      if (strlen($this->data[$key]['value']) > $range['max'])
      {
	$this->data[$key]['errors'][] = $error['max'];
	return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Compares two keys
   *
   * @param   string  the key name
   * @param   string  other key name
   * @return  bool
   */
  public function compare($key, $other_key, $error)
  {
    if ($this->data[$key]['value'] != $this->data[$other_key]['value'])
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }

}