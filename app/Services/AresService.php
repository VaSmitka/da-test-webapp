<?php

declare(strict_types=1);

namespace CzechitasApp\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use Illuminate\Support\Facades\Log;

class AresService
{
    public const ARES_ICO_URL = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/%08d';

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Load data into form by ICO
     *
     * @return array<string, string>|false|null
     */
    public function loadCompanyInfoByICO(string $ico): array|false|null
    {
        $data = null;
        try {
            if (!\is_numeric($ico)) {
                return $data;
            }

            /** @var object $aresData */
            $aresData = Utils::jsonDecode(
                $this->client->get(\sprintf(self::ARES_ICO_URL, (int)$ico))->getBody()->getContents(),
            );

            $data = [
                'company' => $aresData->obchodniJmeno,
                'address' => $aresData->sidlo->textovaAdresa,
            ];
        } catch (\Throwable $e) {
            if ($e instanceof \GuzzleHttp\Exception\ClientException) {
                if ($e->getResponse()->getStatusCode() === 404) {
                    return $data;
                }
            }
            Log::warning($e->getMessage(), ['exception' => $e]);

            return false;
        }

        return $data;
    }
}
