<?php
namespace Soundcloud\Http;

class Url
{
  // Variables
  private $url;
  private $params = [];
  
  // Constructor
  public function __construct($url)
  {
    $this->url = $url;
  }
  
  // Gets the url
  public function getUrl()
  {
    return $this->url;
  }
  
  // Gets and sets the params
  public function getParams()
  {
    return $this->params;
  }
  public function getParam($name)
  {
    if (array_key_exists($name,$this->params))
      return $this->params[$name];
    else
      return null;
  }
  public function withParams(array $params)
  {
    $this->params = array_merge($this->params,$params);
    return $this;
  }
  public function withParam($name, $value)
  {
    $this->params[$name] = $value;
    return $this;
  }
  
  // Build the url
  public function build()
  {
    return $this->url . '?' . http_build_query($this->params);
  }
}
