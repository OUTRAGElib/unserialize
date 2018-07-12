<?php


namespace OUTRAGElib\Unserialize;

use \Exception;
use \Nette\Tokenizer\Token;


abstract class SpliceAbstract
{
	/**
	 *	Set the first 
	 */
	public $first = null;
	
	
	/**
	 *	Set adjustment length
	 */
	public $last = null;
	
	
	/**
	 *	Set the confines of the adjustment
	 */
	public final function __construct(Token $first, Token $last)
	{
		$this->first = $first;
		$this->last = $last;
	}
}