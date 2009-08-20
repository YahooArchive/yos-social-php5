<?php

/**
 * Static utility class for fetching, parsing, and querying XRDS-Simple documents.
 * Also contains a convenience method for performing OAuth Discovery.
 * @see http://xrds-simple.net/core/1.0/
 * @see http://oauth.googlecode.com/svn/spec/discovery/1.0/drafts/2/spec.html
 * @author Joseph Smarr (joseph@plaxo.com)
 *
 * Sample usage (generic XRDS-Simple):
 * 
 *   // perform discovery on a URL and return the parsed 
 *   // Resource Description Document XML (as a SimpleXMLElement)
 *   $rddXml = XrdsSimpleParser::fetchRdd('http://url-to-perform-discovery-on');
 * 
 *   // look in the RDD for a Service URI with a given Type
 *   $url = XrdsSimpleParser::getServiceByType($rddXml, 'http://service-type-to-look-for');
 *   // look in the same RDD for another Service, but only in an XRD with xml:id="myXrdId".
 *   $url2 = XrdsSimpleParser::getServiceByType($rddXml, 'http://some-other-type', 'myXrdId');
 *
 *   // now you can access the API at $url or $url2
 *
 * Sample usage (OAuth Discovery):
 * 
 *   // perform OAuth discovery on a protected endpoint, e.g. after getting a 
 *   // "WWW-Authenticate: OAuth" response header when accessing it directly
 *   $endpoints = XrdsSimpleParser::doOAuthDiscovery('http://protected-resource-url');
 * 
 *   $requestUrl = $endpoints['requestUrl']; // endpoint for getting a request token
 *   $authorizeUrl = $endpoints['authorizeUrl']; // endpoint for authorizing request token
 *   $accessUrl = $endpoints['accessUrl']; // endpoint for exchanging authorized request token for access token
 * 
 *   // now you can perform oauth, using these endpoints
 */
class XrdsSimpleParser {

  /**
   * Discovers, fetches, parses, and returns the Resource Descrption Document 
   * associated with the given URL, or false if no RDD can be found.
   * Return value is a SimpleXmlElement of the RDD, suitable for use with getServiceByType.
   *
   * @param $url endpoint URL to perform discovery on (string)
   * @return     associated Resource Description Document (SimpleXmlElement), or false on error
   */
  public static function fetchRdd($url, $httpProvider) {
    $rddUrl = null;

    // use GET Protocol (5.1.2); TODO: optionally try HEAD Protocol first?
    $url = preg_replace('/#[^?]*/', '', $url); // strip fragment (5.1)
    $info = self::doFetch($httpProvider, $url);

    if (!$info) return false; // no response or unable to parse

    if (isset($info['headers']['X-XRDS-Location'])) {
      $rddUrl = $info['headers']['X-XRDS-Location'];
    } else if (isset($info['headers']['Content-Type']) && $info['headers']['Content-Type'] == 'application/xrds+xml') {
      $rddUrl = $url; // this is the RDD
    } else if (preg_match('/<meta +http-equiv="X-XRDS-Location" +content="([^"]*)"/i', $info['body'], $matches)) {
      $rddUrl = $matches[1]; // TODO: should use XML parser or more robust regex here
    }
    if (!$rddUrl) return false; // failed to find XRDS location

    // fetch RDD at newly discovered location, if needed
    if ($rddUrl != $url) {
      $newResponse = self::doFetch($httpProvider, $rddUrl);
      $body = $newResponse['body'];
    } else $body = $info['body'];

    // parse the RDD and return it
    // TODO: do we also need to remember the fragment and return it (for XRD id)?
    $rddXml = new SimpleXmlElement($body);
    return $rddXml;
  }

