<?php

namespace Gibbo\Bryn\Calculator\Yahoo;

use Gibbo\Bryn\Exchange;
use Gibbo\Bryn\ExchangeRate;
use Gibbo\Bryn\ExchangeRateCalculator;
use Gibbo\Bryn\ExchangeRateCalculatorException;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

/**
 * Calculates an exchange rate from Yahoo's YQL Finance API.
 */
class YahooCalculator implements ExchangeRateCalculator
{

    const URL = 'https://query.yahooapis.com/v1/public/yql?q=%s&env=store://datatables.org/alltableswithkeys&format=json';

    /**
     * @var HttpMethodsClient
     */
    private $httpClient;

    /**
     * Constructor.
     *
     * @param HttpMethodsClient $httpClient
     */
    public function __construct(HttpMethodsClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * A convenience method for initialising the default implementation.
     *
     * @return static
     */
    public static function default()
    {
        return new static(new HttpMethodsClient(HttpClientDiscovery::find(), MessageFactoryDiscovery::find()));
    }

    /**
     * @inheritdoc
     */
    public function getRate(Exchange $exchange): ExchangeRate
    {
        $query = sprintf(
            "select * from yahoo.finance.xchange where pair in ('%s%s')",
            $exchange->getBase(),
            $exchange->getCounter()
        );

        $response = $this->httpClient->send('GET', sprintf(static::URL, rawurlencode($query)));

        if ($response->getStatusCode() !== 200) {
            throw new ExchangeRateCalculatorException(sprintf(
                "Unsuccessful response status '%d' received from Yahoo's YQL Finance API",
                $response->getStatusCode()
            ));
        }

        $rates = json_decode($response->getBody()->getContents());

        if (!($rates instanceof \stdClass) || !isset($rates->query->results->rate->Rate)) {
            throw new ExchangeRateCalculatorException(sprintf(
                "Invalid JSON response received from Yahoo's YQL Finance API",
                $response->getStatusCode()
            ));
        }

        if (strtoupper($rates->query->results->rate->Rate) === 'N/A') {
            throw ExchangeRateCalculatorException::unsupportedCurrency($exchange->getBase(), $this);
        }

        return new ExchangeRate($exchange, (float)$rates->query->results->rate->Rate);
    }
}
