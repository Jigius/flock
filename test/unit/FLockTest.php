<?php
/**
 * Jigius FLock
 *
 * Simple process lock management library.
 *
 * @package   Jigius\FLock
 * @copyright Copyright (c) 2016, Jigius
 * @author    Jigius <jigius@gmail.com>
 * @license   MIT
 */

namespace Jigius\FLock;

use PHPUnit_Framework_TestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * FLock test
 *
 * Tests for the FLock class.
 */
class FLockTest extends TestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStream
     */
    protected $vfs;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->vfs = vfsStream::setup("tests");
    }

    /**
     * Test that the lock file is created
     *
     * @test
     */
    public function testHelperMethodCreate()
    {
        $lock = FLock::create("test", vfsStream::url("tests"));
        $this->assertInstanceOf('\\Jigius\\Flock\\FLock', $lock);
    }

    /**
     * Test that the lock file is created
     *
     * @test
     */
    public function testConstructorCreatesLockFile()
    {
        $lock = new FLock("test", vfsStream::url("tests"));
        $this->assertFalse($this->vfs->hasChild("test.lock"));
    }

    /**
     * Test that a fresh Lock is not in lock state
     *
     * @test
     */
    public function testNewInstanceIsNotLocked()
    {
        $lock = new FLock("test", vfsStream::url("tests"));
        $this->assertFalse($lock->check());
    }

    /**
     * Test that a single lock can only be acquired once
     *
     * @test
     */
    public function testTwoInstancesCannotAcquireSameLock()
    {
        $lock1 = new FLock("test", vfsStream::url("tests"));
        $lock2 = new FLock("test", vfsStream::url("tests"));

        $this->assertTrue($lock1->acquire());
        $this->assertFalse($lock2->acquire());
    }

    /**
     * Test that unlocking removes the lock
     *
     * @test
     */
    public function testUnlockReleasesLock()
    {
        $lock1 = new FLock("test", vfsStream::url("tests"));
        $lock2 = new FLock("test", vfsStream::url("tests"));

        $this->assertTrue($lock1->acquire());
        $this->assertTrue($lock1->check());
        $this->assertFalse($lock2->acquire());
        
        $this->assertTrue($lock1->release());
        $this->assertFalse($lock1->check());
        
        $this->assertTrue($lock2->acquire());
        $this->assertTrue($lock2->check());
    }
}
