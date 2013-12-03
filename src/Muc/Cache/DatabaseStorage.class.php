<?php

namespace Muc\Cache;

class DatabaseStorage extends Storage
{
	private $oDatabase;
	private $sTable;

	/**
	 * @param PDO $oDatabase
	 */
	public function setDatabase( PDO $oDatabase )
	{
		$this->oDatabase = $oDatabase;
	}

	/**
	 * @return PDO
	 */
	public function getDatabase()
	{
		return $this->oDatabase;
	}

	/**
	 * @return string
	 */
	public function getTable()
	{
		return $this->sTable;
	}

	/**
	 * @param string $sTable
	 */
	public function setTable( $sTable )
	{
		$this->sTable = $sTable;
	}
	
	/**
	 * 
	 * @param PDO $oDatabase Instancia de conexion al servidor que servira de backend
	 * @param string $sTable Tabla donde se obtendrá/guardará la información. Tiene que ser una tabla (clave,valor)
	 * @param string $sKey Clave que se utilizará para identificar el valor a obtener dentro del storage
	 */
	public function __construct( PDO $oDatabase, $sTable, $sKey ) 
	{
		$this->setDatabase($oDatabase);
		$this->setTable($sTable);
		$this->setKey($sKey);
	}

	/**
	 * @see Storage::retrieve 
	 */
	public function retrieve() 
	{
		$db = $this->getDatabase();
		
		$sql = 'SELECT valor FROM ' . $this->getTable() . ' WHERE clave = ' . $db->quote( $this->getKey() );
		$sth = $db->query($sql);

		return $sth->fetchColumn();
	}

	/**
	 * @see Storage::store
	 * @param string $mData 
	 */
	public function store( $mData ) 
	{
		$db = $this->getDatabase();
		
		$sql = 'REPLACE ' . $this->getTable() . ' (clave, valor) VALUES (' . $db->quote( $this->getKey() ) . ', ' . $db->quote( $mData ) . ')';
		$this->getDatabase()->exec($sql);
	}

	/**
	 * Elimina un caché
	 */
	public function flush() 
	{
		$db = $this->getDatabase();
		
		$sql = 'DELETE FROM ' . $this->getTable() . ' WHERE clave = '.$db->quote( $this->getKey() );
		$this->getDatabase()->exec($sql);
	}
}
