<?php
class Captcha {


	private static $error;


	// get (latest) error message
	public static function error() {
		return self::$error;
	}


	// get html of captcha field
	public static function getField() {
		return "<div class='g-recaptcha' data-sitekey='{$boxy->config['reCAPTCHA']['sitekey']}' style='display: inline-block;'></div>";
	}

	// display captcha field
	public static function renderField() {
		echo self::getField();
	}


	// validate captcha against posted data
	public static function validate() {
		/*
		<fusedoc>
			<io>
				<in>
					<structure name="config" scope="$boxy">
						<structure name="reCAPTCHA">
							<string name="sitekey" />
							<string name="secret" />
							<string name="url" />
						</structure>
					</structure>
					<string name="g-recaptcha-response" scope="$_POST" comments="user submitted data" />
				</in>
				<out>
					<boolean name="~return~" />
				</out>
			</io>
		</fusedoc>
		*/
		// validate
		if ( !isset($_POST['g-recaptcha-response']) ) {
			self::$error = "Captcha not submitted";
			return false;
		}
		// login to <http://www.google.com/recaptcha/> by your google account for reCAPTCHA site key and secret key
		$secret = $boxy->config['reCAPTCHA']['secret'];
		$url    = $boxy->config['reCAPTCHA']['url'];
		$data   = "secret={$secret}&response={$_POST['g-recaptcha-response']}&remoteip={$_SERVER['REMOTE_ADDR']}";
		// validate captcha remotely
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$captcha_result = curl_exec($ch);
		if ( empty($captcha_result) ) {
			self::$error = "Error occurred while validating reCAPTCHA";
			return false;
		} else {
			$captcha_result = json_decode($captcha_result);
			curl_close($ch);
			// captcha result
			if ( empty($captcha_result->success) ) {
				self::$error = "Captcha failed (".implode(", ", $captcha_result->{'error-codes'}).")";
				return false;
			}
		}
		// success
		return true;
	}


}