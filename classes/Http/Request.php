<?php
namespace Soundcloud\Http;

class Request implements HttpWriteInterface
{
  // User agent
  const USER_AGENT = 'dengsn-soundcloud/1.1.0';
  
  // Variables
  private $verb;
  private $url;
  private $headers = [];
  private $body = null;

  // Constructpor
  public function __construct($verb, Url $url)
  {
    $this->verb = strtoupper($verb);
    $this->url = $url;
  }
  
  // Gets the verb
  public function getVerb()
  {
    return $this->verb;
  }
  
  // Gets the url
  public function getUrl()
  {
    return $this->url;
  }
  
  // Gets and sets headers
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
  public function withHeaders(array $headers)
  {
    $this->headers = array_merge($this->headers,$headers);
    return $this;
  }
  public function withHeader($name, $value)
  {
    $this->headers[$name] = $value;
    return $this;
  }
  
  // Gets and sets body
  public function getBody()
  {
    if ($this->hasHeader('Content-Type'))
    {
      list($content,$properties) = Request::parseHeader($this->getHeader('Content-Type'));
      
      if ($content === 'application/json')
        return json_encode($this->body);
      elseif ($content === 'application/x-www-form-urlencoded')
        return http_build_query($this->body);
    }

    // No match
    return $this->body;
  }
  public function getRawBody()
  {
    return $this->body;
  }
  public function withBody($body)
  {
    $this->body = $body;
    return $this;
  }
  
  // Execute the request
  public function request($followRedirects = true)
  {
    $curl = curl_init();
    
    // Set request variables
    curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$this->verb);
    curl_setopt($curl,CURLOPT_URL,$this->url->build());
    curl_setopt($curl,CURLOPT_HEADER,true);
    curl_setopt($curl,CURLOPT_HTTPHEADER,self::implodeHeaders($this->headers));
    if ($this->getVerb() != 'GET' && !empty($this->body))
      curl_setopt($curl,CURLOPT_POSTFIELDS,$this->body);
    
    // Set other options
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($curl,CURLOPT_USERAGENT,self::USER_AGENT);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    if ($followRedirects)
      curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
    
    // Execute the request
    $response = curl_exec($curl);
    $info = curl_getinfo($curl);
    $errno = curl_errno($curl);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    // Check for errors
    if ($errno != 0)
      throw new RequestException($error);
    
    // Return a response object
    return new Response(
      $info['http_code'],
      self::explodeHeaders(substr($response,0,$info['header_size']),true),
      substr($response,$info['header_size'])
    );
  }
  
  // Implode headers
  private static function implodeHeaders($headers, $toString = false)
  {
    $imploded = [];
    foreach ($headers as $name => $value)
      $imploded[] = "$name: $value";
    
    if ($toString)
      return implode ("\n",$imploded);
    else
      return $imploded;
  }
  
  // Explode headers
  private static function explodeHeaders($headers, $fromString = false)
  {
    if ($fromString && is_string($headers))
      $headers = explode("\n",$headers);
    
    $exploded = [];
    foreach ($headers as $header)
    {
      if (!preg_match('/\:\s/',$header))
        continue;

      list($name,$value) = explode(': ',$header,2);
      $exploded[$name] = $value;
    }
    return $exploded;
  }
  
  // Parse a semicolon-separated header
  public static function parseHeader($value)
  {
    $values = explode('; ',$value);
    $main = array_shift($values);
    foreach ($values as $v)
    {
      list($key,$value) = explode('=',$v,2);
      $properties[$key] = $value;
    }
    return [$main,$properties];
  }
}