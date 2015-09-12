# Remote Deployment Trigger for [PHPCI](https://www.phptesting.org)

A plugin for PHPCI to trigger an deployment url.

To deploy an application when the build is successful tested, specify any external remote deployment url trigger the deployment process.

### Install the Plugin

1. Navigate to your PHPCI root directory and run `composer require rephlux/phpci-remote-deployment`
2. Update your `phpci.yml` in the project you want to deploy with

### Prerequisites

1. The specified deployment url needs to be accessible from PHPCI

### Plugin Options
- **url** _[string]_ - The url to the deployment script
- **method** _[string, optional, values: get, post]_ - The http method used to call the deployment url _(default: 'get')_

### PHPCI Config

```yml
\Rephlux\PHPCI\Plugin\RemoteDeployment:
    url: <url_to_deployment_script>
    method: <http_method>
```

example:

```yml
success:
    \Rephlux\PHPCI\Plugin\RemoteDeployment: 
        url: "http://deploy.mydomain.com/execute?token=123456789"

```