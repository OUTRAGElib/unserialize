<?php


namespace OUTRAGElib\Unserialize;

use \LengthException;
use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Token;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum;
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum;
use \OUTRAGElib\Unserialize\Splice\Serializable as SerializableSplice;
use \RuntimeException;
use \Serializable;


class Parser
{
	/**
	 *	Tokeniser
	 */
	protected $tokenizer = null;
	
	
	/**
	 *	Stream
	 */
	protected $stream = null;
	
	
	/**
	 *	Splicer
	 */
	protected $splicer = null;
	
	
	/**
	 *	Construct the parser
	 */
	public function __construct()
	{
		$this->tokenizer = new Tokenizer([
			TypeEnum::TYPE_IMPLIED_REF => TypePatternEnum::TYPE_IMPLIED_REF,
			TypeEnum::TYPE_STRONG_REF => TypePatternEnum::TYPE_STRONG_REF,
			TypeEnum::TYPE_NULL => TypePatternEnum::TYPE_NULL,
			TypeEnum::TYPE_BOOLEAN => TypePatternEnum::TYPE_BOOLEAN,
			TypeEnum::TYPE_INTEGER => TypePatternEnum::TYPE_INTEGER,
			TypeEnum::TYPE_DOUBLE_INVALID => TypePatternEnum::TYPE_DOUBLE_INVALID,
			TypeEnum::TYPE_DOUBLE => TypePatternEnum::TYPE_DOUBLE,
			TypeEnum::TYPE_STRING => TypePatternEnum::TYPE_STRING,
			TypeEnum::TYPE_SERIALIZED => TypePatternEnum::TYPE_SERIALIZED,
			TypeEnum::TYPE_ARRAY => TypePatternEnum::TYPE_ARRAY,
			TypeEnum::TYPE_ANONYMOUS_OBJECT => TypePatternEnum::TYPE_ANONYMOUS_OBJECT,
			TypeEnum::TYPE_OBJECT => TypePatternEnum::TYPE_OBJECT,
			TypeEnum::TYPE_OBJECT_SERIALIZABLE => TypePatternEnum::TYPE_OBJECT_SERIALIZABLE,
			TypeEnum::TYPE_INVALID => TypePatternEnum::TYPE_INVALID,
		]);
	}
	
	
	/**
	 *	Parse things
	 */
	public function parse($input)
	{
		# do our stream operations
		$this->stream = $this->tokenizer->tokenize($input);
		$this->splicer = new Splicer($this->tokenizer, $this->stream->tokens);
		
		while($this->stream->nextToken())
			$this->next();
		
		# at this point we need to do some fancy things with something that I'm going to call
		# the token splicer. what this wonderful contraption allows us to do is modify the stream
		# in a certain manner, thus reducing the possibility of object corruption, hopefully!
		$output = "";
		$tokens = $this->splicer->splice();
		
		foreach($tokens as $token)
			$output .= $token->value;
		
		unset($this->stream);
		unset($this->splicer);
		
		gc_collect_cycles();
		
		return $output;
	}
	
	
	
	/**
	 *	Go onwards
	 */
	protected function next()
	{
		$datum = $this->stream->currentToken();
		
		if($this->stream->isCurrent(TypeEnum::TYPE_IMPLIED_REF))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_STRONG_REF))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_NULL))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_BOOLEAN))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_INTEGER))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_DOUBLE_INVALID))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_DOUBLE))
		{
			# simple type, no further action required
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_STRING))
		{
			# just progressing forward is needed
			$length = (int) TypePatternEnum::getTypeValue($datum);
			
			$offset = -1;
			$prev = $datum;
			
			while($token = $this->stream->nextToken())
			{
				$offset += ($token->offset - $prev->offset);
				
				if($offset > $length)
					break;
				
				$prev = $token;
			}
			
			$this->stream->consumeValue(TypePatternEnum::C_QUOTE);
			$this->stream->consumeValue(TypePatternEnum::C_SEMI_COLON);
			
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_SERIALIZED))
		{
			throw new RuntimeException("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_ARRAY))
		{
			# arrays are literally just a way to start the cycle of life all over again
			# in an interesting way
			$count = (int) TypePatternEnum::getTypeValue($datum);
			
			# proceed to rattle off key/value pairs
			for($i = 0; $i < $count; ++$i)
			{
				$this->stream->nextToken() && $this->next(); # key
				$this->stream->nextToken() && $this->next(); # value
			}
			
			$this->stream->consumeValue(TypePatternEnum::C_BRACE_CLOSE);
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT_SERIALIZABLE))
		{
			$class = $this->stream->joinUntil(TypePatternEnum::C_QUOTE);
			
			# progress onwards
			$this->stream->consumeValue(TypePatternEnum::C_QUOTE);
			$this->stream->consumeValue(TypePatternEnum::C_COLON);
			
			$length = (int) $this->stream->joinUntil(TypePatternEnum::C_BRACE_OPEN);
			
			$this->stream->consumeValue(TypePatternEnum::C_BRACE_OPEN);
			
			# parse contents of object
			$start = $this->stream->nextToken();
			
			$this->next();
			
			$finish = $this->stream->nextToken();
			
			# due to past experiences of serialize messing about and reporting the wrong length
			# for serialized fields this is something that one will definitely check to see
			if(($finish->offset - $start->offset) !== $length)
				throw new LengthException("Start/finish offsets do not match expected length");
			
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_ANONYMOUS_OBJECT))
		{
			throw new RuntimeException("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT))
		{
			$class = $this->stream->joinUntil(TypePatternEnum::C_QUOTE);
			
			# progress onwards
			$this->stream->consumeValue(TypePatternEnum::C_QUOTE);
			$this->stream->consumeValue(TypePatternEnum::C_COLON);
			
			$count = (int) $this->stream->joinUntil(TypePatternEnum::C_BRACE_OPEN);
			
			$this->stream->consumeValue(TypePatternEnum::C_BRACE_OPEN);
			
			# proceed to rattle off key/value pairs
			for($i = 0; $i < $count; ++$i)
			{
				$this->stream->nextToken() && $this->next(); # key
				$this->stream->nextToken() && $this->next(); # value
			}
			
			$this->stream->consumeValue(TypePatternEnum::C_BRACE_CLOSE);
			
			# if we have reached this point, then one can presume that we have reached the jackpot
			# when it comes to unserialisation - we can mock __wakeup functionality on objects
			# because everything is stored in a format that is very similar to an array - all we
			# need to do is just log the change and the library will format this later on
			if(is_subclass_of("\\".$class, Serializable::class))
				$this->splicer->queue[] = new SerializableSplice($datum, $this->stream->currentToken());
			
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT_SERIALIZABLE))
		{
			throw new RuntimeException("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		else
		{
			throw new RuntimeException("Unhandled type: ".TypeEnum::search($datum->type));
		}
	}
}