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

$yql  = new YahooYQLQuery();

$data = $yql->execute('select * from delicious.feeds.popular;');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<title>Yahoo! Developer Network: YQL</title>
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.0.0b1/build/cssreset/reset-min.css&3.0.0b1/build/cssfonts/fonts-min.css&3.0.0b1/build/cssgrids/grids-min.css&3.0.0b1/build/cssbase/base-min.css">
<style>
  body { text-align: left; }
</style>
</head>
<body id="ydoc" class="yui-skin-sam">

<ul>
<?php foreach($data->query->results->item as $bookmark): ?>
 <li><a href="<?php echo $bookmark->link; ?>" title="<?php echo $bookmark->title; ?>"><?php echo $bookmark->title; ?></a></li>
<?php endforeach; ?>
</ul>

<script type="text/javascript" src="http://yui.yahooapis.com/combo?3.0.0b1/build/yui/yui-min.js"></script>
</body>
</html>