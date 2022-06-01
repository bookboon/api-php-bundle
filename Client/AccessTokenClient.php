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
use Psr\SimpleCache\CacheInterface;

class AccessTokenClient
{
    protected string $_apiUri;
    private ?AccessTokenInterface $accessToken;
    protected ?string $act;
    protected BookboonProvider $provider;
    protected array $requestOptions = [];
    protected string $apiId;
    protected Headers $headers;
    protected ?CacheInterface $cache;

    public function __construct(
        string $apiId,
        string $apiSecret,
        Headers $headers,
        array $scopes,
        CacheInterface $cache = null,
        ?string $redirectUri = null,
        ?string $appUserId = null,
        ?string $authServiceUri = null,
        ?string $apiUri = null,
        LoggerInterface $logger = null,
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
        $this->cache = $cache;
        $this->headers = $headers;
        $this->act = $appUserId;

        $this->_apiUri = $this->parseUriOrDefault($apiUri);
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

    protected function parseUriOrDefault(?string $uri) : string
    {
        $protocol = ClientConstants::API_PROTOCOL;
        $host = ClientConstants::API_HOST;
        $path = ClientConstants::API_PATH;

        if (!empty($uri)) {
            $parts = explode('://', $uri);
            $protocol = $parts[0];
            $host = $parts[1];
            if (strpos($host, '/') !== false) {
                throw new UsageException('URI must not contain forward slashes');
            }
        }

        if ($protocol !== 'http' && $protocol !== 'https') {
            throw new UsageException('Invalid protocol specified in URI');
        }

        return "${protocol}://${host}${path}";
    }

    public function getAct(): ?string {
        return $this->act;
    }
}
