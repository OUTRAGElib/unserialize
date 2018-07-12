<?php


namespace OUTRAGElib\Unserialize;

use \Exception;
use \Nette\Tokenizer\Stream;
use \Nette\Tokenizer\Tokenizer;
use \RangeException;


class Splicer
{
	/**
	 *	Tokeniser
	 */
	protected $tokenizer = null;
	
	
	/**
	 *	Stores a fresh list of tokens
	 */
	protected $tokens = [];
	
	
	/**
	 *	Stores our splice operations to be performed
	 */
	public $queue = [];
	
	
	/**
	 *	Init our splicer
	 */
	public function __construct(Tokenizer $tokenizer, array $tokens)
	{
		$this->tokenizer = $tokenizer;
		$this->tokens = $tokens;
	}
	
	
	/**
	 *	Run our splicer
	 */
	public function splice()
	{
		if(!count($this->queue))
			return $this->tokens;
		
		# if we have anything that needs splicing, we shall proceed to do this splciing
		# riiight about now
		foreach($this->queue as $splice)
		{
			$first = array_search($splice->first, $this->tokens, true);
			$last = array_search($splice->last, $this->tokens, true);
			
			if($first > $last)
				throw new RangeException("Cannot access token, invalid index");
			
			# in an attempt to make things nice and easy to parse, we're going to
			# make our own stream, based on this slice, how lovely
			$slice = $splice->compile($this->tokenizer, array_slice($this->tokens, $first, ($last - $first) + 1));
			
			if($slice !== false)
				array_splice($this->tokens, $first, ($last - $first) + 1, $slice);
		}
		
		return $this->tokens;
	}
}