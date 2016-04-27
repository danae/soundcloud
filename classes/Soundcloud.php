<?php
namespace Soundcloud;

class Soundcloud
{
  // Request variables
  private $clientId;
  private $redirectUri;
  
  // Response variables
  private $responseHeaders;
  private $responseBody;
  private $responseStatus;
  
  // Constructor
  public function __construct($clientId, $redirectUri = null)
  {
    $this->clientId = $clientId;
    $this->redirectUri = $redirectUri;
  }
  
  // Sends a GET request
  public function get($path, array $params = [])
  {
    $url = $this->createUrl($path,$params);
    return $this->request($url);
  }

  // Sends a POST request
  public function post($path, array $data = [])
  {
    $url = $this->createUrl($path);
    return $this->request($url,[
      CURLOPT_POST => true, 
      CURLOPT_POSTFIELDS => $data
    ]);
  }

  // Sends a PUT request
  public function put($path, $data)
  {
    $url = $this->createUrl($path);
    return $this->request($url,[
      CURLOPT_CUSTOMREQUEST => 'PUT', 
      CURLOPT_POSTFIELDS => $data
    ]);
  }
  
  // Sends a DELETE request
  public function delete($path)
  {
    $url = $this->createUrl($path);
    return $this->request($url,[
      CURLOPT_CUSTOMREQUEST => 'DELETE'
    ]);
  }
  
  // Sends a resolve request
  public function resolve($url)
  {
    return $this->get('/resolve',[
      'url' => $url
    ]);
  }
  
  // Sends a oembed request
  public function oembed($url, array $params = [])
  {
    return $this->get('/oembed',array_merge($params,[
      'url' => $url
    ]));
  }
  
  // Sends a request using cUrl
  protected function request($url, array $options = [])
  {
    // Perform a cUrl request
    $curl = curl_init($url);
    curl_setopt($curl,CURLOPT_HEADER,true);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_USERAGENT,'Soundcloud-php');
    curl_setopt_array($curl,$options);
    
    $data = curl_exec($curl);
    $info = curl_getinfo($curl);
    
    curl_close($curl);

    // Set the response fields
    $this->responseStatus = $info['http_code'];
    $this->responseHeaders = self::headers(substr($data,0,$info['header_size']));
    $this->responseBody = substr($data,$info['header_size']);
    
    // Return the body or throw if not succesful
    if (preg_match('/20[0-9]/',$this->responseStatus))
      return json_decode($this->responseBody,true);
    else
      throw new SoundcloudException($this->responseStatus);
  }
  
  // Returns the response headers
  public function getHeaders()
  {
    return $this->responseHeaders;
  }
  
  // Returns the response body
  public function getBody()
  {
    return $this->responseBody;
  }
  
  // Returns the response status
  public function getStatus()
  {
    return $this->responseStatus;
  }
  
  // Creates an formatted URL
  private function createUrl($path, array $params = [])
  {
    $url = "http://api.soundcloud.com" . $path;
    
    $allParams = $params;
    $allParams['client_id'] = $this->clientId;
    if ($this->redirectUri !== null)
      $allParams['redirect_uri'] = $this->redirectUri;

    $url .= '?' . http_build_query($allParams);
    return $url;
  }
  
  // Parses HTTP headers
  private static function headers($headers)
  {
    $unparsedHeaders = explode("\n",trim($headers));
    $parsedHeaders = [];

    foreach ($unparsedHeaders as $header)
    {
      if (!preg_match('/\:\s/',$header))
        continue;

      list($key,$value) = explode(': ',$header,2);
      $parsedHeaders[$key] = trim($value);
    }

    return $parsedHeaders;
  }
}
