<?php


namespace OUTRAGElib\Unserialize\Splice;

use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum; 
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum; 
use \OUTRAGElib\Unserialize\SpliceAbstract;


class MagicWakeup extends SpliceAbstract
{
	/**
	 *	Perform our slicing
	 */
	public function compile(Tokenizer $tokenizer, array $tokens)
	{
		$stream = new Stream($tokens);
		$stream->consumeToken(TypeEnum::TYPE_OBJECT_SERIALIZABLE);
		
		# we will need the class name, obviously(!)
		$class = $stream->joinUntil(TypePatternEnum::C_QUOTE);
		
		$stream->consumeValue(TypePatternEnum::C_QUOTE);
		$stream->consumeValue(TypePatternEnum::C_COLON);
		
		$length = (int) $stream->joinUntil(TypePatternEnum::C_COLON);
		
		$stream->consumeValue(TypePatternEnum::C_COLON);
		$stream->consumeValue(TypePatternEnum::C_BRACE_OPEN);
		$stream->nextToken();
		
		if($stream->isCurrent(TypeEnum::TYPE_ARRAY))
		{
			$stack = [ clone $stream->currentToken() ];
			
			$prev = null;
			$offset = 0;
			
			while($token = $stream->nextToken())
			{
				$offset += strlen($token->value);
				
				if($offset >= $length)
					break;
				
				$stack[] = $prev = $token;
			}
			
			$count = (int) TypePatternEnum::getTypeValue($stack[0]);
			
			$stack[0]->type = TypeEnum::TYPE_OBJECT;
			$stack[0]->value = sprintf('O:%d:"%s":%d:{', strlen($class), $class, $count);
			
			return $stack;
		}
		
		return false;
	}
}