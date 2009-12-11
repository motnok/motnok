<?php

class Motnok_Doctrine_Template_Lucene extends Doctrine_Template
{
	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'indexPath' 	=> null,
		'tableFolder' 	=> true,
		'name' 			=> 'lucene_id',
		'alias'			=> null,
		'keywords'		=> array(),
		'unindexed' 	=> array(),
		'binary' 		=> array(),
		'text'			=> array(),
		'unstored' 		=> array(),
		'html'			=> null,
		'nofollow'		=> true,
		'encoding'		=> 'utf-8',
        'indexName'     => 'lucene'
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Doctrine/Doctrine_Template#setTableDefinition()
	 */
	public function setTableDefinition()
	{
		if(is_null($this->_options["indexPath"]) && !defined("MOTNOK_DOCTRINE_LUCENE_INDEX_PATH"))
			throw new Exception("indexPath option or constant 'MOTNOK_DOCTRINE_LUCENE_INDEX_PATH' is not defined");
			
		//Create the index file
		$this->_options["indexPath"] = !is_null($this->_options["indexPath"]) ? $this->_options["indexPath"] : MOTNOK_DOCTRINE_LUCENE_INDEX_PATH;
		try
		{
			$index = $this->_getIndex();
		}
		catch (Zend_Search_Lucene_Exception $e)
		{
			Zend_Search_Lucene::create($this->_getDataFolder());
		}
		
        $this->addListener(new Motnok_Doctrine_Template_Listener_Lucene($this->_options, $this->_getDataFolder()));
	}
	
	/**
	 * Get the lucene index
	 * @return Zend_Search_Lucene_Interface
	 */
	protected function _getIndex()
	{
		return Zend_Search_Lucene::open($this->_getDataFolder());
	}
	
	/**
	 * Get folder for data
	 * @return string
	 */
	protected function _getDataFolder()
	{
		$path = !is_null($this->_options["indexPath"]) ? $this->_options["indexPath"] : MOTNOK_DOCTRINE_LUCENE_INDEX_PATH;
		if($this->_options['tableFolder'])
			$path .= "/" . strtolower($this->getInvoker()->getTable()->getTableName());
		return $path;
	}
	
	/**
	 * Search lucene index using lucene query language
	 * Return an array of Doctrine_Record or of lucene hits with scores
	 * @param string|Zend_Search_Lucene_Search_Query $format
	 * @param bool $returnObject
	 * @return array
	 */
	public function luceneSearchTableProxy($search, $returnObject = true)
	{
		$return = array();
        $index = $this->_getIndex();
		$hits = $index->find($search);
		$field = $this->_options["name"];
		foreach($hits as $hit)
		{
			if($returnObject)
			{
				$object = $this->getInvoker()->getTable()->find($hit->$field);
				$return[] = $object;
				unset($object);
			}
			else
			{
				$return[] = $hit;
			}
		}
		return $return;
	}
	
	/**
	 * Get the lucene index
	 * @return Zend_Search_Lucene_Interface
	 */
	public function getLuceneIndexTableProxy()
	{
		return $this->_getIndex();
	}
	
}