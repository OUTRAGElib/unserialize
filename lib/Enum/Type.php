<?php


namespace OUTRAGElib\Unserialize\Enum;

use \MyCLabs\Enum\Enum;
use \Nette\Tokenizer\Token;


class Type extends Enum
{
	const TYPE_IMPLIED_REF = 1;
	const TYPE_STRONG_REF = 2;
	const TYPE_NULL = 3;
	const TYPE_BOOLEAN = 4;
	const TYPE_INTEGER = 5;
	const TYPE_DOUBLE_INVALID = 6;
	const TYPE_DOUBLE = 7;
	const TYPE_STRING = 8;
	const TYPE_SERIALIZED = 9;
	const TYPE_ARRAY = 10;
	const TYPE_ANONYMOUS_OBJECT = 11;
	const TYPE_OBJECT = 12;
	const TYPE_OBJECT_SERIALIZABLE = 13;
	const TYPE_INVALID = 14;
}