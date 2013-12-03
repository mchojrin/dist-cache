<?php
/**
 * @author: mchojrin (mchojrin@gmail.com)
 * Date: 12/3/13
 * Time: 2:22 PM
 */

namespace Muc\Cache;

class CacheProducer 
{
	private $oLocalStorageFactory;
	private $oRemoteStorageFactory;
	private $oLockStorageFactory;
	private $sLocalId;
	private $bEnabled;

	const GENERATION_LOCK_PREFIX = 'cache-generation-lock-prefix';

	/**
	 *
	 * @param StorageFactory $oLocalStorageFactory
	 * @param StorageFactory $oRemoteStorageFactory
	 * @param StorageFactory $oLockStorageFactory
	 * @param string $sLocalId (Producer's id for distributed mutex)
	 * @param bool $bEnabled
	 */
	public function __construct( StorageFactory $oLocalStorageFactory, StorageFactory $oRemoteStorageFactory, StorageFactory $oLockStorageFactory, $sLocalId, $bEnabled = true )
	{
		$this->setLocalStorageFactory( $oLocalStorageFactory );
		$this->setRemoteStorage( $oRemoteStorageFactory );
		$this->setLocalId($sLocalId);
		$this->setLockStorageFactory( $oLockStorageFactory );
		$this->setEnabled( $bEnabled );
	}

	/**
	 * @param bool $bEnabled
	 */
	public function setEnabled( $bEnabled )
	{
		$this->bEnabled = $bEnabled;
	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->bEnabled;
	}

	/**
	 *
	 * @param StorageFactory $oLockStorage
	 */
	public function setLockStorageFactory( StorageFactory $oLockStorageFactory )
	{
		$this->oLockStorageFactory = $oLockStorageFactory;
	}

	/**
	 *
	 * @return StorageFactory
	 */
	public function getLockStorageFactory()
	{
		return $this->oLockStorageFactory;
	}

	/**
	 *
	 * @param string $sId
	 */
	public function setLocalId( $sId )
	{
		$this->sLocalId = $sId;
	}

	/**
	 *
	 * @return string
	 */
	public function getLocalId()
	{
		return $this->sLocalId;
	}

	/**
	 *
	 * @param StorageFactory $oRemoteStorageFactory
	 */
	public function setRemoteStorage( $oRemoteStorageFactory )
	{
		$this->oRemoteStorageFactory = $oRemoteStorageFactory;
	}

	/**
	 *
	 * @return StorageFactory
	 */
	public function getRemoteStorageFactory()
	{
		return $this->oRemoteStorageFactory;
	}

	/**
	 *
	 * @param StorageFactory $oLocalStorageFactory
	 */
	public function setLocalStorageFactory( StorageFactory $oLocalStorageFactory )
	{
		$this->oLocalStorageFactory = $oLocalStorageFactory;
	}

	/**
	 *
	 * @return StorageFactory
	 */
	public function getLocalStorageFactory()
	{
		return $this->oLocalStorageFactory;
	}

	/**
	 *
	 * @param string $sKey
	 * @param int $iTtl
	 * @param Closure $fGeneration
	 * @param array $aGenerationParams
	 * @param Closure $fValidation
	 * @return mixed Contenidos del cache
	 * @throws Exception (Should the need for cache regeneration arise and the lock is not obtainable)
	 */
	public function get( $sKey, $iTtl, Closure $fGeneration, array $aGenerationParams = array(), Closure $fValidation = null )
	{
		$fValidation = $fValidation ?: function( $p ) { return true; };

		if ( $this->isEnabled() ) {
			$oLocalStorage = $this->getLocalStorageFactory()->build($sKey);
			if ( ( $oCache = $this->getFromStorage( $oLocalStorage ) ) && !$oCache->isExpired() && $fValidation( $mContents = $oCache->getContents() ) ) {

				return $mContents;
			}

			$oRemoteStorage = $this->getRemoteStorageFactory()->build($sKey);
			if ( ( $oCache = $this->getFromStorage( $oRemoteStorage ) ) && !$oCache->isExpired() && $fValidation( $mContents = $oCache->getContents() ) ) {
				$this->putInStorage( $oCache, $oLocalStorage );

				return $mContents;
			}

			if ( $this->getGenerationLock( $sKey ) ) {
				$mContents = call_user_func_array( $fGeneration, $aGenerationParams );
				$this->releaseGenerationLock( $sKey );
				$oCache = new CacheObject( $mContents, time() + $iTtl );

				$this->putInStorage( $oCache, $oLocalStorage );
				$this->putInStorage( $oCache, $oRemoteStorage );

				return $mContents;
			} else {

				throw new CouldntObtainLockException();
			}
		} else {

			return call_user_func_array( $fGeneration, $aGenerationParams );
		}
	}

	/**
	 *
	 * @param string $sKey
	 * @return boolean
	 */
	private function getGenerationLock( $sKey )
	{
		$oLockStorage = $this->getLockStorageFactory()->build( self::GENERATION_LOCK_PREFIX.$sKey );
		if ( $oLockStorage->retrieve() ) {

			return false;
		}

		$oLockStorage->store( $this->getLocalId() );

		return true;
	}

	/**
	 *
	 * @param string $sKey
	 */
	private function releaseGenerationLock( $sKey )
	{
		$this->getLockStorageFactory()->build( self::GENERATION_LOCK_PREFIX.$sKey )->flush();
	}

	/**
	 *
	 * @param Storage $oStorage
	 * @return CacheObject
	 */
	private function getFromStorage( Storage $oStorage )
	{
		if ( $aData = $oStorage->retrieve() ) {
			$aData = unserialize( $aData );

			return new CacheObject( $aData['contents'], $aData['expires'] );
		}

		return null;
	}

	/**
	 *
	 * @param CacheObject $oCache
	 * @param $oStorage
	 */
	private function putInStorage( CacheObject $oCache, Storage $oStorage )
	{
		$oStorage->store( serialize( array( 'expires' => $oCache->getExpirationDate(), 'contents' => $oCache->getContents() ) ) );
	}
}