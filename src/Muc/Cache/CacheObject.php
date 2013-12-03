<?php
/**
 * @author: mchojrin (mchojrin@gmail.com)
 * Date: 12/3/13
 * Time: 2:27 PM
 */

namespace Muc\Cache;

class CacheObject
{
	private $mContents;
	private $iExpirationDate;

	/**
	 *
	 * @param type $mContents
	 * @param type $iExpirationDate
	 */
	public function __construct( $mContents, $iExpirationDate )
	{
		$this->setContents( $mContents );
		$this->setExpirationDate( $iExpirationDate );
	}

	/**
	 *
	 * @param mixed $mContents
	 */
	public function setContents( $mContents )
	{
		$this->mContents = $mContents;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getContents()
	{
		return $this->mContents;
	}

	/**
	 *
	 * @return boolean
	 */
	public function isExpired()
	{
		return time() > $this->getExpirationDate();
	}

	/**
	 *
	 * @param int $iExpirationDate
	 */
	public function setExpirationDate( $iExpirationDate )
	{
		$this->iExpirationDate = $iExpirationDate;
	}

	/**
	 *
	 * @return int
	 */
	public function getExpirationDate()
	{
		return $this->iExpirationDate;
	}
} 