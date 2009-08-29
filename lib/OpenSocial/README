Welcome to the OpenSocial PHP Client Library README!


--[ USING THE LIBRARY ]---------------------------------------------------------
Everything needed to use the library in a project is included in the 
{CLIENT ROOT}/src directory.  To use the library, make sure that the contents
of this directory are somewhere in your php include path and then place the
following line in your php script:

  require_once "osapi.php";

If you're using the packaged distribution, you should have gotten the library
under a directory named osapi.  Just place that directory wherever you keep your
external libraries and then call:

  require_once "osapi/osapi.php";

If you need to fudge your include path, sometimes it's handy to do something 
like

  set_include_path(get_include_path() . PATH_SEPARATOR . "path/to/library");

if you don't have easy access to php.ini.  Note that the samples use this 
method since they're meant to be run directly from a fresh and clean SVN
checkout.


--[ SAMPLES ]-------------------------------------------------------------------
Example pages using the library are located in {CLIENT ROOT}/examples.  You
should be able to run them directly by unzipping or checking out the project
to a directory served by your PHP-enabled web server and navigating to each 
sample in a browser.


--[ DOCS ]----------------------------------------------------------------------
To build docs, from the project root run:
    phpdoc -d src -t doc -ti "OpenSocial PHP Client" -o HTML:frames:phphtmllib

Then your docs will be in {CLIENT ROOT}/doc/index.html
(You need to have PHPDoc http://www.phpdoc.org/ to create documentation)


--[ TESTS ]---------------------------------------------------------------------
Tests are meant to be run through a php command line, and not called directly
through the web browser. To run the unit tests from the command line:
  $ cd /path/to/client
  $ phpunit AllTests test/AllTests.php
  
(You need PHPUnit http://www.phpunit.de/ for the tests to run.)

For pretty reports, run:
  $ phpunit --coverage-html report AllTests test/AllTests.php
  
Then your reports will be in {CLIENT ROOT}/reports/index.html.
(You need Xdebug http://www.xdebug.org/ to generate pretty reports)


--[ CONTRIBUTING ]--------------------------------------------------------------
PLEASE, PLEASE, PLEASE contribute any fixes or changes you make to the library
back to the original source!  The project homepage is located at:

  http://opensocial-php-client.googlecode.com/

If you'd like to give feedback on the library or even ask how to use it, we've
got a discussion group set up at:

  http://groups.google.com/group/opensocial-client-libraries
  
Also, we like to be in IRC from time to time.  Ask your questions in 
#opensocial at irc.freenode.net

