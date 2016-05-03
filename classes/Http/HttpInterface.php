<?php
namespace Soundcloud\Http;

interface HttpInterface
{
  // Gets headers
  public function getHeaders();
  public function getHeader($name);
  public function hasHeader($name);
  
  // Gets body
  public function getBody();
  public function getRawBody();
}
