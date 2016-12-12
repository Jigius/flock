<?php
/**
 * Jigius
 *
 * Simple process lock management library.
 *
 * @package   Jigius\FLock
 * @copyright Copyright (c) 2016, Jigius
 * @author    Jigius <jigius@gmail.com>
 * @license   MIT
 */

namespace Jigius\FLock;

Class FLock
{
    /**
     * Represents the locked state
     */
    const UNLOCKED = 0;

    /**
     * Represents the unlocked state
     */
    const LOCKED = 1;

     /**
     * Lock file pointer
     *
     * @var resource
     */
    protected $lock;

    /**
     * Lock name
     *
     * @var string
     */
    protected $filename;

    /**
     * Lock status
     *
     * @var Lock::UNLOCKED|Lock::LOCKED
     */
    protected $status;

    /**
     * Lock constructor
     *
     * Creates a Lock instance for a specified lock name.
     *
     * @param  string  $name   Name of the lock.
     * @param  string  $path Filesystem path where lock files should be
     *   stored. If NULL, will be set to the system's default temporary
     *   directory.
     * @return void
     * @throws \RuntimeException Throws a \RuntimeException if the lock file
     *   cannot be opened or created.
     */
    public function __construct($name, $path=null)
    {
        if ($path === null) {
            $path = sys_get_temp_dir();
        }
        $this->filename = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::cleanName($name) . ".lck";
        $this->status = self::UNLOCKED;
    }

    /**
     * Lock helper method
     *
     * Creates a Lock instance for a specified lock name.
     *
     * @param  string  $name   Name of the lock.
     * @param  string  $path Filesystem path where lock files should be
     *   stored. If NULL, will be set to the system's default temporary
     *   directory.
     * @return object LockFile
     */
    public static function create($name, $path=null)
    {
        $lock = new self($name, $path);
        return $lock;
    }

    /**
     * Lock destructor
     *
     * Cleans up before the instance is destroyed.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->release();
    }

    /**
     * Acquire a new lock
     *
     * @param  boolean $block If TRUE, the method call will block until any
     *   existing locks are released. Defaults to FALSE, meaning the call will
     *   return immediately.
     * @return boolean Will return TRUE on success, FALSE otherwise.
     */
    public function acquire($block = false)
    {
        if ($this->status == self::LOCKED) {
            return true;
        }

        $lock = @fopen($this->filename, "c");
        if (!$lock) {
            throw new \RuntimeException("Unable to open/create lock file: {$this->filename}");
        }
        $this->lock = $lock;

        $flags = LOCK_EX;
        if (!$block) {
            $flags |= LOCK_NB;
        }

        if (($status = flock($this->lock, $flags))) {
            $this->status = self::LOCKED;
            ftruncate($this->lock, 0);
            fwrite($this->lock, getmypid());
        }
        return $status;
    }

    /**
     * Release the current lock
     *
     * @return boolean Will return TRUE on success, FALSE otherwise.
     */
    public function release() {
        if ($this->status == self::UNLOCKED) {
            return false;
        }
        ftruncate($this->lock, 0);
        if (($status = flock($this->lock, LOCK_UN))) {
            fclose($this->lock);
            @unlink($this->filename);
            $this->status = self::UNLOCKED;
        }
        return $status;
    }

     /**
     * Check the current lock status
     *
     * @return boolean Will return TRUE if a lock is in place, FALSE otherwise.
     */
    public function check() {
        return $this->status === self::LOCKED;
    }

    /**
     * Read the process ID which holds the lock
     *
     * @return string|false Will return the pid as a string or FALSE on failure.
     */
    public function getOwnerPid() {
        $pid = @file_get_contents($this->filename);
        if (empty($pid)) {
            return false;
        }
        return $pid;
    }

    /**
     * Parse a filesystem-safe file name from the lock name
     *
     * @static
     * @param  string  $name
     * @return string
     */
    protected static function cleanName($name)
    {
        $name = preg_replace("/[^0-9a-z]/i", "", $name);
        return $name;
    }
}
