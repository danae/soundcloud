# purplelum/Soundcloud

**purplelum/Soundcloud** is a Soundcloud API wrapper for PHP. Currently it supports only the public functions found in the JS api (the ones without authorization).

## Installation

The Soundcloud library is best installed using Composer. The library is found as **dengsn/soundcloud** in de Packagist repository. Otherwise you can download the latest release or the dev-master branch as an archive.

## Usage

The following code fragment explains the basic usage of the Soundcloud library. For more information on the API itself, refer to the Soundcloud API [documentation](https://developers.soundcloud.com/docs/api/reference).

    require 'vendor/autoload.php';

    use Soundcloud\Soundcloud;
    use Soundcloud\SoundcloudException;
    
    try
    {
      // Create a new instance of the API
      $sc = new Soundcloud($client_id, $redirect_url);
      
      // Returns an array containing the tracks
      // Other functions are post($url, array $data), put($url, array $data), 
      // delete($url), resolve($url) and oembed($url, array $params) 
      $sc->get('/user/<user>/tracks'); 
    }
    catch (SoundcloudException $ex)
    {
      // Thrown if no 200 status was returned
    }