  /**
   * Returns the URI for a Service with the given Type in the given RDD.
   * 
   * @param $rddXml  Resource Description Document to use, e.g. as returned by fetchRdd (SimpleXmlElement)
   * @param $typeUri Type URI of the Service to look for (string)
   * @param $xrdId   Optional ID to specify using a specific XRD element with a matching xml:id attribute
   * @return         URL for the requested service (string), or false on error
   */
  public static function getServiceByType($rddXml, $typeUri, $xrdId = null) {
    if (!$rddXml || !$rddXml->XRD) return false;

    // find valid XRD (7.1)
    $xrd = null;
    $now = time();
    foreach ($rddXml->XRD as $xrdi) {
      if ($xrdi['version'] != "2.0") continue; // must have a version="2.0" attribute
      if (isset($xrdi->Expires) && strtotime($xrdi->Expires) < $now) continue; // must not be expired
      if ($xrdi->Type != 'xri://$xrds*simple') continue; // must have XRDS-Simple Type

      if ($xrdId) {
        // implicit namespace for xml:id, see http://www.w3.org/TR/REC-xml-names/#ns-decl
        $attrs = $xrdi->attributes('http://www.w3.org/XML/1998/namespace'); 
        if ($attrs['id'] != $xrdId) continue; // only select XRD with matching xml:id, if specified
      }
      $xrd = $xrdi; // prefer the latest XRD, unless we match id
      if ($xrdId) break; // if we got here, we must have found a matching XRD, so we're done
    }     

    if (!$xrd) return false; // failed to find valid XRD

    // find valid Services, sorted by priority (7.2)
    $validServices = array(); 
    foreach ($xrd->Service as $service) {
      if (!$service->URI) continue; // we only want services with a URI
      // look to see if this service has the type we want
      foreach ($service->Type as $type) {
        if ($type == $typeUri) {
          $validServices[] = $service;
          break; // done with this service
        }
      }
    }    
    if (empty($validServices)) return false; // no valid services
    usort($validServices, array('self', 'priorityCmp')); // sort services by priority

    // find valid URIs, sorted by priority (7.2 step 4)
    $uris = array();
    foreach ($validServices[0]->URI as $uri) {
      $uris[] = "$uri"; // cast uri elem to string
    }
    usort($uris, array('self', 'priorityCmp')); // sort URIs by priority

    return $uris[0];
  }

  /**
   * Performs OAuth Discovery on the given URL, and returns the OAuth endpoints.
   * 
   * @param $url       Protected endpoint URL to perform OAuth Discovery on (string)
   * @param $returnRdd Optional flag to also return the RDD found during discovery
   * @return           array containing named OAuth endpoints, and rdd if requested,
   *                   or false if OAuth Discovery fails
   * 
   * The returned endpoints information looks like this:
   *   array(
   *     'requestUrl'   => 'http://endpoint-for-getting-a-request-token',
   *     'authorizeUrl' => 'http://endpoint-for-authorizing-request-token',
   *     'accessUrl'    => 'http://endpoint-for-getting-an-access-token',
   *     'rddXml'       => [SimpleXmlElement for RDD, if requested]
   *   )
   */ 
  public static function doOAuthDiscovery($url, $returnRdd = false, $httpProvider = null) {
    if (!$httpProvider) {
      $httpProvider = new osapiCurlProvider();
    }
    
    $rddXml = XrdsSimpleParser::fetchRdd($url, $httpProvider);
    $oauthUrl = XrdsSimpleParser::getServiceByType($rddXml, 'http://oauth.net/discovery/1.0');
    if (!$oauthUrl) return false; // can't find rdd with oauth info

    $xrdId = null;
    if (strpos($oauthUrl, "#") !== false) {
      // extract fragment, which specifies the xml:id for the right XRD
      list($oauthUrl, $xrdId) = explode('#', $oauthUrl, 2);
    }
    
    // if we were pointed to a different RDD, fetch it now (but if we just got a fragment, keep the same RDD)
    if ($oauthUrl && $oauthUrl != $url) $rddXml = XrdsSimpleParser::fetchRdd($oauthUrl, $httpProvider);
    if (!$rddXml) return false; // can't find final RDD

    // look up the OAuth endpoints and return them, along with the final RDD
    $requestUrl   = XrdsSimpleParser::getServiceByType($rddXml, 'http://oauth.net/core/1.0/endpoint/request', $xrdId);
    $authorizeUrl = XrdsSimpleParser::getServiceByType($rddXml, 'http://oauth.net/core/1.0/endpoint/authorize', $xrdId);
    $accessUrl    = XrdsSimpleParser::getServiceByType($rddXml, 'http://oauth.net/core/1.0/endpoint/access', $xrdId);

    $endpoints = array(
      'requestUrl'   => $requestUrl,
      'authorizeUrl' => $authorizeUrl,
      'accessUrl'    => $accessUrl
    );
    if ($returnRdd) $endpoints['rdd'] = $rddXml; // in case you want to perform additional discovery
    return $endpoints;
  
  }

  ////////////////////////////////////////////////////////////////////////////////
  // --- Internal helper functions --- 

  /** Returns the numerical priority attribute for the given SimpleXmlElement. */
  private static function getPriority($xml) {
    $priorityStr = (string)$xml['priority'];
    $priority = (int)$priorityStr;
    if ($priority <= 0 && $priorityStr !== "0") {
      $priority = 10000000; // sort empty/invalid priorities to the end
    }
    return $priority;
  }

  /** Compares two objects by thier priority attribute. */
  private static function priorityCmp($s1, $s2) {
    return self::getPriority($s1) - self::getPriority($s2);
  }

  /** Fetches the content at the given URL. */
  private static function doFetch($httpProvider, $url) {
    $response = $httpProvider->send($url, 'GET');
    return array(
      "statusCode" => $response["http_code"],
      "headers" => $response["headers"],
      "body" => $response["data"]
    );
  }
}
