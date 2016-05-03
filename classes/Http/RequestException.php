<?php
namespace Soundcloud\Http;

class RequestException extends \Exception
{
  // Constructor
  public function __construct($message)
  {
    parent::__construct($message);
  }
}
