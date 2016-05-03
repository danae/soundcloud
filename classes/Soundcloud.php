<?php
namespace Soundcloud;

use Soundcloud\Http\Request;
use Soundcloud\Http\RequestException;
use Soundcloud\Http\Response;
use Soundcloud\Http\Url;

class Soundcloud
{
  // SoundCloud API location
  const URL = 'https://api.soundcloud.com';
  
  // Variables
  private $clientId;
  private $redirectUri;
  private $oauthToken = null;
  
  // Constructor
  public function __construct($clientId, $redirectUri = '')
  {
    $this->clientId = $clientId;
    $this->redirectUri = $redirectUri;
  }
  
  // Create a request object
  private function createRequest($verb, $path, array $params = [])
  {
    // Create url
    $url = new Url(self::URL . $path);
    $url->withParam('client_id',$this->clientId);
    if (!empty($this->redirectUri))
      $url->withParam('redirect_uri',$this->redirectUri);
    if (!empty($params))
    $url->withParams($params);
    
    // Create request
    $request = new Request($verb,$url);
    $request->withHeader('Accept','application/json');
    if ($this->oauthToken !== null)
      $request->withHeader('Authorization','OAuth ' . $this->oauthToken);
    if (!empty($body))
      $request->withBody($body);
    
    // Return request
    return $request;
  }
  
  // Check the response for a vaild status
  private function checkResponse(Response $response)
  {
    if (preg_match('/[2-3][0-9]{2}/', $response->getStatus()))
      return $response->getBody();
    else
      throw new SoundcloudException("The request returned with HTTP status code " . $response->getStatus());
  }
  
  // Sends a GET request
  public function get($path, array $params = [])
  {
    try
    {
      $response = $this->createRequest('GET',$path,$params)
        ->request();
      return $this->checkResponse($response);
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }

  // Sends a POST request
  public function post($path, array $body = [])
  {
    try
    {
      $response = $this->createRequest('POST',$path)
        ->withBody($body)
        ->request();
      return $this->checkResponse($response);
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }

  // Sends a PUT request
  public function put($path, array $body = [])
  {
    try
    {
      $response = $this->createRequest('PUT',$path)
        ->withBody($body)
        ->request();
      return $this->checkResponse($response);
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }
  
  // Sends a DELETE request
  public function delete($path)
  {
    try
    {
      $response = $this->createRequest('DELETE',$path)
        ->request();
      return $this->checkResponse($response);
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }
  
  // Sends a resolve request
  public function resolve($url)
  {
    return $this->get('/resolve',['url' => $url]);
  }
  
  // Sends a oembed request
  public function oembed($url, array $params = [])
  {
    return $this->get('/oembed',array_merge($params,['url' => $url]));
  }
}
