<?php
namespace App\Models\Validation\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class MatchesPasswordException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'The value given is not a multiple of 30.',
		],
	];
}