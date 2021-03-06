<?php
namespace Respect\Test;

use ReflectionObject, Exception, InvalidArgumentException;
/**
 * @covers Respect\Test\StreamWrapper
 */
class StreamWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileStreamEntity local instance
     */
    protected $object;

    /**
     * Setting up the stage before our unit tests run.
     */
    protected function setUp()
    {
        StreamWrapper::setStreamOverrides(array());
        $this->object = new StreamWrapper;
    }

    /**
     * Tearing thing down once test execution is done.
     */
    protected function tearDown()
    {
        StreamWrapper::releaseOverrides();
        $this->object = null;
    }

    /**
     * @covers Respect\Test\StreamWrapper::setStreamOverrides
     */
    public function testSetStreamOverrides()
    {
        StreamWrapper::setStreamOverrides(array(
            'virtual/foo-bar-baz.ini' => "foo=bar\nbaz=bat",
            'virtual/happy-panda.ini' => "panda=happy\nhappy=panda",
        ));
        $this->assertTrue(is_dir('virtual'));
        $this->assertTrue(file_exists('virtual/happy-panda.ini'));
        $this->assertEquals("foo=bar\nbaz=bat", file_get_contents('virtual/foo-bar-baz.ini'));
    }

    /**
     * @covers Respect\Test\StreamWrapper::setStreamOverrides
     */
    public function testSetStreamOverrideResource()
    {
        StreamWrapper::setStreamOverrides(array(
            'virtual/foo-bar-baz.ini' => fopen('data:text/plain;base64,'.base64_encode("foo=bar\nbaz=bat"), 'w+b'),
            'virtual/happy-panda.ini' => fopen('data:text/plain;base64,'.base64_encode("panda=happy\nhappy=panda"), 'w+b'),
        ));
        $this->assertTrue(is_dir('virtual'));
        $this->assertTrue(file_exists('virtual/happy-panda.ini'));
        $this->assertEquals("foo=bar\nbaz=bat", file_get_contents('virtual/foo-bar-baz.ini'));
    }

    /**
     * @covers Respect\Test\StreamWrapper::interfacePrep
     */
    public function testInterfacePrep()
    {
        $sw = new StreamWrapper;
        Reflect::on($sw)->setProperty('methods', array());

        StreamWrapper::setStreamOverrides(array());
        $stat = $sw->url_stat('.', STREAM_URL_STAT_LINK);
        $this->assertEquals('-1', $stat['blocks']);
    }

    /**
     * @covers Respect\Test\StreamWrapper::__call
     * @expectedException Exception
     * @expectedExceptionMessage No method implemented for unknown
     */
    public function test__call()
    {
        $sw = new StreamWrapper;
        $sw->unknown();
    }

    /**
     * @covers Respect\Test\StreamWrapper::delegate
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid number of required arguments for method stream_open, expected 4 but found 0.
     */
    public function test__callInvalidArgs()
    {
        $sw = new StreamWrapper;
        $sw->stream_open();
    }

    /**
     * @covers Respect\Test\StreamWrapper::releaseOverrides
     */
    public function testReleaseOverrides()
    {
        StreamWrapper::releaseOverrides();
        $this->assertFalse(is_dir('virtual'));
        $this->assertFalse(file_exists('virtual/happy-panda.ini'));
    }

    /**
     * @covers Respect\Test\StreamWrapper::__call
     * @expectedException Exception
     * @expectedExceptionMessage First inject stream overrides.
     */
    public function test__callInjectionRequired()
    {
        StreamWrapper::releaseOverrides();
        $sw = new StreamWrapper;
        $sw->unknown();
    }
}
