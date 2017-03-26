<?php

namespace Gibbo\Bryn\Calculator\Yahoo\Test;

use Gibbo\Bryn\Calculator\Yahoo\ExchangeRateCalculator;
use Gibbo\Bryn\Currency;
use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Calculator tests.
 */
class ExchangeRateCalculatorTest extends TestCase
{

    /**
     * Can the calcualtor be initialised.
     *
     * @return void
     */
    public function testCanBeInitialised()
    {
        $this->assertInstanceOf(ExchangeRateCalculator::class, $this->getCalculator());
    }

    /**
     * Test an exception is thrown when an unsuccessful response is received.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage Unsuccessful response status '500' received from Yahoo's YQL Finance API
     *
     * @return void
     */
    public function testDoesThrowOnUnsuccessfulResponse()
    {
        $httpClient = new Client();

        $httpClient->addResponse($this->getMockResponse('', 500));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test an exception is thrown when invalid JSON is provided in the response.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage Invalid JSON response received from Yahoo's YQL Finance API
     *
     * @return void
     */
    public function testDoesThrowOnInvalidJson()
    {
        $httpClient = new Client();

        $httpClient->addResponse($this->getMockResponse('foo'));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test an exception is thrown when the JSON doesn't match the expected structure.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage Invalid JSON response received from Yahoo's YQL Finance API
     *
     * @return void
     */
    public function testDoesThrowOnUnexpectedJson()
    {
        $httpClient = new Client();

        $contents = <<<JSON
{
  "query": {
    "count": 1,
    "created": "2017-03-25T21:39:08Z",
    "lang": "en-gb",
    "error": {
        "message": "foo bar"
    }
  }
}
JSON;

        $httpClient->addResponse($this->getMockResponse($contents));

        $this->getCalculator($httpClient)->getRate(new Exchange(Currency::GBP(), Currency::USD()));
    }

    /**
     * Test an exception is thrown when an unsupported currency is given.
     *
     * @expectedException \Gibbo\Bryn\ExchangeRateCalculatorException
     * @expectedExceptionMessage The currency 'GGG' is not supported by the calculator (Gibbo\Bryn\Calculator\Yahoo\ExchangeRateCalculator)
     *
     * @return void
     */
    public function testDoesThrowOnUnsupportedCurrency()
    {
        $httpClient = new Client();

        $contents = <<<JSON
{
  "query": {
    "count": 1,
    "created": "2017-03-25T21:39:08Z",
    "lang": "en-gb",
    "results": {
      "rate": {
        "id": "GGGEUR",
        "Name": "N/A",
        "Rate": "N/A",
        "Date": "N/A",
        "Time": "N/A",
        "Ask": "N/A",
        "Bid": "N/A"
      }
    }
  }
}
JSON;

        $httpClient->addResponse($this->getMockResponse($contents));

        $this->getCalculator($httpClient)->getRate(new Exchange(new Currency('GGG', '$'), Currency::Euro()));
    }

    /**
     * Test the exchange rate can be calculated.
     *
     * @return void
     */
    public function testDoesCalculateExchangeRate()
    {
        $httpClient = new Client();

        $contents = <<<JSON
{
  "query": {
    "count": 1,
    "created": "2017-03-25T21:43:21Z",
    "lang": "en-gb",
    "results": {
      "rate": {
        "id": "GBPEUR",
        "Name": "GBP/EUR",
        "Rate": "1.1545",
        "Date": "3/24/2017",
        "Time": "9:30pm",
        "Ask": "1.1555",
        "Bid": "1.1545"
      }
    }
  }
}
JSON;

        $httpClient->addResponse($this->getMockResponse($contents));

        $calculator = $this->getCalculator($httpClient);
        $exchange   = new Exchange(Currency::GBP(), Currency::Euro());

        $this->assertEquals(new ExchangeRate($exchange, 1.1545), $calculator->getRate($exchange));
    }

    /**
     * Get the calculator under test.
     *
     * @param Client|null $httpClient
     *
     * @return ExchangeRateCalculator
     */
    private function getCalculator(Client $httpClient = null): ExchangeRateCalculator
    {
        return new ExchangeRateCalculator(
            new HttpMethodsClient($httpClient ?: new Client(), MessageFactoryDiscovery::find())
        );
    }

    /**
     * Get a mock response.
     *
     * @param string $contents The response contents
     * @param int    $status   The response status
     *
     * @return ResponseInterface
     */
    private function getMockResponse($contents, $status = 200): ResponseInterface
    {
        $body     = $this->createMock(StreamInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $response
            ->method('getBody')
            ->willReturn($body);

        $body
            ->method('getContents')
            ->willReturn($contents);

        $response
            ->method('getStatusCode')
            ->willReturn($status);

        return $response;
    }
}