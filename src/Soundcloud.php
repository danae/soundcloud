<?php
namespace Soundcloud;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use RuntimeException;

class Soundcloud
{
  // SoundCloud API location
  const URI = 'https://api.soundcloud.com/';
  const OEMBED_URI = 'https://soundcloud.com/oembed';
  const AUTHORIZE_URI = 'https://soundcloud.com/connect';

  // Variables
  private $clientId;
  private $clientSecret;
  private $redirectUri;
  private $accessToken;
  private $client;

  // Constructor
  public function __construct(string $clientId, string $clientSecret = null, string $redirectUri = null)
  {
    // initialize variables
    $this->clientId = $clientId;
    $this->clientSecret = $clientSecret;
    $this->redirectUri = $redirectUri;
    $this->accessToken = null;

    // Initilalize HTTp client
    $this->client = new Client([
      'base_uri' => self::URI,
      'verify' => false,
      'http_errors' => false
    ]);
  }

  // Getters and setters
  public function getClientId(): string
  {
    return $this->clientId;
  }
  public function setClientId($clientId): self
  {
    $this->clientId = $clientId;
    return $this;
  }
  public function getClientSecret()
  {
    return $this->clientSecret;
  }
  public function setClientSecret($clientSecret): self
  {
    $this->clientSecret = $clientSecret;
    return $this;
  }
  public function getRedirectUri()
  {
    return $this->redirectUri;
  }
  public function setRedirectUri($redirectUri): self
  {
    $this->redirectUri = $redirectUri;
    return $this;
  }

  // Get the access token
  public function getAccessToken()
  {
    return $this->accessToken;
  }

  // Set the access token based on a /connect code
  public function authorizeWithCode($code): self
  {
    // Check if all arguments are set
    if ($this->clientSecret == null)
      throw new InvalidArgumentException('No clientSecret was set');
    if ($this->redirectUri == null)
      throw new InvalidArgumentException('No redirectUri was set');

    // Send the request
    $json = $this->post('/oauth2/token', [
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'redirect_uri' => $this->redirectUri,
      'grant_type' => 'authorization_code',
      'code' => $code
    ]);

    // Set the access token
    $this->accessToken = $json->access_token;

    // Return self for chainability
    return $this;
  }

  // Sets the access token based on user credentials
  public function authorizeWithCredentials(string $username, string $password): self
  {
    // Check if all arguments are set
    if ($this->clientSecret == null)
      throw new InvalidArgumentException('No clientSecret was set');
    if ($this->redirectUri == null)
      throw new InvalidArgumentException('No redirectUri was set');

    // Send the request
    $json = $this->post('/oauth2/token', [
      'client_id' => $this->clientId,
      'client_secret' => $this->clientSecret,
      'redirect_uri' => $this->redirectUri,
      'grant_type' => 'password',
      'username' => $username,
      'password' => $password
    ]);

    // Set the access token
    $this->accessToken = $json->access_token;

    // Return self for chainability
    return $this;
  }

  // Send a request
  private function request(string $method, string $uri, array $query = [], array $jsonBody = [])
  {
    try
    {
      // Set the query
      $query['client_id'] = $this->clientId;
      if ($this->accessToken)
        $query['oauth_token'] = $this->accessToken;

      // Send the request
      if ($method == 'POST' || $method == 'PUT')
        $response = $this->client->request($method, $uri, ['query' => $query,'json' => $jsonBody]);
      else
        $response = $this->client->request($method, $uri, ['query' => $query]);

      // Check if authorization is needed
      if ($response->getStatusCode() == 401)
        throw new SoundcloudException("You must authorize your application first");

      // Check if the request succeeded
      if (!preg_match('/[2-3][0-9]{2}/', $response->getStatusCode()))
        throw new SoundcloudException("The request \"{$method} {$uri}" . (!empty($query) ? "?" . http_build_query($query) : "") . "\" returned with HTTP status code {$response->getStatusCode()}: {$response->getBody()}");

      // Otherwise handle the response
      if (strpos($response->getHeaderLine('Content-Type'),'application/json') === 0)
        return json_decode($response->getBody());
      else
        return $response->getBody();
    }
    catch (RequestException $ex)
    {
      throw new RuntimeException($ex->getMessage(), $ex->getCode(), $ex);
    }
  }

  // Sends a GET request
  public function get(string $uri, array $query = [])
  {
    return $this->request('GET', $uri, $query);
  }

  // Sends a POST request
  public function post(string $uri, array $body = [], array $query = [])
  {
    return $this->request('POST', $uri, $query, $body);
  }

  // Sends a PUT request
  public function put(string $uri, array $body = [], array $query = [])
  {
    return $this->request('PUT', $uri, $query, $body);
  }

  // Sends a DELETE request
  public function delete(string $uri, array $query = [])
  {
    return $this->request('DELETE', $uri, $query);
  }

  // Sends a resolve request
  public function resolve(string $url)
  {
    return $this->get('/resolve', ['url' => $url]);
  }

  // Sends a oembed request
  public function oembed(string $url, array $query = [])
  {
    return $this->get(self::OEMBED_URI, array_merge([
      'url' => $url,
      'format' => 'json'
    ], $query));
  }
}
