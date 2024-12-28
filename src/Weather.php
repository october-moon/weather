<?php

/*
 * This file is part of the october-moon/weather.
 *
 * (c) october-moon <invalid@example.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace OctoberMoon\Weather;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OctoberMoon\Weather\Exceptions\HttpException;
use OctoberMoon\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    protected $key;
    protected $guzzleOptions = [];

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     *  获取实时天气.
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    /**
     *  获取天气预报.
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function getForecastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }

    /**
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function getWeather($city, string $type = 'live', string $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        $types = [
            'live' => 'base',
            'forecast' => 'all',
        ];
        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!\array_key_exists(\strtolower($type), $types)) {
            throw new InvalidArgumentException('Invalid type value(live/forecast): '.$type);
        }
        // 2. 封装 query 参数，并对空值进行过滤。

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => \strtolower($format),
            'extensions' => \strtolower($type),
        ]);

        try {
            // 3. 调用 getHttpClient 获取实例，并调用该实例的 `get` 方法，
            // 传递参数为两个：$url、['query' => $query]，
            $response = $this->getHttpClient()->get($url, [
                'query' => $query,
            ])->getBody()->getContents();

            // 4. 返回值根据 $format 返回不同的格式，
            // 当 $format 为 json 时，返回数组格式，否则为 xml。
            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 5. 当调用出现异常时捕获并抛出，消息为捕获到的异常消息，
            // 并将调用异常作为 $previousException 传入。
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
