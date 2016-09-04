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
  private $clientSecret = null;
  private $redirectUri = null;
  private $accessToken = null;
  
  // Constructor
  public function __construct($clientId)
  {
    $this->clientId = $clientId;
  }
  
  // Sets the client id
  public function withClientId($clientId)
  {
    $this->clientId = $clientId;
    return $this;
  }
  
  // Sets the client secret
  public function withClientSecret($clientSecret)
  {
    $this->clientSecret = $clientSecret;
    return $this;
  }

  // Sets the redirect URI
  public function withRedirectUri($redirectUri)
  {
    $this->redirectUri = $redirectUri;
    return $this;
  }
  
  // Sets the access token based on a /connect code
  public function withAuthCode($code)
  {
    try
    {
     if ($this->clientSecret == null)
        throw new \InvalidArgumentException('You must set client_secret first');
      if ($this->redirectUri == null)
        throw new \InvalidArgumentException('You must set redirect_uri first');
      
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
  public function withAuthCredentials($username, $password)
  {
    try
    {
      if ($this->clientSecret == null)
        throw new \InvalidArgumentException('You must set client_secret first');
      if ($this->redirectUri == null)
        throw new \InvalidArgumentException('You must set redirect_uri first');
      
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
    if ($this->redirectUri != null)
      $url->withParam('redirect_uri',$this->redirectUri);
    if ($this->accessToken != null)
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
    if ($response->getStatus() == 401)
      throw new SoundcloudException("You must authorize your application first",$response);
    elseif (!preg_match('/[2-3][0-9]{2}/', $response->getStatus()))
      throw new SoundcloudException("The request returned with HTTP status code " . $response->getStatus(),$response);
    else
      return $response;
  }
}
