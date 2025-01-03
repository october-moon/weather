<?php

/*
 * This file is part of the october-moon/weather.
 *
 * (c) october-moon <invalid@example.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace OctoberMoon\Weather\Tests;

    use OctoberMoon\Weather\Exceptions\InvalidArgumentException;
    use OctoberMoon\Weather\Weather;
    use PHPUnit\Framework\TestCase;
    use GuzzleHttp\ClientInterface;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\Attributes\UsesClass;

    #[CoversClass(Weather::class)]
    #[UsesClass(Weather::class)]
    class WeatherTest extends TestCase
    {
        public function testGetLiveWeather()
        {
            // 将 getWeather 接口模拟为返回固定内容，以测试参数传递是否正确
            $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
            $w->expects()->getWeather('深圳', 'base', 'json')->andReturn(['success' => true]);

            // 断言正确传参并返回
            $this->assertSame(['success' => true], $w->getLiveWeather('深圳'));
        }

        public function testGetForecastsWeather()
        {
            // 将 getWeather 接口模拟为返回固定内容，以测试参数传递是否正确
            $w = \Mockery::mock(Weather::class, ['mock-key'])->makePartial();
            $w->expects()->getWeather('深圳', 'all', 'json')->andReturn(['success' => true]);

            // 断言正确传参并返回
            $this->assertSame(['success' => true], $w->getForecastsWeather('深圳'));
        }

        // 检查 $type 参数
        public function testGetWeatherWithInvalidType()
        {
            $w = new Weather('mock-key');

            // 断言会抛出此异常类
            $this->expectException(InvalidArgumentException::class);

            // 断言异常消息为 'Invalid type value(base/all): all'
            $this->expectExceptionMessage('Invalid type value(live/forecast): all');

            $w->getWeather('深圳', 'all');

            $this->fail('Failed to assert getWeather throw exception with invalid argument.');
        }

        // 检查 $format 参数
        public function testGetWeatherWithInvalidFormat()
        {
            $w = new Weather('mock-key');

            // 断言会抛出此异常类
            $this->expectException(InvalidArgumentException::class);

            // 断言异常消息为 'Invalid response format: array'
            $this->expectExceptionMessage('Invalid response format: array');

            // 因为支持的格式为 xml/json，所以传入 array 会抛出异常
            $w->getWeather('深圳', 'base', 'array');

            // 如果没有抛出异常，就会运行到这行，标记当前测试没成功
            $this->fail('Failed to assert getWeather throw exception with invalid argument.');
        }

        public function testGetHttpClient()
        {
            $w = new Weather('mock-key');

            // 断言返回结果为 GuzzleHttp\ClientInterface 实例
            $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
        }

        public function testSetGuzzleOptions()
        {
            $w = new Weather('mock-key');

            // 设置参数前，timeout 为 null
            $this->assertNull($w->getHttpClient()->getConfig('timeout'));

            // 设置参数
            $w->setGuzzleOptions(['timeout' => 5000]);

            // 设置参数后，timeout 为 5000
            $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
        }
    }
