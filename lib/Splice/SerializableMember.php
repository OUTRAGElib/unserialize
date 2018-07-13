<?php


namespace OUTRAGElib\Unserialize\Splice;

use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum; 
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum; 
use \OUTRAGElib\Unserialize\SpliceAbstract;


class SerializableMember extends SpliceAbstract
{
	/**
	 *	Perform our slicing
	 */
	public function compile(Tokenizer $tokenizer, array $tokens)
	{
		# deal with header
		$header = clone array_shift($tokens);
		$matches = [];
		
		$header->value = preg_replace_callback("/^".TypePatternEnum::TYPE_STRING_VALUE."$/", function($matches)
		{
			return sprintf('s:%d:"', ((int) $matches[1]) - 3);
		}, $header->value);
		
		# remove \0*\0
		array_shift($tokens);
		array_shift($tokens);
		array_shift($tokens);
		
		# replace header
		array_unshift($tokens, $header);
		
		return $tokens;
	}
}