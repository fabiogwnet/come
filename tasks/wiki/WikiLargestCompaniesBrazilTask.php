<?php

namespace Wiki;

use App\Models\BaseModel;
use App\Models\CaptureCompany;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

use App\Models\Company;

class WikiLargestCompaniesBrazilTask extends Command
{
    use LockableTrait;

    protected string $url = 'https://pt.wikipedia.org/wiki/Lista_das_maiores_empresas_do_Brasil';

    protected function configure()
    {
        $this->setName("wiki:import-largest-companies-brazil")
            ->setDescription("Run the import");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock("wiki:import-largest-companies-brazil")) {
            $output->writeln('<error>Task is already being executed by another process.</error>');
            return 0;
        }

        try {
            $this->importDataFromWiki();
            $output->writeln('<info>---- Import executed successfully.</info>');
            return 1;
        } catch (\Exception $e) {
            $output->writeln("<error>---- {$e->getMessage()} => Linha: {$e->getLine()}</error>");
            return 0;
        }
    }

    private function importDataFromWiki(): void
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $this->url);

        $html = $response->getContent();
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

    private function insertCompanies(array $rowCompanies, string $html): void
    {
        $this->db()->connection()->getPdo()->beginTransaction();

        try {
            $capture_company = CaptureCompany::createCaptureCompany($html);

            Company::truncateCompaniesTable();

            $data = $this->prepareCompanyData($rowCompanies, $capture_company);

            Company::insertCompanyData($data);

            $this->db()->connection()->getPdo()->commit();
        } catch (\Exception $e) {
            $this->db()->connection()->getPdo()->rollback();
            throw new \Exception("{$e->getMessage()} => Linha: {$e->getLine()}");
        }
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

    private function getRowsCompany(Crawler $firstTable, array $rowIndex): array
    {
        $allowedColumns = ['rank', 'name', 'profit'];

        $data = [];
        $companies = $this->getTableRows($firstTable, 'td');
        foreach ($companies as $company) {
            $data[] = array_combine($rowIndex, $company);
        }

        return array_map(function ($row) use ($allowedColumns) {
            // Filters the keys of each row according to the allowed keys
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

    private function formatProfit(string $profit): float
    {
        if (strpos($profit, 'milhões') !== false) {
            return (float) str_replace(',', '.', preg_replace('/[^\d,]/', '', $profit)) / 1000;
        }

        return (float) str_replace(',', '.', preg_replace('/[^\d,]/', '', $profit));
    }

    private function db()
    {
        return BaseModel::getConnectionResolver();
    }
}
