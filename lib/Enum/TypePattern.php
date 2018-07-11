<?php


namespace OUTRAGElib\Unserialize\Enum;

use \MyCLabs\Enum\Enum;
use \Nette\Tokenizer\Token;


class TypePattern extends Enum
{
	/**
	 *	Common filters
	 */
	const T_UIV = "[0-9]+";
	const T_IV = "[+-]?[0-9]+";
	const T_NV = "[+-]?(?:[0-9]*\.[0-9]+|[0-9]+\.[0-9]*)";
	const T_NVEXP = "(?:".self::T_IV."|".self::T_NV.")[eE]".self::T_IV;
	const T_ANY = "[\\000-\\377]";
	const T_OBJECT = "[OC]";
	
	
	/**
	 *	Special characters
	 */
	const C_BRACE_CLOSE = "}";
	const C_BRACE_OPEN = "{";
	const C_COLON = ":";
	const C_QUOTE = "\"";
	const C_SEMI_COLON = ";";
	
	
	/**
	 *	Specific types
	 */
	const TYPE_IMPLIED_REF = "r:(?:".self::T_UIV.");";
	const TYPE_STRONG_REF = "R:(?:".self::T_UIV.");";
	const TYPE_NULL = "N;";
	const TYPE_BOOLEAN = "b:(?:[0-1]);";
	const TYPE_INTEGER = "i:(?:".self::T_IV.");";
	const TYPE_DOUBLE_INVALID = "d:(?:NAN|-?INF);";
	const TYPE_DOUBLE = "d:(?:".self::T_IV."|".self::T_NV."|".self::T_NVEXP.");";
	const TYPE_STRING = "s:(?:".self::T_UIV."):".self::C_QUOTE;
	const TYPE_SERIALIZED = "S:(?:".self::T_UIV."):".self::C_QUOTE;
	const TYPE_ARRAY = "a:(?:".self::T_UIV."):".self::C_BRACE_OPEN;
	const TYPE_ANONYMOUS_OBJECT = "o:(?:".self::T_UIV."):".self::C_QUOTE;
	const TYPE_OBJECT = "O:(?:".self::T_UIV."):".self::C_QUOTE;
	const TYPE_OBJECT_SERIALIZABLE = "C:(?:".self::T_UIV."):".self::C_QUOTE;
	const TYPE_INVALID = self::T_ANY;
	
	
	/**
	 *	Specific types, with values returned
	 */
	const TYPE_IMPLIED_REF_VALUE = "r:(".self::T_UIV.");";
	const TYPE_STRONG_REF_VALUE = "R:(".self::T_UIV.");";
	const TYPE_NULL_VALUE = "N;";
	const TYPE_BOOLEAN_VALUE = "b:([0-1]);";
	const TYPE_INTEGER_VALUE = "i:(".self::T_IV.");";
	const TYPE_DOUBLE_INVALID_VALUE = "d:(NAN|-?INF);";
	const TYPE_DOUBLE_VALUE = "d:(".self::T_IV."|".self::T_NV."|".self::T_NVEXP.");";
	const TYPE_STRING_VALUE = "s:(".self::T_UIV."):".self::C_QUOTE;
	const TYPE_SERIALIZED_VALUE = "S:(".self::T_UIV."):".self::C_QUOTE;
	const TYPE_ARRAY_VALUE = "a:(".self::T_UIV."):".self::C_BRACE_OPEN;
	const TYPE_ANONYMOUS_OBJECT_VALUE = "o:(".self::T_UIV."):".self::C_QUOTE;
	const TYPE_OBJECT_VALUE = "O:(".self::T_UIV."):".self::C_QUOTE;
	const TYPE_OBJECT_SERIALIZABLE_VALUE = "C:(".self::T_UIV."):".self::C_QUOTE;
	const TYPE_INVALID_VALUE = self::T_ANY;
	
	
	/**
	 *	Get value, based on type in class
	 */
	public static function getTypeValue(Token $token)
	{
		$type = Type::search($token->type);
		
		if($type === false || defined("\\".self::class."::".$type."_VALUE") === false)
			return false;
		
		$matches = [];
		
		if(!preg_match("~^".constant("\\".self::class."::".$type."_VALUE")."$~", $token->value, $matches))
			return false;
		
		return $matches[1] ?? null;
	}
}