<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 * =====================
 *
 * Copyright © 2016 Billie Thompson (Purple Booth Ltd)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the “Software”), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace FitnessBundle\Service;

use DateInterval;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;

class StepService
{
    /**
     * @var MessageFactory
     */
    private $messageFactory;
    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var ResourceOwnerMap
     */
    private $googleResourceOwner;

    /**
     * StepService constructor.
     *
     * @param MessageFactory      $messageFactory
     * @param HttpClient          $httpClient
     * @param GoogleResourceOwner $googleResourceOwner
     */
    public function __construct(
        MessageFactory $messageFactory,
        HttpClient $httpClient,
        GoogleResourceOwner $googleResourceOwner
    ) {
        $this->messageFactory = $messageFactory;
        $this->httpClient = $httpClient;
        $this->googleResourceOwner = $googleResourceOwner;
    }

    public function getSteps(OAuthToken $oauthToken)
    {
        $to = new \DateTime();
        $from = (clone $to);
        $from->sub(new DateInterval('P1D'));

        $range = "{$from->getTimestamp()}000000000-{$to->getTimestamp()}000000000";
        $source = 'derived:com.google.step_count.delta:com.google.android.gms:estimated_steps';

        $response = $this->makeRequest(
            'https://www.googleapis.com/fitness/v1/users/me/dataSources/'.$source.'/datasets/'.$range,
            $oauthToken
        );

        $stepCount = 0;

        foreach (json_decode((string) $response->getBody(), true)['point'] as $dataPoint) {
            foreach ($dataPoint['value'] as $value) {
                $stepCount += $value['intVal'];
            }
        }

        return $stepCount;
    }

    /**
     * @param OAuthToken $oauthToken
     */
    private function refreshToken(OAuthToken $oauthToken): void
    {
        $newToken = $this->googleResourceOwner->refreshAccessToken($oauthToken->getRefreshToken());

        $oauthToken->setRefreshToken($newToken['refresh_token']);
        $oauthToken->setAccessToken($newToken['refresh_token']);
        $oauthToken->setExpiresIn($newToken['expires_in']);
    }

    /**
     * @param OAuthToken $oauthToken
     *
     * @throws \Exception
     * @throws \Http\Client\Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function makeRequest($url, OAuthToken $oauthToken): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->messageFactory->createRequest(
            'GET',
            $url,
            [
                'Authorization' => 'Bearer '.$oauthToken->getAccessToken(),
                'Content-Type'  => 'application/json;encoding=utf-8',
            ],
            ''
        );
        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() == 401) {
            $this->refreshToken($oauthToken);

            $request = $this->messageFactory->createRequest(
                'GET',
                $url,
                [
                    'Authorization' => 'Bearer '.$oauthToken->getAccessToken(),
                    'Content-Type'  => 'application/json;encoding=utf-8',
                ],
                ''
            );
            $response = $this->httpClient->sendRequest($request);
        }

        return $response;
    }
}
