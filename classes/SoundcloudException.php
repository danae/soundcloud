<?php
namespace Soundcloud;

use Exception;

class SoundcloudException extends Exception
{
  // Constructor
  public function __construct($status)
  {
    parent::__construct("The request returned with HTTP status code " . $status);
  }
}
