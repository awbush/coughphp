<?php

class As_DatabaseQueryLogger
{
	protected $queryLog = array();
	
	/**
	 * Whether or not to log backtraces when logging queries
	 *
	 * @var string
	 **/
	protected $logBacktraces = false;
	
	public function notify($eventType, $db)
	{
		if ($eventType == 'query')
		{
			$newLog = array(
				'database' => $db->getDbName(),
				'sql'      => $db->getLastQuery(),
				'params'   => $db->getLastParams(),
				'types'    => $db->getLastTypes(),
				'time'     => $db->getLastQueryTime(),
				
			);
			
			if ($this->logBacktraces)
			{
			
				$newLog['backtrace'] = $this->getBacktrace();
			}
			
			$this->queryLog[] = $newLog;
		}
	}
	
	protected function getBacktrace()
	{
		// @todo improve option this with other tracing options, e.g.
		// (string)new Exception()
		// debug_backtrace(false); // since 5.2.5
		// debug_backtrace(); // could go through and remove any 'object' elements ourselves
		ob_start();
		debug_print_backtrace();
		$backtrace = ob_get_clean();
		return $backtrace;
	}
	
	public function setLogBacktraces($logBacktraces)
	{
		$this->logBacktraces = $logBacktraces;
	}
	
	public function getQueryLog()
	{
		return $this->queryLog;
	}
	
	public function clearQueryLog()
	{
		$this->queryLog = array();
	}
	
	public function getQueryLogTime()
	{
		$time = 0.0;
		foreach ($this->queryLog as $query)
		{
			$time += $query['time'];
		}
		return $time;
	}
	
	/**
	 * Like the query log, but it rolls up all duplicate queries into only
	 * one entry in the array. It adds a count value equal to the number of
	 * times the query was run, and the time value is equal to the total
	 * time of each query run.
	 *
	 * @return hash in format of [sql] => array([time] => float, [count] => integer)
	 * @author Anthony Bush
	 * @since 2007-09-07
	 **/
	public function getUniqueQueryLog()
	{
		$uniqueQueryLog = array();
		$rawQueryLog = $this->getQueryLog();
		foreach ($rawQueryLog as $rawQuery)
		{
			if (isset($uniqueQueryLog[$rawQuery['sql']]))
			{
				$uniqueQueryLog[$rawQuery['sql']]['time'] += $rawQuery['time'];
				$uniqueQueryLog[$rawQuery['sql']]['count']++;
				if (!isset($uniqueQueryLog[$rawQuery['sql']]['count_by_database'][$rawQuery['database']]))
				{
					$uniqueQueryLog[$rawQuery['sql']]['count_by_database'][$rawQuery['database']] = 1;
				}
				else
				{
					$uniqueQueryLog[$rawQuery['sql']]['count_by_database'][$rawQuery['database']]++;
				}
			}
			else
			{
				$uniqueQueryLog[$rawQuery['sql']] = array();
				$uniqueQueryLog[$rawQuery['sql']]['time'] = $rawQuery['time'];
				$uniqueQueryLog[$rawQuery['sql']]['count'] = 1;
				$uniqueQueryLog[$rawQuery['sql']]['count_by_database'][$rawQuery['database']] = 1;
			}
		}
		return $uniqueQueryLog;
	}
	
}

?>