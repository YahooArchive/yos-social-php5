<?php

require_once 'Auth/OpenID/Message.php';
require_once 'Auth/OpenID/Extension.php';
require_once 'OAuth/OAuth.php';

define('Auth_OpenID_OAUTH_NS_URI', 'http://specs.openid.net/extensions/oauth/1.0');

Auth_OpenID_registerNamespaceAlias(Auth_OpenID_OAUTH_NS_URI, 'oauth');

/**
 * An object to hold the state of a oauth request.
 *
 * required: A list of the required fields in this simple registration
 * request
 *
 * optional: A list of the optional fields in this simple registration
 * request
 *
 * @package OpenID
 */
class Auth_OpenID_OAuthRequest extends Auth_OpenID_Extension {

    var $ns_alias = 'oauth';
	  var $ns_uri   = Auth_OpenID_OAUTH_NS_URI;

    /**
     * Initialize an empty oauth request.
     */
    function Auth_OpenID_OAuthRequest($consumer=null, $scope=null)
    {
        $this->consumer = $consumer;
        $this->scope = $scope;
    }

    function getExtensionArgs()
    {
        $args = array();

        if ($this->consumer) {
            $args['consumer'] = $this->consumer;
        }

        if ($this->scope) {
            $args['scope'] = $this->scope;
        }

        return $args;
    }
}

/**
 * Represents the data returned in a oauth response
 * inside of an OpenID C{id_res} response. This object will be created
 * by the OpenID server, added to the C{id_res} response object, and
 * then extracted from the C{id_res} message by the Consumer.
 *
 * @package OpenID
 */
class Auth_OpenID_OAuthResponse extends Auth_OpenID_Extension {

  var $ns_alias = 'oauth';
	var $ns_uri = Auth_OpenID_OAUTH_NS_URI;

	function Auth_OpenID_OAuthResponse()
    {
        $this->authorized_request_token = null;
    }

    function fromSuccessResponse(&$success_response, $signed_only=true)
    {
        $obj = new Auth_OpenID_OAuthResponse();
        $obj->ns_uri = Auth_OpenID_OAUTH_NS_URI;

        if ($signed_only) {
            $args = $success_response->getSignedNS($obj->ns_uri);
        } else {
            $args = $success_response->message->getArgs($obj->ns_uri);
        }

        if ($args === null || Auth_OpenID::isFailure($args)) {
            return null;
        }

        $obj->authorized_request_token = new OAuthToken($args['request_token'], '');

        return $obj;
    }
}

?>
