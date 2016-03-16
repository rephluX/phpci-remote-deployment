<?php
/**
 * Remote Deployment plugin for PHPCI
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright Copyright 2015, Chris van Daele
 * @license   https://github.com/rephluX/phpci-remote-deployment/blob/master/LICENSE
 * @link      https://github.com/rephluX/phpci-remote-deployment
 */

namespace Rephlux\PHPCI\Plugin;

use PHPCI;
use Curl\Curl;
use PHPCI\Plugin;
use PHPCI\Builder;
use PHPCI\Model\Build;
use PHPCI\ZeroConfigPlugin;

/**
 * RemoteDeployment - Triggers a external deployment url after build is successful.
 *
 * @author       Chris van Daele <engine_no9@gmx.net>
 * @package      PHPCI
 * @subpackage   Plugins
 */
class RemoteDeployment implements Plugin, ZeroConfigPlugin
{
    /**
     * @var \PHPCI\Builder
     */
    protected $phpci;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $build;

    /**
     * @var string $deployUrl The deployment url.
     */
    protected $deployUrl = false;

    /**
     * @var string $requestMethod The http method to call the deploy url.
     */
    protected $requestMethod = 'GET';

    /**
     * @var string $branch The name for the selected branch
     */
    protected $branch;

    /**
     * @var array $validRequestMethods Allowed request to request the remote deploy url.
     */
    protected $validRequestMethods = ['GET', 'POST'];

    /**
     * Check if this plugin can be executed.
     *
     * @param $stage
     * @param Builder $builder
     * @param Build $build
     *
     * @return bool
     */
    public static function canExecute($stage, Builder $builder, Build $build)
    {
        if ($stage == 'success') {
            return true;
        }

        return false;
    }

    /**
     * Standard Constructor
     *
     * $options[<branch>]['url']      Trigger URL.
     * $options[<branch>]['method']   Method to call the trigger url with. Default: GET
     *
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     *
     * @throws \Exception
     */
    public function __construct(Builder $phpci, Build $build, array $options = [])
    {
        $this->phpci  = $phpci;
        $this->build  = $build;
        $this->branch = $this->build->getBranch();

        $this->deployUrl     = $this->getDeploymentUrl($options);
        $this->requestMethod = $this->getDeploymentMethod($options);
    }

    /**
     * Runs the shell command.
     */
    public function execute()
    {
        $success = false;

        $successfulBuild = $this->build->isSuccessful();

        if ($successfulBuild) {
            $success = $this->callDeploymentUrl($this->deployUrl, $this->requestMethod);
        }

        return $success;
    }

    /**
     * Return a new curl instance.
     *
     * @return Curl
     */
    public function getCurl()
    {
        return new Curl();
    }

    /**
     * Get url for remote deployment script.
     *
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    protected function getDeploymentUrl(array $options)
    {
        if (!is_array($options) || !isset($options[$this->branch])) {
            throw new \Exception('No configuration found for the ' . $this->branch . ' branch!');
        }

        if (!is_array($options[$this->branch]) || !isset($options[$this->branch]['url'])) {
            throw new \Exception('Please define a deployment url for remote deployment!');
        }

        if (filter_var($options[$this->branch]['url'], FILTER_VALIDATE_URL) === false) {
            throw new \Exception('Please define a valid deployment url for remote deployment!');
        }

        return trim($options[$this->branch]['url']);
    }

    /**
     * Get the deployment http method.
     *
     * @param array $options
     *
     * @return string
     * @throws \Exception
     */
    protected function getDeploymentMethod(array $options)
    {
        if (isset($options[$this->branch]['method'])) {
            $this->requestMethod = strtoupper($options[$this->branch]['method']);
        }

        if (!in_array($this->requestMethod, $this->validRequestMethods)) {
            throw new \Exception('Please define a valid http method to call the remote deployment url!');
        }

        return $this->requestMethod;
    }

    /**
     * Calls the deployment url.
     *
     * @param string $deployUrl
     * @param string $method
     *
     * @return bool
     */
    protected function callDeploymentUrl($deployUrl, $method = 'get')
    {
        $curl = $this->getCurl();

        $this->phpci->log(
            sprintf(
                'Calling remote deployment url %s with method (%s) on the %s branch',
                $deployUrl,
                $method,
                $this->branch
            )
        );

        if ($method == 'GET') {
            $curl->get($deployUrl);
        }

        if ($method == 'POST') {
            $curl->post($deployUrl);
        }

        if ($curl->error) {
            $this->phpci->logFailure(
                sprintf('%s request to remote deployment url %s failed.', $method, $deployUrl)
            );

            return false;
        }

        $this->phpci->logSuccess('Remote deployment request was successful.');

        return true;
    }
}
