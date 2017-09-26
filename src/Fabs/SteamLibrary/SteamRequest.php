<?php


namespace Fabs\SteamLibrary;


use Fabs\SteamLibrary\Exception\GeneralSteamException;
use Fabs\SteamLibrary\Exception\BadGatewayException;
use Fabs\SteamLibrary\Exception\TooManyRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SteamRequest
{

    /** @var string */
    public static $proxy_url = null;
    /** @var int */
    public static $proxy_port = 0;
    /** @var string */
    public static $proxy_username_password = null;

    const BASE_IMAGE_URL = 'https://steamcommunity-a.akamaihd.net/economy/image/';

    /**
     * @param string $url
     * @param bool $do_not_proxy
     * @return mixed
     * @throws BadGatewayException
     * @throws GeneralSteamException
     * @throws TooManyRequestException
     * @author necipallef <necipallef@gmail.com>
     */
    public static function get($url, $do_not_proxy = false)
    {

        $config = [];
        if ($do_not_proxy !== true) {
            if (self::$proxy_url !== null) {
                $config['curl'] = [
                    CURLOPT_PROXY => self::$proxy_url,
                    CURLOPT_PROXYPORT => self::$proxy_port,
                    CURLOPT_PROXYUSERPWD => self::$proxy_username_password,
                ];
                $config['proxy'] = self::$proxy_url;

            }
        }

        $client = new Client($config);
        try {
            $json_content = $client->get($url)->getBody()->getContents();
            return json_decode($json_content, true);
        } catch (RequestException $exception){
            switch ($exception->getResponse()->getStatusCode()){
                case 429:
                    throw new TooManyRequestException($exception->getRequest()->getUri()->getPath());
                case 500:
                    throw new GeneralSteamException($exception->getRequest()->getUri()->getPath());
                case 502:
                    $reason_phrase = $exception->getResponse()->getReasonPhrase();
                    if ($reason_phrase === null){
                        $reason_phrase = '';
                    }
                    $reason_message = sprintf('body %s, reason %s', $exception->getResponse()->getBody(), $reason_phrase);
                    throw new BadGatewayException($exception->getRequest()->getUri()->getPath(), $reason_message);
                default:
                    throw $exception;
            }
        }
    }


    /**
     * @param string $url
     * @param mixed $body
     * @param bool $do_not_proxy
     * @return mixed
     * @author necipallef <necipallef@gmail.com>
     */
    public static function post($url, $body, $do_not_proxy = false)
    {

        $config = [];
        if ($do_not_proxy !== true) {
            if (self::$proxy_url !== null) {
                $config['curl'] = [
                    CURLOPT_PROXY => self::$proxy_url,
                    CURLOPT_PROXYPORT => self::$proxy_port,
                    CURLOPT_PROXYUSERPWD => self::$proxy_username_password,
                ];
                $config['proxy'] = self::$proxy_url;

            }
        }

        $client = new Client($config);
        $json_content = $client->post($url, $body)->getBody()->getContents();
        return json_decode($json_content, true);
    }
}