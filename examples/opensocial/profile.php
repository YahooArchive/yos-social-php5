<?php

/**
 * Yahoo! PHP5 SDK
 *
 *  * Yahoo! Query Language
 *  * Yahoo! Social API
 *
 * Find documentation and support on Yahoo! Developer Network: http://developer.yahoo.com
 *
 * Hosted on GitHub: http://github.com/yahoo/yos-social-php5/tree/master
 *
 * @package    yos-social-php5
 * @subpackage yahoo
 *
 * @author     Dustin Whittle <dustin@yahoo-inc.com>
 * @copyright  Copyrights for code authored by Yahoo! Inc. is licensed under the following terms:
 * @license    BSD Open Source License
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *   THE SOFTWARE.
 **/

require_once(dirname(__FILE__).'/../common.inc.php');

/**
 * Yahoo! Open Social Rest End Point: http://appstore.apps.yahooapis.com/social/rest
 */

// get current session id
$session_id = session_id();

// enable osapi logging to file
osapiLogger::setLevel(osapiLogger::INFO);
osapiLogger::setAppender(new osapiFileAppender(sys_get_temp_dir().'/opensocial.log'));

// create yahoo open social provider
$provider    = new osapiYahooProvider();

// create file system storage using system temp directory
$storage     = new osapiFileStorage(sys_get_temp_dir());

// if this is a YAP application, the access token and secret
// will be provided.
if(isset($_POST['yap_viewer_access_token']) &&
   isset($_POST['yap_viewer_access_token_secret']) &&
   isset($_POST['yap_viewer_guid'])) {

  $oauth = new osapiOAuth3Legged(
      OAUTH_CONSUMER_KEY,
      OAUTH_CONSUMER_SECRET,
      $storage,
      $provider,
      $_POST['yap_viewer_guid'],
      $_POST['yap_viewer_guid'],
      $_POST['yap_viewer_access_token'],
      $_POST['yap_viewer_access_token_secret']
  );
}
else {
  $oauth = osapiOAuth3Legged::performOAuthLogin(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, $storage, $provider, $session_id);
}

// create open social instance from yahoo provider + oauth credentials
$opensocial = new osapi($provider, $oauth);

// The number of friends to fetch.
$friend_count = 10;

// Start a batch so that many requests may be made at once.
$batch = $opensocial->newBatch();

// Fetch the user profile
$batch->add($opensocial->people->get(array('userId' => '@me', 'groupId' => '@self', 'fields' => array('displayName'))), 'self');

// Fetch the friends of the user
$batch->add($opensocial->people->get(array('userId' => '@me', 'groupId' => '@friends', 'fields' => array('id'), 'count' => 100)), 'friends');

// Request the activities of the current user
$batch->add($opensocial->activities->get(array('userId' => '@me', 'groupId' => '@self', 'count' => 100)), 'userActivities');

// Send the batch request
$result = $batch->execute();

foreach ($result as $key => $result_item) {
  if ($result_item instanceof osapiError) {
    $code = $result_item->getErrorCode();
    $message = $result_item->getErrorMessage();
    echo "<h2>There was a <em>$code</em> error with the <em>$key</em> request:</h2>";
    echo "<pre>";
    echo htmlentities($message);
    echo "</pre>";
  } else {
    echo "<h2>Response for the <em>$key</em> request:</h2>";
    echo "<pre>";
    echo htmlentities(print_r($result_item, True));
    echo "</pre>";
  }
}
