<?php
/*
 * Copyright (c) 2014-2016 Nicolas Braquart
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Ngames\Framework;

class Logger
{

    const LEVEL_DEBUG = 0;

    const LEVEL_INFO = 1;

    const LEVEL_WARNING = 3;

    const LEVEL_ERROR = 4;

    private static $destination = null;

    private static $minLevel = null;

    private static $file = null;

    /**
     * Initializes the logger
     *
     * @param string $destination
     *            where to write the logs. Any path that can be written to is valid (including php://stdout).
     * @param string $minLevel
     *            the minium level for the logs to be taken into account
     */
    public static function initialize($destination, $minLevel)
    {
        self::$minLevel = $minLevel;
        self::setDestination($destination);
    }

    /**
     * Sets the destination and tries to open it in append mode
     *
     * @param string $destination            
     * @throws \Exception
     */
    public static function setDestination($destination)
    {
        self::$destination = $destination;
        
        if (!self::$file = fopen(self::$destination, 'a')) {
            throw new \Exception('Cannot open log file for writing');
        }
    }

    /**
     * Logs a debug message
     *
     * @param string $message            
     */
    public static function logDebug($message)
    {
        self::log(self::LEVEL_DEBUG, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    /**
     * Logs an error message
     *
     * @param string $message            
     */
    public static function logError($message)
    {
        self::log(self::LEVEL_ERROR, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    /**
     * Logs a warning message
     *
     * @param string $message            
     */
    public static function logWarning($message)
    {
        self::log(self::LEVEL_WARNING, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    /**
     * Logs an info message
     *
     * @param string $message            
     */
    public static function logInfo($message)
    {
        self::log(self::LEVEL_INFO, $message, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]);
    }

    /**
     * Logs a message
     *
     * @param int $level            
     * @param string $message            
     * @param array $trace            
     *
     */
    private static function log($level, $message, $trace = null)
    {
        if (self::$destination != null && self::$minLevel <= $level) {
            if ($trace == null) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            }
            $logLine = self::assembleLogLine($level, $message, $trace);
            \Ngames\Framework\Utility\FileSystem::fwriteStream(self::$file, $logLine);
        }
    }

    /**
     * Assemble the log line that will be written to the log file
     *
     * @param int $level            
     * @param string $message            
     * @param array $trace            
     */
    protected static function assembleLogLine($level, $message, $trace)
    {
        $levelString = null;
        
        switch ($level) {
            case self::LEVEL_DEBUG:
                $levelString = 'DEBUG';
                break;
            case self::LEVEL_INFO:
                $levelString = 'INFO ';
                break;
            case self::LEVEL_WARNING:
                $levelString = 'WARN ';
                break;
            case self::LEVEL_ERROR:
                $levelString = 'ERROR';
                break;
        }
        
        $time = microtime(true);
        $dateTimeString = date('Y-m-d H:i:s');
        $dateTimeString .= ',' . sprintf('%06d', ($time - floor($time)) * 1000000);
        
        $lineNumber = $trace['line'];
        $fileName = str_replace(ROOT_DIR, '', $trace['file']);
        
        return $dateTimeString . ' [' . $levelString . '] ' . $fileName . ':' . $lineNumber . ' - ' . $message . "\n";
    }
}
