<?php

namespace Mralston\Mug;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class Mug
{
    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $authEndpoints = [
        'local' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'dev' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'testing' => 'https://authorisation-staging.myutilitygenius.co.uk',
        'production' => 'https://authorisation.myutilitygenius.co.uk',
    ];

    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'local' => 'https://api-home-staging.myutilitygenius.co.uk',
        'dev' => 'https://api-home-staging.myutilitygenius.co.uk',
        'testing' => 'https://api-home-staging.myutilitygenius.co.uk',
        'production' => 'https://api-home.myutilitygenius.co.uk',
    ];

    private string $endpoint;
    private string $authEndpoint;
    private string $token;

    public function __construct(
        public string $clientId,
        public string $secret,
        string $endpoint
    ) {
        $this->authEndpoint = $this->authEndpoints[$endpoint];
        $this->endpoint = $this->endpoints[$endpoint];
    }

    private function authenticate(?bool $force = false): void
    {
        if (!empty($this->token) && !$force) {
            return;
        }

        $json = Http::asForm()
            ->post($this->authEndpoint . '/connect/token', [
                'grant_type' => 'client_credentials',
                'scope' => 'DomesticApi',
                'client_id' => $this->clientId,
                'client_secret' => $this->secret,
            ])
            ->throw()
            ->json();

        $this->token = $json['access_token'];
    }

    public function addressPostcodeReady(?string $postCode = null): bool
    {
        if (empty($postCode)) {
            return false;
        }

        $this->authenticate();

        $json = Http::withHeader('Authorization', 'Bearer ' . $this->token)
            ->post($this->endpoint . '/request/Address/Postcode/Ready', [
                'postcodeReadyBindingModel' => $postCode,
            ])
            ->throw()
            ->json();

        return $json['postcodeReadyDto']['postcodeIsSwitchable'];
    }

    public function addressRecco(?string $postCode = null): Collection
    {
        if (empty($postCode)) {
            return collect();
        }

        $this->authenticate();

        $json = Http::withHeader('Authorization', 'Bearer ' . $this->token)
            ->get($this->endpoint . '/request/Address/recco', [
                'postcode' => $postCode,
            ])
            ->throw()
            ->json();

        return collect($json)->sortBy('addressLine1');
    }

    public function addressReccoDetails(array $address = null): array
    {
        if (empty($address)) {
            return [];
        }

        $this->authenticate();

        return Http::withHeader('Authorization', 'Bearer ' . $this->token)
            ->post($this->endpoint . '/request/Address/recco/details', [
                'mpanCores' => $address['mpancore'],
                'xoserveAddressCodes' => $address['xoserveAddressCode']
            ])
            ->throw()
            ->json()[0];
    }
}
