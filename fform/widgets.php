<?php
/**
 * FForm widgets
 *
 * @package   fform
 * @author    Jose Sibande <jbsibande@gmail.com>
 * @license   (c) 2010 JB Sibande GNU GPL 3.  
 */

/**
 * Transforms html input options into a string
 *
 * @param   array   options
 * @return  string  options as string 
 */
function html_params($args=array())
{
  $params = '';
  foreach ($args as $key=>$value)
  {
    $params .= ' '.$key.'="'.htmlspecialchars($value).'"';
  }
  return trim($params);
}

/**
 *
 */
class FForm_Widgets
{
  /**
   * Widgets
   *
   * @param   object  FForm instance
   * @return  void
   */
  public function __construct($that)
  {
    $this->that = $that;
  }

  /**
   * Input widget
   *
   * @param   string  input name
   * @param   array   html input options
   * @return  string  html input element
   */
  public function input($name, $args)
  {
    $value = array_key_exists($name, $this->that->data) ? $this->that->data[$name]['value'] : '';
    $defaults = array('type'=>'text', 'value'=>$value, 'name'=>$name, 'id'=>$name);
    
    foreach ($defaults as $k=>$v)
    {
      if ( ! isset($args[$k]))
      {
	$args[$k] = $v;
      }
    }

    return '<input '.html_params($args).' />';
  }

}