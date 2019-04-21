<?php
class TestFuseboxyCaptcha extends UnitTestCase {


	function __construct() {
		if ( !class_exists('Framework') ) {
			include __DIR__.'/utility-captcha/framework/1.0.3/fuseboxy.php';
			Framework::$mode = Framework::FUSEBOX_UNIT_TEST;
			Framework::$configPath = __DIR__.'/utility-captcha/config/fusebox_config.php';
		}
		if ( !class_exists('F') ) {
			include __DIR__.'/utility-captcha/framework/1.0.3/F.php';
		}
		if ( !class_exists('Captcha') ) {
			include dirname(__DIR__).'/app/model/Captcha.php';
		}
		if ( !class_exists('Util') ) {
			include __DIR__.'/utility-captcha/model/Util.php';
		}
		if ( !class_exists('phpQuery') ) {
			include __DIR__.'/utility-captcha/lib/phpquery/0.9.5/phpQuery.php';
		}
	}


	function test__Captcha__getClientAPI() {
		// something must be returned
		$this->assertTrue( !empty(Captcha::getClientAPI()) );
	}


	function test__Captcha__getField() {
		global $fusebox;
		Framework::createAPIObject();
		// check config
		$this->assertFalse( Captcha::getField() );
		$this->assertPattern('/captcha config was not defined/i', Captcha::error());
		// check site key
		Framework::loadConfig();
		$fusebox->config['captcha']['siteKey'] = null;
		$this->assertFalse( Captcha::getField() );
		$this->assertPattern('/captcha \[siteKey\] was not defined/i', Captcha::error());
		$fusebox->config = null;
		// something must be returned
		Framework::loadConfig();
		$result = Captcha::getField();
		$this->assertTrue( !empty($result) );
		// clean-up
		$fusebox = null;
	}


	function test__Captcha__renderClientAPI() {
		// check output
		ob_start();
		Captcha::renderClientAPI();
		$output = ob_get_clean();
		$this->assertTrue( $output == Captcha::getClientAPI() );
		// parse output
		$doc = phpQuery::newDocument('<html><body>'.$output.'</body></html>');
		// must be javascript
		$this->assertTrue( $doc->find('script')->length != 0 );
		// javascript must have content
		$this->assertTrue( $doc->find('script')->is('[src]') );
		$js = Util::getPage( $doc->find('script')->attr('src') );
		$this->assertTrue( !empty($js) );
	}


	function test__Captcha__renderField() {
		global $fusebox;
		Framework::createAPIObject();
		Framework::loadConfig();
		// check output
		ob_start();
		Captcha::renderField();
		$output = ob_get_clean();
		$this->assertTrue( $output == Captcha::getField() );
		// parse output
		$doc = phpQuery::newDocument('<html><body>'.$output.'</body></html>');
		// must contain site-key
		$this->assertTrue( $doc->find('[data-sitekey]')->length != 0 );
		$this->assertTrue( $doc->find('[data-sitekey]')->attr('data-sitekey') == $fusebox->config['captcha']['siteKey'] );
		// clean-up
		$fusebox = null;
	}


	function test__Captcha__validate() {
		global $fusebox;
		Framework::createAPIObject();
		// check config
		$this->assertFalse( Captcha::validate() );
		$this->assertPattern('/captcha config was not defined/i', Captcha::error());
		// check secret key
		Framework::loadConfig();
		$fusebox->config['captcha']['secretKey'] = null;
		$this->assertFalse( Captcha::validate() );
		$this->assertPattern('/captcha \[secretKey\] was not defined/i', Captcha::error());
		$fusebox->config = null;
		// check response
		Framework::loadConfig();
		$this->assertFalse( Captcha::validate() );
		$this->assertPattern('/captcha not submitted/i', Captcha::error());
		$fusebox->config = null;
		// validate captcha remotely
		// ===> failure is normal here
		// ===> because no real human click
		Framework::loadConfig();
		 $_POST['g-recaptcha-response'] = 999;
		$this->assertFalse( Captcha::validate() );
		$this->assertPattern('/captcha failed/i', Captcha::error());
		// clean-up
		$fusebox = null;
	}


} // TestFuseboxyCaptcha