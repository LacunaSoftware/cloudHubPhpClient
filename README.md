Cloudhub client lib for PHP
=================================

The sample projects depend on [Cloudhub client lib for PHP](https://github.com/LacunaSoftware/cloudHubPhpClient) library, which in
turn requires **PHP 5.5** or greater.

This dependency is specified in the file `composer.json`:

	{
		"require": {
			"lacuna/cloudhub-client": "^2.0.0"
		}
	}

TLS certificate verification is enabled by default. For testing only, you can disable it by passing `true` as the third argument of `CloudhubClient` (`new CloudhubClient($baseUrl, $apiKey, true)`, parameter `dangerouslyDisableTlsVerification`). **Never use this in production**: it exposes your API key to anyone on the network path.
