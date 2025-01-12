<?php

namespace Tests\Unit\Services;

use App\Models\CaptureCompany;
use App\Models\Company;
use App\Services\WikiLargestCompaniesService;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\TestCase;

class WikiLargestCompaniesServiceTest extends TestCase
{
    /**
     * Sets up a mock for the HttpClient, returning the specified content.
     *
     * @param string $returnContent Content to be returned by the mock.
     * @return WikiLargestCompaniesService
     */
    private function setupMock(string $returnContent): WikiLargestCompaniesService
    {
        // Mock HTTP client response.
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')
            ->willReturn($returnContent);

        // Mock for HTTP client.
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')
            ->with('GET', WikiLargestCompaniesService::WIKI_URL)
            ->willReturn($mockResponse);

        // Service instance with mocked HTTP client.
        return new WikiLargestCompaniesService($mockHttpClient, WikiLargestCompaniesService::WIKI_URL);
    }

    /**
     * Tests whether an exception is thrown when the table does not contain the column "lucro (bilhões)".
     */
    public function testValidateHeaderProfitThrowsException()
    {
        $returnContent = '<html></html>';
        $wikiService = $this->setupMock($returnContent);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The profit column is missing the word "bilhões", check that the value has not been changed to "milhões"!');

        $wikiService->importData();
    }

    /**
     * Tests whether the `importData` method inserts the correct number of records into the database.
     */
    public function testImportDataInsertsCorrectNumberOfRecords()
    {
        $returnContent = file_get_contents('/app/storage/app/mock_file/Lista_das_maiores_empresas_do_Brasil.html');
        $wikiService = $this->setupMock($returnContent);

        // Perform data import.
        $wikiService->importData();

        // Validates insertions into the database.
        $this->assertEquals(1, CaptureCompany::count(), 'The amount of capture entered is not correct.');
        $this->assertEquals(21, Company::count(), 'The number of companies entered is not correct.');
    }
}
