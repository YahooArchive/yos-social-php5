Yahoo! Social SDK - PHP5
========================

**Warning**
=======

**_The yos-social-php5 SDK is released alpha and is experimental. The difference between the yos-social-php5 sdk and the yos-social-php sdk is that the storage is left for the user to implement.
The new implementation is more flexible as developers can store the OAuth tokens in a php session, in a database, or in memcache. It does not require php sessions unless a partner wants it to.
This means there is no method equivalent to 'hasSession' or 'requireSession'. An example of how this functionality could be implemented is in the yos-social-php5/examples/simpleauth/simpleauth.php._**


Find documentation and support on Yahoo! Developer Network: http://developer.yahoo.com

 * Yahoo! Application Platform - http://developer.yahoo.com/yap/
 * Yahoo! Social APIs - http://developer.yahoo.com/social/
 * Yahoo! Query Language - http://developer.yahoo.com/yql/

Hosted on GitHub: http://github.com/yahoo/yos-social-php5/tree/master

License
=======

@copyright: Copyrights for code authored by Yahoo! Inc. is licensed under the following terms:
@license:   BSD Open Source License

Yahoo! Social SDK
Software License Agreement (BSD License)
Copyright (c) 2009, Yahoo! Inc.
All rights reserved.

Redistribution and use of this software in source and binary forms, with
or without modification, are permitted provided that the following
conditions are met:

* Redistributions of source code must retain the above
  copyright notice, this list of conditions and the
  following disclaimer.

* Redistributions in binary form must reproduce the above
  copyright notice, this list of conditions and the
  following disclaimer in the documentation and/or other
  materials provided with the distribution.

* Neither the name of Yahoo! Inc. nor the names of its
  contributors may be used to endorse or promote products
  derived from this software without specific prior
  written permission of Yahoo! Inc.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


The Yahoo! Social PHP SDK code is subject to the BSD license, see the LICENSE file.


Requirements
============

The following dependencies are bundled with the Yahoo! PHP SDK, but are under
terms of a separate license. See the bundled LICENSE files for more information:

 * OAuth      - http://code.google.com/p/oauth
 * OpenID     - http://www.openidenabled.com/php-openid/
 * OpenSocial - http://code.google.com/p/opensocial-php-client/
 * JSON       - http://pear.php.net/Services_JSON


Installation
============

After downloading and unpacking the release, copy the contents of 'lib'
to a directory that is accessible via the PHP include_path method.


Examples
========

The sdk comes with example code, but you must first upcate the file
'examples/common.inc.php' with your OAuth consumer key and secret.


    define('OAUTH_CONSUMER_KEY', '###');
    define('OAUTH_CONSUMER_SECRET', '###');
    define('OAUTH_DOMAIN', '###');
    define('OAUTH_APP_ID', '###');


Create OAuth applications in the Yahoo! Developer Dashboard:

http://developer.yahoo.com/dashboard/


## Fetching YQL:

See the bundled sample code in examples/yql/delicious.php.

    $yql = new YahooYQLQuery();
    $response = $yql->execute('select * from delicious.feeds.popular');

    if(isset($response->query) && isset($response->query->results))
    {
      var_dump($response->query->results);
    }
    elseif(isset($response->error))
    {
      print sprintf('YQL query failed with error: "%s".', $response->error->description);
    }
    else
    {
      print 'YQL response malformed.';
    }


## Fetching Social Data:

    # Yahoo! OAuth Credentials - http://developer.yahoo.com/dashboard/

    $CONSUMER_KEY      = '##';
    $CONSUMER_SECRET   = '##';
    $APPLICATION_ID    = '##';
    $CALLBACK_URL      = '##';

    $oauthapp      = new YahooOAuthApplication($CONSUMER_KEY, $CONSUMER_SECRET, $APPLICATION_ID, $CALLBACK_URL);

    # Fetch request token
    $request_token = $oauthapp->getRequestToken($CALLBACK_URL);

    # Redirect user to authorization url
    $redirect_url  = $oauthapp->getAuthorizationUrl($request_token);

    # Exchange request token for authorized access token
    $access_token  = $oauthapp->getAccessToken($request_token, $_REQUEST['oauth_verifier']);

    # update access token
    $oauthapp->token = $access_token;

    # fetch user profile
    $profile = $oauthapp->getProfile();

    var_dump($profile);

## Inserting Updates

    $oauthapp->insertUpdate(array(
       'title' => "cloned the yos-social-php5 SDK on Github",
       'description' => "A PHP5 SDK for YQL",
       'link' => "http://github.com/yahoo/yos-social-php5",
       'imgURL' => 'http://github.com/images/modules/header/logov3.png', 
       'imgWidth' => '100',
       'imgHeight' => '45'
    ));

## Signing with SimpleAuth (OpenID + OAuth):

    See the bundled sample code in examples/simpleauth/simpleauth.php.


## Fetching people and activities with OpenSocial:

    See the bundled sample code in examples/opensocial/profile.php.


## Adding a Contact:

	# valid 'Yahoo! Contacts Read/Write' scopes are required to support this method. 

    $contact_fields = array();
    $contact_fields[] = array('type' => 'email', 'value' => 'me@domain.com');
    $contact_fields[] = array('type' => 'name', 'value' => array('givenName'=> 'John', 'familyName' => 'Doe'));
    $contact = array('fields' => $contact_fields);

    var_dump($oauthapp->addContact($contact));

Tests
=====

The Yahoo! PHP SDK comes with a test suite to validate functionality. The tests also
show functional examples and results. To run the test suite, simply execute the test suite:

    php phpunit test/AllTests.php
