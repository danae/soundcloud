**soundcloud-php** is a SoundCloud® API wrapper for PHP 7.0 or higher. 

## Installation

The SoundCloud library is best installed using Composer. The library is found as **dengsn/soundcloud** in the Packagist repository. If your project does not use composer, you can download the latest release or the dev-master branch as an archive. The library has no additional dependencies.

## Usage

The following code fragment explains the basic usage of the Soundcloud library. For more information on the API itself, refer to the Soundcloud API [documentation](https://developers.soundcloud.com/docs/api/reference).

    require 'vendor/autoload.php';

    use Soundcloud\Soundcloud;
    use Soundcloud\SoundcloudException;
    
    try
    {
      // Create a new instance of the API
      $sc = new Soundcloud($client_id,$client_secret);
      
      // Set the redirect URI
      $sc->setRedirectUri($redirect_uri);
      
      // Get an authorization code
      $sc->authorizeWithCode($code);
      
      // Return an array containing the tracks
      $sc->get('/me/tracks'); 
      
      // The previous lines can also be written in one line as follows
      (new Soundcloud($client_id, $client_secret, $redirect_url))
        ->authorizeWithCode($code)
        ->get('/me/tracks');
    }
    catch (SoundcloudException $ex)
    {
      // Thrown if no 20X or 30X status was returned
    }

## Functions

This paragraph describes all functions of the public API. Underlying classes and functions are not documented.

### Instantination and authorization

To create a new `Soundcloud` object, you need to specify your `client_id` and optionally your `client_secret` and `redirect_uri`.

    $soundcloud = new Soundcloud($client_id,$client_secret, $redirect_uri);
    
You can also use the setter functions for these parameters.

    $soundcloud->setClientSecret($client_secret);
    $soundcloud->setRedirectUri($redirect_uri);
    
If you received the access code from the connection page, you can use that tho authorize the API and access personal resources such as `/me ` or private sets. The function returns the `Soundcloud` object and throws a `SoundcloudException` if the request failed.

    $soundcloud->authorizeWithCode($code);
    
You can also create an access token by entering the user's credentials directly. The function returns the `Soundcloud` object and throws a `SoundcloudException` if the request failed.

    $soundcloud->authorizeWithCredentials($username, $password);

This authorization method is not recommended by [SoundCloud](https://developers.soundcloud.com/docs/api/guide#authentication) and is prohibited is using other user's accounts:
> Our Terms of Service specify that you must use the Connect with SoundCloud screen unless you have made a separate arrangement with us.

### Requests

The following functions return a stdClass object created by `json_decode`ing the response body. All request functions throw a `SoundcloudException` with the status code if the request failed.

    $soundcloud->get($uri, array $query = []);
    $soundcloud->post($uri, array $body = [], array $query = []);
    $soundcloud->put($uri, array $body[], array $query = []);
    $soundcloud->delete($uri, array $query = []);
    $soundcloud->resolve($url, array $query = []);
    $soundcloud->oembed($url, array $query = []);
