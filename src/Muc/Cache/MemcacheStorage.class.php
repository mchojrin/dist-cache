<?php

namespace Muc\Cache;

class MemcacheStorage extends Storage
{
	private $oMemcache;

	/**
	 * @param Memcache $oMemcache
	 */
	public function setMemcache( Memcache $oMemcache )
	{
		$this->oMemcache = $oMemcache;
	}

	/**
	 * @return Memcache
	 */
	public function getMemcache()
	{
		return $this->oMemcache;
	}
	
	/**
	 * @param Memcache
	 * @param string
	 */
	public function __construct( Memcache $oMemcache, $sKey )
	{
		$this->setMemcache($oMemcache);
		$this->setKey($sKey);
	}

	/**
	 * @see Storage::retrieve 
	 */
	public function retrieve() 
	{
		return $this->getMemcache()->get( $this->getKey() );
	}

	/**
	 * @see Storage::store
	 * @param mixed $mData 
	 */
	public function store( $mData )
	{
		$this->getMemcache()->set( $this->getKey(), $mData );
	}
	
	public function flush()
	{
		$this->getMemcache()->delete( $this->getKey() );
	}
}
