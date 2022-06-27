<?php

namespace Bookboon\ApiBundle\Client;

use Bookboon\OauthClient\BookboonProvider;
use Bookboon\OauthClient\OauthGrants;
use Bookboon\ApiBundle\Exception\ApiAuthenticationException;
use Bookboon\ApiBundle\Exception\ApiInvalidStateException;
use Bookboon\ApiBundle\Exception\UsageException;
use GuzzleHttp\TransferStats;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;

class AccessTokenClient
{
    private ?AccessTokenInterface $accessToken;
    protected ?string $act;
    protected BookboonProvider $provider;
    protected array $requestOptions = [];
    protected string $apiId;

    public function __construct(
        string $apiId,
        string $apiSecret,
        array $scopes,
        string $authServiceUri,
        string $redirectUri,
        ?string $appUserId = null,
        ?LoggerInterface $logger = null,
        array $clientOptions = []
    ) {
        if (empty($apiId)) {
            throw new UsageException("Client id is required");
        }

        $clientOptions = array_merge(
            $clientOptions,
            [
                'clientId'      => $apiId,
                'clientSecret'  => $apiSecret,
                'scope'         => $scopes,
                'redirectUri'   => $redirectUri,
                'baseUri'       => $authServiceUri,
            ]
        );

        if ($logger !== null) {
            $this->requestOptions = [
                'on_stats' => function (TransferStats $stats) use ($logger) {
                    if ($stats->hasResponse()) {
                        $size = $stats->getHandlerStat('size_download') ?? 0;
                        $statusCode = $stats->getResponse() ? $stats->getResponse()->getStatusCode() : 0;

                        $logger->info(
                            "Api request \"{$stats->getRequest()->getMethod()} {$stats->getRequest()->getRequestTarget()} HTTP/{$stats->getRequest()->getProtocolVersion()}\" {$statusCode} - {$size} - {$stats->getTransferTime()}"
                        );
                    } else {
                        $logger->error(
                            "Api request: No response received with error {$stats->getHandlerErrorData()}"
                        );
                    }
                }
            ];
        }

        $clientOptions['requestOptions'] = $this->requestOptions;
        $this->provider = new BookboonProvider($clientOptions);

        $this->apiId = $apiId;
        $this->act = $appUserId;
    }

    /**
     * @param array $options
     * @param string $type
     * @return AccessTokenInterface
     * @throws ApiAuthenticationException
     * @throws UsageException
     */
        public function requestAccessToken(
        array $options = [],
        string $type = OauthGrants::AUTHORIZATION_CODE
    ) : AccessTokenInterface {
        $provider = $this->provider;

        if ($type == OauthGrants::AUTHORIZATION_CODE && !isset($options["code"])) {
            throw new UsageException("This oauth flow requires a code");
        }

        try {
            $this->accessToken = $provider->getAccessToken($type, $options);
        }

        catch (IdentityProviderException $e) {
            //TODO: Parse and send this with exception (string) $e->getResponseBody()->getBody()
            throw new ApiAuthenticationException("Authorization Failed");
        }

        return $this->accessToken;
    }

    public function refreshAccessToken(AccessTokenInterface $accessToken) : AccessTokenInterface
    {
        $this->accessToken = $this->provider->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->getRefreshToken()
        ]);

        return $accessToken;
    }

    public function generateState(): string
    {
        return $this->provider->generateRandomState();
    }

    public function isCorrectState(string $stateParameter, string $stateSession) : bool
    {
        if (empty($stateParameter) || ($stateParameter !== $stateSession)) {
            throw new ApiInvalidStateException("State is invalid");
        }

        return true;
    }

    public function getAuthorizationUrl(array $options = []): string
    {
        $provider = $this->provider;

        if (null != $this->act && false === isset($options['act'])) {
            $options['act'] = $this->act;
        }

        return $provider->getAuthorizationUrl($options);
    }

    public function getAct(): ?string {
        return $this->act;
    }
}
