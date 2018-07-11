<?php


namespace OUTRAGElib\Unserialize\Adjustment;

use \Exception;
use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Token;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum;
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum;


class Serializable extends AdjustmentAbstract
{
	/**
	 *	Perform the transformation
	 */
	public function transform(Tokenizer $tokenizer, $segment)
	{
		$stream = $tokenizer->tokenize($segment);
		$stream->consumeToken(TypeEnum::TYPE_OBJECT);
		
		$class = $stream->joinUntil(TypePatternEnum::C_QUOTE);
		
		# progress lazily, not particularly caring for validation of the number of
		# items in the __wakeup
		$stream->consumeValue(TypePatternEnum::C_QUOTE);
		$stream->consumeValue(TypePatternEnum::C_COLON);
		
		# we will need this as 
		$count = (int) $stream->joinUntil(TypePatternEnum::C_COLON);
		
		$stream->consumeValue(TypePatternEnum::C_COLON);
		$stream->consumeValue(TypePatternEnum::C_BRACE_OPEN);
		
		# at this point we can literally just dump the content of the token, until we get
		# to the very last token
		$content = $stream->currentValue().$stream->joinAll();
		
		if(!$stream->isCurrent(TypePatternEnum::C_BRACE_CLOSE))
			throw new Exception("Invalid ending character");
		
		$inner = sprintf('a:%d:%s', $count, $content);
		$outer = sprintf('C:%d:"%s":%d:{%s}', strlen($class), $class, strlen($inner), $inner);
		
		return $outer;
	}
}