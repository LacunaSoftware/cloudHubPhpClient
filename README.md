Cloudhub client lib for PHP
=================================

The sample projects depend on [Cloudhub client lib for PHP](https://github.com/LacunaSoftware/cloudHubPhpClient) library, which in
turn requires **PHP ^7.2.5** or or **^8.0"**.

This dependency is specified in the file `composer.json`:

	{
		"require": {
			"lacuna/cloudhub-client": "^1.0.8-guzzle-7"
		}
	}

## Warning
Due to supporting guzzle 7, this version does not support older versions of PHP such as 5.5 or greater. 