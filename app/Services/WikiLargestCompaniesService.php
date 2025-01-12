<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CaptureCompany;
use App\Models\Company;
use App\Helpers\Helpers;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WikiLargestCompaniesService
{
    protected HttpClientInterface $httpClient;
    const WIKI_URL = 'https://pt.wikipedia.org/wiki/Lista_das_maiores_empresas_do_Brasil';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Método para setar o HttpClient manualmente nos testes
    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function importData(): void
    {
        $html = $this->fetchDataFromWiki();
        $crawler = new Crawler($html);
        $firstTable = $crawler->filter('div.mw-content-ltr table')->first();

        $dataHeader = $this->getTableRows($firstTable, 'th');
        
        if (!$this->validateHeaderProfit($dataHeader)) {
            throw new \Exception('The profit column is missing the word "bilhões", check that the value has not been changed to "milhões"!');
        }

        $rowIndex = $this->getRowsIndex($dataHeader);
        $rowCompanies = $this->getRowsCompany($firstTable, $rowIndex);

        $this->insertCompanies($rowCompanies, $html);
    }

    private function fetchDataFromWiki(): string
    {
        $response = $this->httpClient->request('GET', self::WIKI_URL);
        return $response->getContent();
    }

    private function getRowsCompany(Crawler $firstTable, array $rowIndex): array
    {
        $allowedColumns = ['rank', 'name', 'profit'];

        $data = [];
        $companies = $this->getTableRows($firstTable, 'td');
        foreach ($companies as $company) {
            $data[] = array_combine($rowIndex, $company);
        }

        return array_map(function ($row) use ($allowedColumns) {
            return array_intersect_key($row, array_flip($allowedColumns));
        }, $data);
    }

    private function getTableRows(Crawler $firstTable, string $filterType): array
    {
        $data = [];
        $firstTable->filter('tr')->each(function (Crawler $row) use (&$data, $filterType) {
            $columns = $row->filter($filterType);

            if ($columns->count() > 0) {
                $data[] = $columns->each(function (Crawler $column) use ($filterType) {
                    $value = trim($column->text());
                    return ($filterType === 'th' && $value === "#") ? 'Rank' : $value;
                });
            }
        });

        return $data;
    }

    private function insertCompanies(array $rowCompanies, string $html): void
    {
        $capture_company = CaptureCompany::createCaptureCompany($html);

        Company::truncateCompaniesTable();

        $data = $this->prepareCompanyData($rowCompanies, $capture_company);

        Company::insertCompanyData($data);

        Helpers::clearCache("Company");
    }

    private function prepareCompanyData(array $rowCompanies, CaptureCompany $capture_company): array
    {
        return collect($rowCompanies)->map(function ($value) use ($capture_company) {
            return [
                'rank' => $value['rank'],
                'name' => $value['name'],
                'profit' => $this->formatProfit($value['profit']),
                'created_at' => date("Y-m-d H:i:s"),
                'capture_company_id' => $capture_company->id,
            ];
        })->toArray();
    }

    private function formatProfit(string $profit): float
    {
        if (strpos($profit, 'milhões') !== false) {
            return (float) str_replace(',', '.', preg_replace('/[^\d,]/', '', $profit)) / 1000;
        }

        return (float) str_replace(',', '.', preg_replace('/[^\d,]/', '', $profit));
    }

    private function getRowsIndex(array $dataHeader): array
    {
        $fieldMapping = [
            'rank' => 'rank',
            'nome' => 'name',
            'lucro' => 'profit',
        ];

        $keys = [];

        foreach ($dataHeader[0] as $value) {
            $value = mb_strtolower(trim($value));

            $normalizedValue = $this->getMappedField($value, $fieldMapping);

            if (!$normalizedValue) {
                $normalizedValue = md5($value);
            }

            $keys[] = $normalizedValue;
        }

        return $keys;
    }

    private function getMappedField(string $value, array $fieldMapping): ?string
    {
        foreach ($fieldMapping as $key => $mappedField) {
            if (str_starts_with($value, $key)) {
                return $mappedField;
            }
        }
        return null;
    }

    private function validateHeaderProfit(array $dataHeader): bool
    {
        if (empty($dataHeader) || empty($dataHeader[0])) {
            return false;
        }

        foreach ($dataHeader[0] as $value) {
            $normalizedValue = mb_strtolower(trim($value));

            if (str_contains($normalizedValue, 'lucro') && str_contains($normalizedValue, 'bilhões')) {
                return true;
            }
        }

        return false;
    }
}
