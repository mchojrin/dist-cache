<?php

namespace Muc\Cache;

abstract class Storage
{
	private $sKey;
	
	/**
	 * 
	 * @return string
	 */
	public function getKey()
	{
		return $this->sKey;
	}
	
	/**
	 * 
	 * @param string $sKey
	 */
	public function setKey( $sKey )
	{
		$this->sKey = $sKey;
	}

	/**
	 * @param mixed $mData Datos que deben ser almacenados 
	 */
	abstract function store( $mData );
	
	/**
	 * @return mixed Valor almacenado en el storage 
	 */
	abstract function retrieve();
	
	/**
	 * Elimina lo almacenado
	 */
	abstract function flush();
}
