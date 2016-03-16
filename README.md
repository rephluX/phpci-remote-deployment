# Remote Deployment Trigger for [PHPCI](https://www.phptesting.org)

[![Build Status](https://travis-ci.org/rephluX/phpci-remote-deployment.svg?branch=master)](https://travis-ci.org/rephluX/phpci-remote-deployment)
[![Latest Stable Version](https://poser.pugx.org/rephlux/phpci-remote-deployment/v/stable.svg)](https://packagist.org/packages/rephlux/phpci-remote-deployment)
[![License](https://poser.pugx.org/rephlux/phpci-remote-deployment/license.svg)](https://packagist.org/packages/rephlux/phpci-remote-deployment)

A plugin for PHPCI to trigger an deployment url.

To deploy an application when the build is successful tested, specify any external remote deployment url trigger the deployment process.

Each branch in the VCS can be configured separately to support different settings for each branch:

* master branch -> production settings
* development branch -> stage settings

### Install the Plugin

1. Navigate to your PHPCI root directory and run `composer require rephlux/phpci-remote-deployment`
2. Update your `phpci.yml` in the project you want to deploy with

### Prerequisites

1. The specified deployment url needs to be accessible from PHPCI

### Plugin Options
- **branch** _[array]_ The specific branch for the project
    - **url** _[string]_ - The url to the deployment script
    - **method** _[string, optional, values: get, post]_ - The http method used to call the deployment url _(default: 'get')_

### PHPCI Config

```yml
\Rephlux\PHPCI\Plugin\RemoteDeployment:
    <branch>:
        url: <url_to_deployment_script>
        method: <http_method>
```

example:

```yml
success:
    \Rephlux\PHPCI\Plugin\RemoteDeployment:
        master:
            url: "http://deploy.mydomain.com/execute?token=123456789"
        development:
            url: "http://deploy.mydomain.com/execute?token=987654321" 

```
