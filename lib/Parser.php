<?php


namespace OUTRAGElib\Unserialize;

use \Exception;
use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Token;
use \Nette\Tokenizer\Tokenizer;
use \OUTRAGElib\Unserialize\Enum\Type as TypeEnum;
use \OUTRAGElib\Unserialize\Enum\TypePattern as TypePatternEnum;


class Parser
{
	/**
	 *	List of modes
	 */
	const MODE_FREE = 0;
	const MODE_ASSOC_START = 1;
	const MODE_ASSOC_KEY = 2;
	const MODE_ASSOC_VALUE = 3;
	
	
	/**
	 *	Tokeniser
	 */
	protected $tokenizer = null;
	
	
	/**
	 *	Stream
	 */
	protected $stream = null;
	
	
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
		var_dump($input);
		
		$this->stream = $this->tokenizer->tokenize($input);
		
		while($this->stream->nextToken())
			$this->next();
		
		unset($this->stream);
	}
	
	
	
	/**
	 *	Go onwards
	 */
	protected function next()
	{
		$datum = $this->stream->currentToken();
		
		if($this->stream->isCurrent(TypeEnum::TYPE_IMPLIED_REF))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_STRONG_REF))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_NULL))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_BOOLEAN))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_INTEGER))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_DOUBLE_INVALID))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_DOUBLE))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_STRING))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_SERIALIZED))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_ARRAY))
		{
			$count = (int) $this->getValue($this->stream->currentToken());
			
			# proceed to rattle off key/value pairs
			for($i = 0; $i < $count; ++$i)
			{
				$this->stream->nextToken() && $this->next(); # key
				$this->stream->nextToken() && $this->next(); # value
			}
			
			$this->assert($this->stream->nextToken(), TypePatternEnum::C_BRACE_CLOSE);
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT_SERIALIZABLE))
		{
			$class = "";
			$length = (int) $this->getValue($this->stream->currentToken());
			
			# get class name
			for($i = 0; $i < $length; ++$i)
				$class .= $this->stream->nextValue();
			
			# does class exist?
			if(!class_exists("\\".$class))
				throw new Exception("Class '".$class."' does not exist");
			
			$this->assert($this->stream->nextToken(), TypePatternEnum::C_QUOTE);
			$this->assert($this->stream->nextToken(), TypePatternEnum::C_COLON);
			
			$length = "";
			
			while(($char = $this->stream->nextValue()) != TypePatternEnum::C_COLON)
				$length .= $char;
			
			$this->assert($this->stream->nextToken(), TypePatternEnum::C_BRACE_OPEN);
			
			# okay, let's start the entire process all over again
			$start = $this->stream->nextToken();
			$this->next();
			$finish = $this->stream->currentToken();
			
			var_dump($start, $finish);
			exit;
			
			return true;
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_ANONYMOUS_OBJECT))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		elseif($this->stream->isCurrent(TypeEnum::TYPE_OBJECT_SERIALIZABLE))
		{
			throw new Exception("Not yet defined type: ".TypeEnum::search($datum->type));
		}
		else
		{
			throw new Exception("Unhandled type: ".TypeEnum::search($datum->type));
		}
	}
	
	
	/**
	 *	Get value
	 */
	protected function getValue(Token $token)
	{
		$type = TypeEnum::search($token->type);
		
		if($type === false || defined("\\".TypePatternEnum::class."::".$type."_VALUE") === false)
			return false;
		
		$matches = [];
		
		if(!preg_match("~^".constant("\\".TypePatternEnum::class."::".$type."_VALUE")."$~", $token->value, $matches))
			return false;
		
		return isset($matches[1]) ? $matches[1] : null;
	}
	
	
	/**
	 *	Assert something
	 */
	public function assert(Token $token, $value)
	{
		if($token->value !== $value)
			throw new Exception("Error: expected '".$value."', got '".$token->value."'");
	}
}