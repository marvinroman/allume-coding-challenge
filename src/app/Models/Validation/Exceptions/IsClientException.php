<?php
namespace App\Models\Validation\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class IsClientException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'A client with this ID does not exist.',
		],
	];
}