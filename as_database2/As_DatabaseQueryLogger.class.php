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
				'server'   => $db->getHostAndPort(),
				'database' => $db->getDbName(),
				'sql'      => $db->getLastQuery(),
				'time'     => $db->getLastQueryTime(),
				'num_rows' => $db->getLastQueryNumRows(),
			);
			
			if ($this->logBacktraces)
			{
				// Too much memory usage, mostly we just want files and line numbers anyway:
				// ob_start();
				// debug_print_backtrace();
				// $newLog['backtrace'] = ob_get_clean();
				$e = new Exception();
				$newLog['backtrace'] = $e->getTraceAsString();
			}
			
			$this->queryLog[] = $newLog;
		}
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
	 * @return hash in format of [sql] => array([time] => float, [num_rows] => integer, [count] => integer, [backtrace] => array of backtraces as strings)
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
				$uniqueQueryLog[$rawQuery['sql']]['num_rows'] .= ',' . $rawQuery['num_rows'];
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
				$uniqueQueryLog[$rawQuery['sql']]['num_rows'] = $rawQuery['num_rows'];
				$uniqueQueryLog[$rawQuery['sql']]['count'] = 1;
				$uniqueQueryLog[$rawQuery['sql']]['count_by_database'][$rawQuery['database']] = 1;
			}
			if (isset($rawQuery['backtrace']))
			{
				if (!isset($uniqueQueryLog[$rawQuery['sql']]['backtrace']))
				{
					$uniqueQueryLog[$rawQuery['sql']]['backtrace'] = array();
				}
				$uniqueQueryLog[$rawQuery['sql']]['backtrace'][] = $rawQuery['backtrace'];
			}
		}
		return $uniqueQueryLog;
	}
	
}

?>