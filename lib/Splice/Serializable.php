<?php


namespace OUTRAGElib\Unserialize\Splice;

use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum; 
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum; 
use \OUTRAGElib\Unserialize\SpliceAbstract;


class Serializable extends SpliceAbstract
{
	/**
	 *	Perform our slicing
	 */
	public function compile(Tokenizer $tokenizer, array $tokens)
	{
		$stream = new Stream($tokens);
		$stream->consumeToken(TypeEnum::TYPE_OBJECT);
		
		# we will need the class name, obviously(!)
		$class = $stream->joinUntil(TypePatternEnum::C_QUOTE);
		
		# progress lazily, not particularly caring for validation of the number of
		# items in the __wakeup
		$stream->consumeValue(TypePatternEnum::C_QUOTE);
		$stream->consumeValue(TypePatternEnum::C_COLON);
		
		# we will need this for some reason below
		$count = (int) $stream->joinUntil(TypePatternEnum::C_COLON);
		
		$stream->consumeValue(TypePatternEnum::C_COLON);
		$stream->consumeValue(TypePatternEnum::C_BRACE_OPEN);
		
		# at this point we do not need to worry about the contents of this too much
		# as we can safely presume this is *not* to be worried about
		# something to bear in mind chums is that the first token of the payload has to be
		# converted from T_ANY to T_ARRAY
		$payload = array_slice($stream->tokens, $stream->position);
		
		$payload[0]->type = TypeEnum::TYPE_ARRAY;
		$payload[0]->value = sprintf('a:%d:{', $count);
		
		$payload_length = 0;
		
		foreach($payload as $p)
			$payload_length += strlen($p->value);
		
		# to make things easier let's just go ahead and create an example of a
		# header/footer stream for this thing, can't be that bad of an idea
		$header = $tokenizer->tokenize(sprintf('C:%d:"%s":%d:{', strlen($class), $class, $payload_length));
		
		# the footer...
		$footer = $tokenizer->tokenize("}");
		
		return array_merge($header->tokens, $payload, $footer->tokens);
	}
}