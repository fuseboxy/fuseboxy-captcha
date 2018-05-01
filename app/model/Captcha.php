<?php /*
<fusedoc>
	<history version="1.1">
		- apply Util component for http-request to solve proxy issue
	</history>
	<history version="1.0">
		- first commit
	</history>
</fusedoc>
*/
class Captcha {


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			get html of client-api javascript tag
			===> display this snippet before the closing </head> tag on your HTML template
		</description>
	</fusedoc>
	*/
	public static function getClientAPI() {
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
					<structure name="reCAPTCHA" optional="yes">
						<string name="sitekey" />
					</structure>
				</structure>
			</in>
			<out />
		</io>
	</fusedoc>
	*/
	public static function getField() {
		$captcha = F::config('reCAPTCHA');
		// validate
		if ( empty($captcha) ) {
			self::$error = 'Captcha config was not defined';
			return false;
		} elseif ( empty($captcha['sitekey']) ) {
			self::$error = 'Captcha [sitekey] was not defined';
			return false;
		}
		// done!
		return "<div class='g-recaptcha' data-sitekey='{$captcha['sitekey']}' style='display: inline-block;'></div>";
	}




	/**
	<fusedoc>
		<description>
			display client-api javascript tag
		</description>
	</fusedoc>
	*/
	public static function renderClientAPI() {
		$html = self::getClientAPI();
		echo ( $html === false ) ? self::error() : $html;
	}




	/**
	<fusedoc>
		<description>
			display captcha field
		</description>
	</fusedoc>
	*/
	public static function renderField() {
		$html = self::getField();
		echo ( $html === false ) ? self::error() : $html;
	}




	/**
	<fusedoc>
		<description>
			validate captcha against posted data
		</description>
		<io>
			<in>
				<structure name="config" scope="$fusebox">
					<structure name="reCAPTCHA">
						<string name="sitekey" />
						<string name="secret" />
						<string name="verify" />
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
		$captcha = F::config('reCAPTCHA');
		// validate
		if ( empty($captcha) ) {
			self::$error = 'Captcha config was not defined';
			return false;
		} elseif ( empty($captcha['secret']) ) {
			self::$error = 'Captcha [secret] was not defined';
			return false;
		} elseif ( empty($captcha['verify']) ) {
			self::$error = 'Captcha [verify] was not defined';
			return false;
		} elseif ( !isset($_POST['g-recaptcha-response']) ) {
			self::$error = "Captcha not submitted";
			return false;
		}
		// validate captcha remotely
		// ===> login to <http://www.google.com/recaptcha> by your google account for reCAPTCHA site key and secret key
		$captchaResult = Util::postPage($captcha['verify'], array(
			'secret'   => $captcha['secret'],
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


} // Captcha