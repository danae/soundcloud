<?php
namespace Soundcloud\Http;

class Response implements HttpInterface
{
  // Variables
  private $headers;
  private $body;
  private $status;
  private $request;
   
  // Constructor
  public function __construct($status, $headers, $body, Request $request = null)
  {
    $this->status = $status;
    $this->headers = $headers;
    $this->body = $body;
    $this->request = $request;
  }
  
  // Gets the header
  public function getStatus()
  {
    return $this->status;
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
  
  // Gets the request
  public function getRequest()
  {
    return $this->request;
  }
}
