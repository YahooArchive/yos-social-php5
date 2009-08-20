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


/*
 * Make sure you obtain oauth keys before continuing by visiting: http://developer.yahoo.com/dashboard
 */

# openid/oauth credentials
define('OAUTH_CONSUMER_KEY', '####################');
define('OAUTH_CONSUMER_SECRET', '####################');
define('OAUTH_DOMAIN', '####################');
define('OAUTH_APP_ID', '####################');

$oauthapp = new YahooOAuthApplication(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET, OAUTH_APP_ID, OAUTH_DOMAIN);

// handle openid/oauth
if(isset($_REQUEST['openid_mode']))
{
  switch($_REQUEST['openid_mode'])
  {
    case 'discover':
    case 'checkid_setup':
    case 'checkid_immediate':

      // handle yahoo simpleauth popup + redirect to yahoo! open id with open app oauth request
      header('Location: '.$oauthapp->getOpenIDUrl(isset($_REQUEST['popup']) ? $oauthapp->callback_url.'?close=true': $oauthapp->callback_url)); exit;

    break;

    case 'id_res':

    // validate claimed open id

    // extract approved request token from open id response
    $request_token = new YahooOAuthRequestToken($_REQUEST['openid_oauth_request_token'], '');
    $_SESSION['yahoo_oauth_request_token'] = $request_token->to_string();

    // exchange request token for access token
    $oauthapp->token = $oauthapp->getAccessToken($request_token);

    // store access token for later
    $_SESSION['yahoo_oauth_access_token'] = $oauthapp->token->to_string();

    break;

    case 'cancel':

      unset($_SESSION['yahoo_oauth_access_token']);
      unset($_REQUEST['openid_mode']);

      header('Location: '.$oauthapp->callback_url); exit;

      // openid cancelled
    break;

    case 'associate':
      // openid associate user
    break;

    default:
  }
}

header('Cache-Control: Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="X-XRDS-Location" content="xrds.xml">
<title>Yahoo! Developer Network: OpenID + OAuth Popup</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.0.0b1/build/cssreset/reset-min.css&3.0.0b1/build/cssfonts/fonts-min.css&3.0.0b1/build/cssgrids/grids-min.css&3.0.0b1/build/cssbase/base-min.css">
<link rel="stylesheet" type="text/css" href="css/simpleauth.css">
</head>
<body id="ydoc" class="yui-skin-sam">
<?php if(!isset($_REQUEST['openid_mode']) && !isset($_SESSION['yahoo_oauth_access_token'])): ?>
<div id="ysimpleauth-login" class="authbar">
  <span class="svy-sg">
    <a href="simpleauth.php?openid_mode=discover" title="Sign in with a Yahoo! ID">
      <span><span><span><span><span>Sign In with a</span><span style="display:none"> Yahoo!</span><span class="rtext"> ID</span></span></span></span></span>
    </a>
  </span>
</div>
<?php else: ?>

<?php if(isset($_SESSION['yahoo_oauth_request_token'])): ?>
<div id="ysimpleauth-logout" class="authbar"><a href="simepleauth.php?openid_mode=cancel">Logout</a></div>
<?php endif; ?>

<?php

// restore access token from session
$oauthapp->token = YahooOAuthAccessToken::from_string($_SESSION['yahoo_oauth_access_token']);

$profile  = $oauthapp->getProfile();
$updates  = $oauthapp->getUpdates(null, 0, 20);
$connections = $oauthapp->getConnections(null, 0, 1000);

// $oauthapp->setStatus(null, 'making yahoo! open...');
// $oauthapp->insertUpdate(null, 'my demo app update', 'my demo app update description.......', 'http://mylink.com/');
?>

<div id="profile" class="vcard">
  <span class="fn n">
		<a href="<?php echo $profile->profileUrl; ?>"  title="<?php echo $profile->nickname; ?>'s Profile" ><img src="<?php echo $profile->image->imageUrl; ?>" height="<?php echo ceil($profile->image->height / 2); ?>" width="<?php echo ceil($profile->image->width / 2); ?>" alt="<?php echo $profile->nickname; ?>'s Profile Picture"></a>
    <span class="given-name"><?php echo $profile->givenName; ?></span>
    <span class="family-name"><?php echo $profile->familyName; ?></span>
  </span>

	<div class="adr">
		<span class="locality"><?php echo $profile->location; ?></span>
	</div>

	<em><?php echo $profile->status->message;?></em>
</div>

<div id="updates">
  <h1><?php echo $profile->nickname; ?>'s Updates</h1>
  <?php if(!empty($updates)): ?>
  <ul>
    <?php foreach($updates as $update): ?>
    <li><a title="<?php echo $update->loc_longForm; ?>" href="<?php echo $update->link; ?>"><img src="<?php echo $update->loc_iconURL; ?>" height="16" width="16" alt="<?php echo $update->loc_localizedName; ?>"><?php echo $update->loc_longForm; ?></a></li>
    <?php endforeach; ?>
  </ul>
  <?php else: ?>
    <span>No updates, make some!</span>
  <?php endif; ?>
</div>

<div id="socialgraph">
  <h1><?php echo $profile->nickname; ?>'s Social Graph</h1>
  <?php if(!empty($connections)): ?>
  <ul>
    <?php foreach($connections as $connection): ?>
    <li><a title="<?php echo $connection->nickname; ?>'s Profile" href="<?php echo $connection->profileUrl; ?>"><img src="<?php echo $connection->image->imageUrl; ?>" height="<?php echo $connection->image->height; ?>" width="<?php echo $connection->image->width; ?>" alt="<?php echo $connection->nickname; ?>'s Profile Picture"></a></li>
    <?php endforeach; ?>
  </ul>
  <?php else: ?>
    <span>No contacts, find some!</span>
  <?php endif; ?>
</div>

<?php endif; ?>


<?php if(isset($_REQUEST['close'])): ?>
<script type="text/javascript">
// close popup window and refresh page for access token
if(window.opener)
{
  window.opener.location.replace(window.opener.location.href);
  window.opener.focus();

  window.close();
}
</script>
<?php endif; ?>
<script type="text/javascript" src="http://yui.yahooapis.com/combo?3.0.0b1/build/yui/yui-min.js"></script>
<script type="text/javascript" src="js/simpleauth.js"></script>
</body>
</html>