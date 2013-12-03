<?php

namespace Muc\Cache;

class FileSystemStorage extends Storage
{
	private $sFileNamePrefix;
	private $sFileNameSuffix;

	/**
	 * 
	 * @param string $sFileName
	 */
	public function __construct( $sFileNamePrefix, $sFileNameSuffix, $sKey )
	{
		$this->setKey($sKey);
		$this->setFileNamePrefix( $sFileNamePrefix );
		$this->setFileNameSuffix($sFileNameSuffix);
	}
	
	/**
	 * 
	 * @param string $sFileNamePrefix
	 */
	public function setFileNamePrefix( $sFileNamePrefix )
	{
		$this->sFileNamePrefix = $sFileNamePrefix;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getFileNamePrefix()
	{
		return $this->sFileNamePrefix;
	}

	/**
	 * 
	 * @return string
	 */
	public function getFileNameSuffix()
	{
		return $this->sFileNameSuffix;
	}

	/**
	 * 
	 * @param string $sFileNameSuffix
	 */
	public function setFileNameSuffix( $sFileNameSuffix )
	{
		$this->sFileNameSuffix = $sFileNameSuffix;
	}

	/**
	 * @return mixed
	 */
	public function retrieve()
	{
		$sFileName = $this->buildFileFullPath();
		if ( is_readable( $sFileName ) ) {
			return file_get_contents( $sFileName );
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param mixed $mData
	 */
	public function store($mData)
	{
		$sFileName = $this->buildFileFullPath();
		if ( !file_exists( $sFileName ) || is_writable( $sFileName ) ) {
			$sBaseName = basename($sFileName);

			$sDir1 = $this->getFileNamePrefix().DIRECTORY_SEPARATOR.substr($sBaseName, 0, 1);
			$sDir2 = $this->getFileNamePrefix().DIRECTORY_SEPARATOR.substr($sBaseName, 0, 1).DIRECTORY_SEPARATOR.substr($sBaseName, 0, 2);

			if ( !file_exists($sDir1) ) {
				mkdir($sDir1);
				mkdir($sDir2);
			}

			if ( !file_exists($sDir2) ) {
				mkdir($sDir2);
			}

			file_put_contents( $sFileName, $mData );
		} else {
			
			throw new Exception( 'No se puede escribir en el archivo '.$sFileName );
		}
	}

	public function flush() 
	{
		$sFileName = $this->buildFileFullPath();
		if ( file_exists($sFileName) && is_writable($sFileName) ) {
			unlink( $sFileName );
		} else {
			
			throw new Exception( 'No se puede escribir en el archivo '.$sFileName );
		}
	}

	/**
	 * @return string
	 */
	private function buildFileFullPath()
	{
		$sPath = substr($this->getKey(), 0, 1).DIRECTORY_SEPARATOR.substr($this->getKey(), 0, 2).DIRECTORY_SEPARATOR.$this->getKey();

		return $this->getFileNamePrefix().DIRECTORY_SEPARATOR.$sPath.$this->getFileNameSuffix();
	}
}