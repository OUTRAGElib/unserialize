<?php


namespace OUTRAGElib\Unserialize\Adjustment;

use \Exception;
use \Nette\Tokenizer\Tokenizer;


abstract class AdjustmentAbstract
{
	/**
	 *	Set adjustment begin point
	 */
	public $offset = 0;
	
	
	/**
	 *	Set adjustment length
	 */
	public $length = 0;
	
	
	/**
	 *	Set the confines of the adjustment
	 */
	public final function __construct($offset, $length)
	{
		$this->offset = $offset;
		$this->length = $length;
	}
	
	
	/**
	 *	Perform the transformation
	 */
	abstract public function transform(Tokenizer $tokenizer, $segment);
}