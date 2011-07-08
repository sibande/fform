<?php
/**
 * Forms library
 *
 * @package   fform
 * @author    Jose Sibande <jbsibande@gmail.com>
 * @license   (c) 2010 JB Sibande GNU GPL 3.  
 */

class FForm_Form
{
  public function __construct($schema)
  {
    $this->schema = $schema;
    $this->data = $this->filter_data($schema);
    $this->widget = new FForm_Widgets($this);
  }
  
  /**
   * Filters forged fields from $_POST
   *
   * @param   array  form shema
   * @param   array  default data
   * @return  array  default data + array(expected field => key)
   */
  public function filter_data($schema, $valid_data = array())
  {
    foreach ($schema as $field=>$rules)
    {
      $valid_data[$field]['value'] = array_key_exists($field, $_POST) ?
	$_POST[$field]: (array_key_exists($field, $_FILES) ? $_FILES[$field]: '');
      $valid_data[$field]['errors'] = array();
    }
    return $valid_data;
  }
  
  /**
   * Validate form
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
  public function regex($key, $pattern, $error=NULL,  $allow_empty=FALSE)
  {
    if ( ! $error)
    {
      $error = 'Invalid input.';
    }
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
  
  public function required($key, $error=NULL)
  {
    if ( ! $error)
    {
      $error = 'Required.';
    }
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
  public function email($key, $error=NULL, $allow_empty=FALSE)
  {
    if ( ! $error)
    {
      $error = 'Invalid E-Mail address.';
    }
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
  public function in($key, $range, $error=NULL, $allow_empty=FALSE)
  {
    if ( ! $error)
    {
      $error = 'Value not in range.';
    }
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
  public function number($key, $range, $error=NULL, $allow_empty=NULL)
  {
    if ( ! (bool) $error)
    {
      $error = 'Invalid number.';
    }
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
  public function length($key, $range, $error=NULL, $allow_empty=FALSE)
  {
    if ( ! (bool) $error)
    {
      $error = 'Field length must be between '.$range['min'].' and '.$range['max'].'.';
    }
    if (isset($range['min']))
    {
      if (strlen($this->data[$key]['value']) < $range['min'])
      {
	$this->data[$key]['errors'][] = $error;
	return FALSE;
      }
    }
    if (isset($range['max']))
    {
      if (strlen($this->data[$key]['value']) > $range['max'])
      {
	$this->data[$key]['errors'][] = $error;
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
  public function compare($key, $other_key, $error=NULL)
  {
    if ( ! (bool) $error)
    {
      $error = 'Field not matching '.$other_key.'.';
    }
    if ($this->data[$key]['value'] != $this->data[$other_key]['value'])
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Validates file input
   *
   * @param   string  key name
   * @param   array   file types
   * @return  bool
   */
  public function file($key, $file_types, $error=NULL)
  {
    if ( ! (bool) $error)
    {
      $error = 'Unsupported file.';
    }

    if ( ! in_array('type', $this->data[$key]['value']))
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    elseif ( ! in_array($this->data[$key]['value']['type'], array_values($file_types)))
    {
      $this->data[$key]['errors'][] = $error;
      return FALSE;
    }
    else
    {
      return TRUE;
    }
  }

}
