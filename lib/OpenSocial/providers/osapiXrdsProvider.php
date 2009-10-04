<?php
/**
 * @package OpenSocial
 * @license Apache License
 *
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * The osapiXrdsProvider class attempts to auto-detect the OAuth and OpenSocial/PortableContacts
 * end-points using XRDS 1.0 Simple discovery. For this to work the provider needs to support XRDS
 * discovery. The easiest way to test if the site supports XRDS is by checking it using:
 * http://www.chabotc.com/xrds-test.php
 *
 * @author Chris Chabot
 */
class osapiXrdsProvider extends osapiProvider {
  public $httpProvider;
  
  private $providerUrl;

  public function __construct($providerUrl, osapiStorage $storage, osapiHttpProvider $httpProvider = null) {
    $this->providerUrl = $providerUrl;
    $this->providerName = $this->providerUrl;
    if ($httpProvider) {
      $this->httpProvider = $httpProvider;
    } else {
      $this->httpProvider = new osapiCurlProvider();
    }
    // See if we have any cached XRDS info so we can skip the http request. Cache time is currently hard-coded to 1 day
    if (($xrds = $storage->get($this->providerUrl.":xrds", 24 * 60 * 60)) !== false) {
      list($requestTokenUrl, $authorizeUrl, $accessTokenUrl, $restEndpoint, $rpcEndpoint, $this->providerName, $isOpenSocial) = $xrds;
    } else {
      // Start XRDS discovery

      $xrds = XrdsSimpleParser::doOAuthDiscovery($this->providerUrl, true, $this->httpProvider);

      // OAuth end-points
      $requestTokenUrl = $xrds['requestUrl'];
      $authorizeUrl = $xrds['authorizeUrl'];
      $accessTokenUrl = $xrds['accessUrl'];
      if (empty($requestTokenUrl) || empty($authorizeUrl) || empty($accessTokenUrl)) {
        throw new osapiException("Could not discover the required OAuth end-points");
      }
      
      $rddXml = $xrds['rdd'];

      // PortableContacts end-point, optional
      $pocoUrl = XrdsSimpleParser::getServiceByType($rddXml, 'http://portablecontacts.net/spec/1.0');
      if (empty($pocoUrl)) $pocoUrl = null;

      // These are not official end-point names, only partuza supports them currently, a proposal has been send to the spec list
      $restEndpoint = XrdsSimpleParser::getServiceByType($rddXml, 'http://ns.opensocial.org/rest/0.8');
      $rpcEndpoint = XrdsSimpleParser::getServiceByType($rddXml, 'http://ns.opensocial.org/rpc/0.8');
      if (empty($restEndpoint) && empty($rpcEndpoint)) {
        // no experimental simple end points found, try to find the rest base based on the people end-point
        $peopleEndpoint = XrdsSimpleParser::getServiceByType($rddXml, 'http://ns.opensocial.org/people/0.8');
        $restEndpoint = str_replace('/people', '', $peopleEndpoint);
      }
      $isOpenSocial = true;
      if (empty($restEndpoint) && empty($rpcEndpoint) && empty($pocoUrl)) {
        throw new osapiException("No supported social end-points found");
      } elseif (empty($restEndpoint) && empty($rpcEndpoint) && !empty($pocoUrl)) {
        $isOpenSocial = false;
        $restEndpoint = $pocoUrl;
        $rpcEndpoint = null;
      }

      // Store the results in cache so we can skip it next time
      $storage->set($this->providerUrl.":xrds", array((string)$requestTokenUrl, (string)$authorizeUrl, (string)$accessTokenUrl, (string)$restEndpoint, (string)$rpcEndpoint, (string)$this->providerName, (int)$isOpenSocial));
    }
    // Construct our selves based on the XRDS discovered end-points
    parent::__construct($requestTokenUrl, $authorizeUrl, $accessTokenUrl, $restEndpoint, $rpcEndpoint, $this->providerName, $isOpenSocial);
  }
}
