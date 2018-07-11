<?php


namespace OUTRAGElib\Unserialize\Adjustment;


abstract class Adjustment
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
}