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
  const URL_AUTHORIZE = 'https://soundcloud.com/connect';
  
  // Variables
  private $clientId;
  private $clientSecret;
  private $redirectUri;
  private $accessToken;
  
  // Constructor
  public function __construct($clientId, $clientSecret = null, $redirectUri = null, $accessToken = null)
  {
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
    $this->redirectUri = $redirectUri;
    $this->accessToken = $accessToken;
  }
  
  // Sets the access token based on a /connect code
  public function withAuthorizationCode($code)
  {
    try
    {
      $url = new Url(self::URL . '/oauth2/token');
      
      $response = $this->createRequest('POST',$url)
        ->withBodyParam('client_id',$this->clientId)
        ->withBodyParam('client_secret',$this->clientSecret)
        ->withBodyParam('redirect_uri',$this->redirectUri)
        ->withBodyParam('grant_type','authorization_code')
        ->withBodyParam('code',$code)
        ->request();
      
      $this->accessToken = $this->validate($response)->getBody()->access_token;
      return $this;
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }
  
  // Sets the access token based on user credentials
  public function withAuthorizationCredentials($username, $password)
  {
    try
    {
      $url = new Url(self::URL . '/oauth2/token');
      
      $response = $this->createRequest('POST',$url)
        ->withBodyParam('client_id',$this->clientId)
        ->withBodyParam('client_secret',$this->clientSecret)
        ->withBodyParam('redirect_uri',$this->redirectUri)
        ->withBodyParam('grant_type','password')
        ->withBodyParam('username',$username)
        ->withBodyParam('password',$password)
        ->request();
      
      $this->accessToken = $this->validate($response)->getBody()->access_token;
      return $this;
    }
    catch (RequestException $ex)
    {
      throw new SoundcloudException($ex->getMessage);
    }
  }
  
  // Sends a GET request
  public function get($path, array $params = [])
  {
    try
    {
      $url = $this->createUrl($path,$params);      
      
      $response = $this->createRequest('GET',$url)
        ->request();
      
      var_dump($response);
      
      return $this->validate($response)->getBody();
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
      $url = $this->createUrl($path);
      
      $response = $this->createRequest('POST',$url)
        ->withBody($body)
        ->request();
      
      return $this->validate($response)->getBody();
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
      $url = $this->createUrl($path);
      
      $response = $this->createRequest('PUT',$url)
        ->withBody($body)
        ->request();
      
      return $this->validate($response)->getBody();
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
      $url = $this->createUrl($path);
      
      $response = $this->createRequest('DELETE',$url)
        ->request();
      
      return $this->validate($response)->getBody();
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
  
  // Gets the authorization url
  public function authorizeUrl()
  {
    return (new Url(self::URL_AUTHORIZE))
      ->withParam('client_id',$this->clientId)
      ->withParam('redirect_uri',$this->redirectUri)
      ->withParam('response_type','code')
      ->withParam('scope','non-expiring')
      ->withParam('display','popup')
      ->build();
  }
  
  // Creates an url
  private function createUrl($path, array $params = [])
  {
    $url = new Url(self::URL . $path);
    $url->withParam('client_id',$this->clientId);
    if (!empty($this->redirectUri))
      $url->withParam('redirect_uri',$this->redirectUri);
    if (!empty($this->accessToken))
      $url->withParam('oauth_token',$this->accessToken);
    if (!empty($params))
      $url->withParams($params);
    return $url;
  }
  
  // Creates a request
  private function createRequest($verb, Url $url)
  {
    $request = new Request($verb,$url);
    $request->withHeader('Accept','application/json');
    if (!empty($this->accessToken))
      $request->withHeader('Authorization','OAuth ' . $this->accessToken);
    return $request;
  }
  
  // Check the response for a vaild status
  private function validate(Response $response)
  {
    if (!preg_match('/[2-3][0-9]{2}/', $response->getStatus()))
      throw new SoundcloudException("The request returned with HTTP status code " . $response->getStatus(),$response);
    else
      return $response;
  }
}
