<?php

namespace Muc\Cache;

class StorageFactory 
{
	private $oPrototype;
	
	/**
	 * 
	 * @param Storage $oPrototype
	 */
	public function __construct(Storage $oPrototype)
	{
		$this->setPrototype($oPrototype);
	}
	
	/**
	 * 
	 * @param string $sKey
	 * @return Storage
	 */
	public function build( $sKey )
	{
		$oNewStorage = clone $this->oPrototype;
		$oNewStorage->setKey($sKey);
		
		return $oNewStorage;
	}
	
	/**
	 * 
	 * @param Storage $oPrototype
	 */
	public function setPrototype( Storage $oPrototype )
	{
		$this->oPrototype = $oPrototype;
	}
	
	/**
	 * 
	 * @return Storage
	 */
	public function getPrototype()
	{
		return $this->oPrototype;
	}
}