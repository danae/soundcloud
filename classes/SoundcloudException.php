<?php
namespace Soundcloud;

use Soundcloud\Http\Response;

class SoundcloudException extends \Exception
{
  // Variables
  private $response;
  
  // Constructor
  public function __construct($message, Response $response = null)
  {
    parent::__construct($message);
    $this->response = $response;
  }
  
  // Gets response
  public function getResponse()
  {
    return $this->response;
  }
}
