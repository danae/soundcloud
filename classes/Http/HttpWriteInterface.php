<?php
namespace Soundcloud\Http;

interface HttpWriteInterface extends HttpInterface
{
  // Sets headers
  public function withHeaders(array $headers);
  public function withHeader($name, $value);
  
  // Sets body
  public function withBody($body);
}
