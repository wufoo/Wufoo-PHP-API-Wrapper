<?php

/**
 * Entry Args
 *
 * @package default
 * @author Timothy S Sabat
 */
class EntryArgs {
	
	private $filters = array();
	private $pageStart = 0;
	private $pageSize = 25;
	private $sort = null;
	private $sortDirection = null;
	private $match = 'AND';
	
	/**
	 * Sets a filter.
	 *
	 * @param int $id the ID value of the field, as gathered from WufooApiWrapper::getFields
	 * @param string $operator a value from Filter::getWhiteList()
	 * @param string $value what you want to filter against. 
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setFilter($id, $operator, $value) {
		$filter = new Filter($id, $operator, $value);
		array_push($this->filters, $filter);
	}
	
	/**
	 * gets the query string for your entry args.
	 *
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function getArgsAsQueryString() {
		foreach ($this as $key => $value) {
			if($key == 'filters' && is_array($value)) {
				$ret.= $this->getNumeredFilters().'&';
			} else {
				if ($value) $ret.= $key.'='.$value.'&';			
			}
		}
		return rtrim($ret, '&');
	}
	
	private function getNumeredFilters() {
		$count = 1;
		foreach ($this->filters as $filter) {
			$ret.='Filter'.$count.'='.$filter->getFilterString().'&';
			$count++;
		}
		return rtrim($ret, '&');
	}
	
	/**
	 * sets the start page for your args
	 *
	 * @param int $pageStart the page you want to begin from
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setPageStart($pageStart) {
		if (!is_numeric($pageStart)) 
			throw new WufooException('Page start must be numeric.  You chose: '.$pageStart, '400'); 
		if ($pageStart < 0)
			throw new WufooException('Page start must be zero or greater: '.$pageStart, '400'); 
		$this->pageStart = floor($pageStart);
	}
	
	/**
	 * sets the page size
	 *
	 * @param int $pageSize between 1 and 100 inclusive
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setPageSize($pageSize) {
		if (!is_numeric($pageSize)) 
			throw new WufooException('Page size must be numeric.  You chose: '.$pageSize, '400'); 
		if ($pageSize < 1)
			throw new WufooException('Page size must be one or greater. You chose: '.$pageSize, '400'); 
		if ($pageSize > 100)
			throw new WufooException('Page size must be less than 100.  You chose: '.$pageSize, '400');
		$this->pageSize = floor($pageSize);
	}
	
	/**
	 * Sets the field on which you wish to filter
	 *
	 * @param string int $id the ID value of the field, as gathered from WufooApiWrapper::getFields
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setSort($id) {
		$this->sort = $sort;
	}
	
	/**
	 * Sets the sort direction for your filters
	 *
	 * @param string $direction either ASC or DESC
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setSortDirection($direction) {
		if ($direction != 'ASC' || $direction != 'DESC') 
			throw new WufooException('Sort Direction must be either ASC or DESC.  You chose: '.$direction, '400'); 
		$this->sortDirection = floor($direction);
	}
	
	/**
	 * Sets the match type
	 *
	 * @param string $match either AND or OR
	 * @return void
	 * @author Timothy S Sabat
	 */
	public function setMatch($match) {
		if (strtolower($match) !== 'and' || strtolower($match) !== 'or') {
			throw new WufooException('Invalid match.  You chose: '.$match.'. Match may only be AND or OR', '400'); 
		}
	}	
	
}

/**
 * You don't need to directly call this class.  Used to encapsulate the filter string.
 *
 * @package default
 * @author Timothy S Sabat
 */
class Filter {
	
	private $id;
	private $operator;
	private $value;
	
	public function __construct($id, $operator, $value) {
		$this->id = str_replace('Filter', '', $id);
		$this->operator = $operator;
		$this->value = $value;
	}
	
	public function getFilterString() {
		return $this->getField().'+'.$this->getOperator().'+'.$this->getValue();
	}
	
	private function getField() {
		$ret = $this->id;
		if (is_numeric($this->id)) {
			$ret = 'Field'.$this->id;
		}
		return $ret;
	}
	
	private function getOperator() {
		
		$ret = strtolower(str_replace('_', ' ', $this->operator));
		if (in_array($ret, $this->getWhiteList())) {
			$ret = str_replace(' ', '_', $ret);
			$ret = str_replace('_null', '_NULL', $ret);
		} else {
			throw new WufooException('Invalid Filter Operator: '.$this->operator, '400');
		}
		return ucfirst($ret);
	}
	
	private function getValue() {
		return urlencode($this->value);
	}
	
	private function getWhiteList() {
		return array(
			'contains', 'does not contain', 'begins with', 'ends with',
			'is less than', 'is greater than', 'is on', 'is before', 'is after',
			'is not equal to', 'is equal to', 'is not null');
	}
	
	
}
?>