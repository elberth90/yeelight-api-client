# Contributing to yeelight-api-client
 
Feel free to open an [issue](https://github.com/elberth90/yeelight-api-client/issues) if you run into any problem, or
send a pull request (see bellow) with your contribution.

## Workflow when contributing a patch

1. Fork the project on GitHub
2. Implement your code changes into separate branch
3. Make sure all PHPUnit tests passes and code-style matches PSR-2 (see below). There is also Travis CI build which will automatically run tests on your pull request.
5. Submit your [pull request](https://github.com/elberth90/yeelight-api-client/pulls) against community branch
 
### Run unit tests

To run unit test simply run:
```bash
composer test
```

### Check coding style

Your code-style should comply with [PSR-2](http://www.php-fig.org/psr/psr-2/). To make sure your code matches this requirement run:
```bash
composer check-code
composer fix-code
```
