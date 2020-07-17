<?php /*
<fusedoc>
	<description>
		reCAPTCHA v2 - Checkbox
		reCAPTCHA v2 - Invisible
	</description>
	<io>
		<in>
			<structure name="captcha" scope="fusebox-config">
				<string name="siteKey" comments="site key provided by google" />
				<string name="secretKey" comments="secret key provided by google" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
class Captcha {




	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	// alias methods (for backward compatibility)
	public static function getField() { return self::field(); }
	public static function renderField() { echo self::field(); }
	public static function getClientAPI() { return self::api(); }
	public static function renderClientAPI() { echo self::api(); }




	/**
	<fusedoc>
		<description>
			get html of client-api javascript tag
			===> display this snippet before the closing </head> tag on your HTML template
		</description>
	</fusedoc>
	*/
	public static function api() {
		return "<script src='https://www.google.com/recaptcha/api.js'></script>";
	}




	/**
	<fusedoc>
		<description>
			get html of captcha field
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<structure name="captcha" optional="yes">
						<string name="siteKey" />
					</structure>
				</structure>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function field() {
		$captcha = F::config('captcha');
		// validate
		if ( empty($captcha) ) {
			return '[Error] Captcha config was not defined';
		} elseif ( empty($captcha['siteKey']) ) {
			return '[Error] Captcha [siteKey] was not defined';
		}
		// specify unique identifier for widget
		$widgetID = 'g-recaptcha-'.( function_exists('random_bytes') ? bin2hex(random_bytes(16)) : uniqid() );
		// done!
		return "<div id='{$widgetID}' class='g-recaptcha' data-sitekey='{$captcha['siteKey']}' style='display: inline-block;'></div>";
	}




	/**
	<fusedoc>
		<description>
			validate captcha against posted data
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<structure name="captcha">
						<string name="siteKey" />
						<string name="secretKey" />
					</structure>
					<string name="httpProxy" optional="yes" />
					<string name="httpsProxy" optional="yes" />
				</structure>
				<string name="g-recaptcha-response" scope="$_POST" comments="user submitted data" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function validate() {
		$captcha = F::config('captcha');
		// validate
		if ( empty($captcha) ) {
			self::$error = 'Captcha config was not defined';
			return false;
		} elseif ( empty($captcha['secretKey']) ) {
			self::$error = 'Captcha [secretKey] was not defined';
			return false;
		} elseif ( !isset($_POST['g-recaptcha-response']) ) {
			self::$error = "Captcha not submitted";
			return false;
		}
		// validate captcha remotely
		// ===> login to <http://www.google.com/recaptcha> by your google account
		// ===> get site key and secret key
		$verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
		$captchaResult = Util::postPage($verifyURL, array(
			'secret'   => $captcha['secretKey'],
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		));
		// check response
		if ( $captchaResult === false ) {
			self::$error = Util::error();
			return false;
		// parse captcha result
		} else {
			$captchaResult = json_decode($captchaResult);
			if ( empty($captchaResult->success) ) {
				self::$error = "Captcha failed (".implode(", ", $captchaResult->{'error-codes'}).")";
				return false;
			}
		}
		// success
		return true;
	}


} // class