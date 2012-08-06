<?php
/* This file is really *free* software, not like FSF ones.
*  Do what you want with this piece of code, I just enjoyed coding, don't care.
*/

/**
* Provides a way to prevent Session Hijacking attacks, using session tokens.
*
* This class was written starting from Claudio Guarnieri's {@link http://www.playhack.net Seride}. I took Claudio's code and learned how it works; then I wrote my own code tring to improve his.
* @author Francesco Ciracì <sydarex@gmail.com>
* @link http://sydarex.org
* @version 0.2
* @copyright Copyleft (c) 2009/2010 Francesco Ciracì
*/

/**
* Token class.
*
* Provides a way to prevent Session Hijacking attacks, using session tokens.
*
* This class was written starting from Claudio Guarnieri's {@link http://www.playhack.net Seride}. I took Claudio's code and learned how it works; then I wrote my own code tring to improve his.
*
* @author Francesco Ciracì <sydarex@gmail.com>
* @copyright Copyleft (c) 2009, Francesco Ciracì
*/
class Token {

	/**
	 * Stores the error code raised by Check method.
	 *
	 * The possible values are:
	 * 0 No error detected.
	 * 1 No Request Token detected.
	 * 2 No Session Token corrisponding to the Request Token.
	 * 3 No value for the Session Token.
	 * 4 Token reached the timeout.
	 * @access private
	 * @var integer
	 */
	private $error = 0;

	/**
	 * Class constructor. Sets class vars, and starts the session if it isn't started yet. Generates the new token.
	 *
	 * @param integer $timeout token timeout period, in minutes. Default 5 minutes.
	 */
	function __construct($timeout=5) {
		$this->timeout = $timeout;
		if(!isset($_SESSION)) session_start();
		$this->tokenSet();
	}

	/**
	 * Generates tokens. Generation tecnique taken from {@link http://www.playhack.net Seride}.
	 * 
	 * @access private
	 */
	public function tokenGen() {
		// Hashes a randomized UniqId.
		$hash = sha1(uniqid(mt_rand(), true));
		// Selects a random number between 1 and 32 (40-8)
		$n = mt_rand(1, 32);
		// Generate the token retrieving a part of the hash starting from the random N number with 8 of length
		$token = substr($hash, $n, 8);
		return $token;
	}

	/**
	 * Destroys token.
	 *
	 * @param string $token the token to destroy.
	 */
	function tokenDel($token) {
		unset($_SESSION[$token]);
	}

	/**
	 * Destroys all tokens except the new token.
	 */
	function tokenDelAll() {
		$sessvars = array_keys($_SESSION);
		$tokens = array();
		foreach ($sessvars as $var) if(substr_compare($var,"squerly_",0,7)==0) $tokens[]=$var;
		unset($tokens[array_search("squerly_".$this->token,$tokens)]);
		foreach ($tokens as $token) unset($_SESSION[$token]);
	}

	/**
	 * Sets token.
	 *
	 * @access private
	 */
	private function tokenSet() {
		$this->token = $this->tokenGen();
		$_SESSION["squerly_".$this->token] = time();
	}

	/**
	 * Sets a token to protect a form. In fact, it prints a hidden field with the token.
	 */
	function protectForm() {
		echo "<input type=\"hidden\" name=\"csrf_token\" value=\"".$this->token."\" />";
	}

	/**
	 * Sets a token to protect a link. In fact, it puts in the querystring the token. Returns the protected link.
	 *
	 * @param string $link the link to protect.
	 * @return string
	 */
	function protectLink($link) {
		if(strpos($link,"?")) return $link."&csrf_token=".$this->token;
		else return $link."?csrf_token=".$this->token;
	}

	/**
	 * Checks if the request have the right token set; after that, destroy the old tokens. Returns true if the request is ok, otherwise returns false.
	 */
	function Check() {
		// Check if the token has been sent.
		if(isset($_REQUEST['csrf_token'])) {
			// Check if the token exists
			if(isset($_SESSION["squerly_".$_REQUEST['csrf_token']])) {
				// Check if the token isn't empty
				if(isset($_SESSION["squerly_".$_REQUEST['csrf_token']])) {
					$age = time()-$_SESSION["squerly_".$_REQUEST['csrf_token']];
					// Check if the token did not timeout
					if($age > $this->timeout*60) $this->error = 4;
				}
				else $this->error = 3;
			}
			else $this->error = 2;
		}
		else $this->error = 1;
		// Anyway, destroys the old token.
		$this->tokenDelAll();
		if($this->error==0) return true;
		else return false;
	}

	/**
	 * Gets the error code.
	 */
	function Error() {
		return $this->error;
	}
 }
 
 