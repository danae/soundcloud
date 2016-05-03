<?php
namespace Soundcloud\Http;

class Response implements HttpInterface
{
  // Variables
  private $headers;
  private $body;
  private $status;
   
  // Constructor
  public function __construct($status, $headers, $body)
  {
    $this->status = $status;
    $this->headers = $headers;
    $this->body = $body;
  }
  
  // Gets headers
  public function getHeaders()
  {
    return $this->headers;
  }
  public function getHeader($name)
  {
    if (array_key_exists($name,$this->headers))
      return $this->headers[$name];
    else
      return null;
  }
  public function hasHeader($name)
  {
    return !empty($this->getHeader($name));
  }
  
  // Gets body
  public function getBody()
  {
    if ($this->hasHeader('Content-Type'))
    {
      list($content,$properties) = Request::parseHeader($this->getHeader('Content-Type'));
      
      if ($content === 'application/json')
        return json_decode($this->body);
    }

    // No match
    return $this->body;
  }
  public function getRawBody()
  {
    return $this->body;
  }
  
  // Gets the header
  public function getStatus()
  {
    return $this->status;
  }
}
