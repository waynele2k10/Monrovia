<?php

/**
 * the file for the sanitizer functions.
 *
 * @link       http://registration_magic.com
 * @since      1.0.0
 *
 * @package    Registraion_Magic
 * @subpackage Registraion_Magic/includes
 */
class RM_Sanitizer
{

	/**
	 * Sanitizes the request variable
	 *
	 * @since    1.0.0
	 */
	public static function sanitize_request($req)
	{
            $request = RM_Utilities::trim_array($req);
            
            return $request;

	}



}
