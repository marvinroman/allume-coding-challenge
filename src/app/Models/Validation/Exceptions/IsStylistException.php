<?php
namespace App\Models\Validation\Exceptions;

use \Respect\Validation\Exceptions\ValidationException;

class IsStylistException extends ValidationException
{
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'A stylist with this ID does not exist.',
		],
	];
}