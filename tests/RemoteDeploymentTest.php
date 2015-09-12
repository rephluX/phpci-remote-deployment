<?php

use Rephlux\PHPCI\Plugin;
use Rephlux\PHPCI\Plugin\RemoteDeployment;

class RemoteDeploymentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RemoteDeployment
     */
    protected $plugin = false;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $buildMock = false;

    /**
     * @var \PHPCI\Builder
     */
    protected $builderMock = false;

    /**
     * @var string
     */
    protected $url = 'http://localhost';

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->builderMock = $this->getMockBuilder('PHPCI\Builder')->getMock();
        $this->buildMock   = $this->getMockBuilder('PHPCI\Model\Build')->getMock();

        $this->builderMock
            ->method('log')
            ->willReturn(null);

        $this->builderMock
            ->method('logSuccess')
            ->willReturn(null);

        $this->builderMock
            ->method('logFailure')
            ->willReturn(null);

        $this->buildMock
            ->method('isSuccessful')
            ->willReturn(true);
    }

    /**
     * Tear down method
     * This method will destroy the instance of the plugin.
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->plugin = null;
    }

    /**
     * Ensure that the plugin can be executed.
     */
    public function testPluginCanBeExecuted()
    {
        $setup = RemoteDeployment::canExecute('success', $this->builderMock, $this->buildMock);

        $this->assertEquals(true, $setup);
    }

    /**
     * Ensure that the plugin can be instantiated with valid values.
     */
    public function testPluginCanBeInstantiated()
    {
        $options = ['url' => $this->url, 'method' => 'get'];
        $plugin  = $this->getPlugin($options);

        $this->assertInstanceOf('Rephlux\PHPCI\Plugin\RemoteDeployment', $plugin);
    }

    /**
     * Ensure that the plugin can not be instantiated with a missing url.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Please define a deployment url
     */
    public function testPluginCanNotBeInstantiatedWithMissingUrl()
    {
        $options = [];

        $this->getPlugin($options);
    }

    /**
     * Ensure that the plugin can not be instantiated with a invalid url.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Please define a valid deployment url
     */
    public function testPluginCanNotBeInstantiatedWithInvalidUrl()
    {
        $options = ['url' => '%'];

        $this->getPlugin($options);
    }

    /**
     * Ensure that the plugin can not be instantiated with a invalid method type.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Please define a valid http method
     */
    public function testPluginCanNotBeInstantiatedWithInvalidMethodType()
    {
        $options = ['url' => $this->url, 'method' => 'delete'];

        $this->getPlugin($options);
    }

    /**
     * Test the execution of the plugin with 'get' method option set.
     */
    public function testPluginExecutionWithGetRequest()
    {
        $this->executePlugin($this->url, 'get');
    }

    /**
     * Test the execution of the plugin with 'post' method option set.
     */
    public function testPluginExecutionWithPostRequest()
    {
        $this->executePlugin($this->url, 'post');
    }

    /**
     * Execute the plugin.
     *
     * @param $url
     * @param $method
     */
    protected function executePlugin($url, $method)
    {
        $curlMock = $this->getMockBuilder('Curl\Curl')
                         ->setMethods([$method])
                         ->getMock();

        $curlMock->expects($this->once())
                 ->method($method)
                 ->with($url);

        $plugin = $this->getMockBuilder('Rephlux\PHPCI\Plugin\RemoteDeployment')
                       ->setConstructorArgs([$this->builderMock, $this->buildMock, ['url' => $url, 'method' => $method]])
                       ->setMethods(['getCurl'])
                       ->getMock();

        $plugin->expects($this->once())
               ->method('getCurl')
               ->will($this->returnValue($curlMock));

        $executePlugin = $plugin->execute();

        $this->assertTrue($executePlugin);
    }

    /**
     * Get a plugin instance.
     *
     * @param array $options
     *
     * @return RemoteDeployment
     */
    protected function getPlugin(array $options = [])
    {
        $this->plugin = new RemoteDeployment($this->builderMock, $this->buildMock, $options);

        return $this->plugin;
    }
}