<?php

namespace Tests\Unit\Services;

use App\Models\CaptureCompany;
use App\Models\Company;
use App\Services\WikiLargestCompaniesService;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Models\BaseModel;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    /**
     * Helper method to generate authorization headers.
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . env('USER_AUTH_TEST'), // Bearer Token
        ];
    }

    /**
     * Set up a mock for the HttpClient, returning the specified content.
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
     * Common setup for testing API requests and validating the response.
     */
    private function performApiRequestAndValidate(array $queryParams, array $dataCompare)
    {
        $returnContent = file_get_contents('/app/storage/app/mock_file/Lista_das_maiores_empresas_do_Brasil.html');
        $wikiService = $this->setupMock($returnContent);

        // Perform data import.
        $wikiService->importData();

        $db = BaseModel::getConnectionResolver()->connection()->getPdo();
        $db->commit();

        $this->assertGreaterThan(0, CaptureCompany::count());
        $this->assertGreaterThan(0, Company::count());

        $headers = $this->getAuthHeaders();

        // Performs the GET request with the filter parameters
        $response = $this->get('/api/company/list?' . http_build_query($queryParams), $headers);

        // Check if the response was successful (status 200)
        $this->assertResponseOk();

        // Verify that the response content contains the expected data
        $this->seeJson($dataCompare);
    }

    /**
     * Test filtering companies with range.
     */
    public function testGetCompanyListWithBearerTokenAndFilteredByRange()
    {
        $queryParams = [
            'filters' => [
                'rule' => 'between',
                'range' => [1, 2]
            ]
        ];

        $dataCompare = [
            [
                "company_name" => "BTG Pactual",
                "profit" => "1.520",
                "rank" => 6
            ]
        ];

        $this->performApiRequestAndValidate($queryParams, $dataCompare);
    }

    /**
     * Test filtering companies with greater profit.
     */
    public function testGetCompanyListWithBearerTokenAndFilteredByGreater()
    {
        $queryParams = [
            'filters' => [
                'rule' => 'greater',
                'billions' => 32
            ]
        ];

        $dataCompare = [
            [
                "company_name" => "Petrobras",
                "profit" => "36.470",
                "rank" => 1
            ]
        ];

        $this->performApiRequestAndValidate($queryParams, $dataCompare);
    }

    /**
     * Test filtering companies with smaller profit.
     */
    public function testGetCompanyListWithBearerTokenAndFilteredBySmaller()
    {
        $queryParams = [
            'filters' => [
                'rule' => 'smaller',
                'billions' => 0.2
            ]
        ];

        $dataCompare = [
            [
                "company_name" => "Braskem",
                "profit" => "0.065",
                "rank" => 18
            ]
        ];

        $this->performApiRequestAndValidate($queryParams, $dataCompare);
    }

    public function testGetCompanyListWithBearerTokenAndFilteredByRuleWithoutBillions()
    {
        $rules = ['smaller', 'greater'];
        $dataCompare = [
            "filters.billions" => [
                "Billions - Required when Rule is \"greater\" or \"smaller\""
            ]
        ];

        foreach ($rules as $rule) {
            $this->assertMissingBillionsForRule($rule, $dataCompare, $this);
        }
    }

    private function assertMissingBillionsForRule(string $rule, array $dataCompare, $v)
    {
        $queryParams = [
            'filters' => [
                'rule' => $rule
            ]
        ];

        $headers = $this->getAuthHeaders();

        // Performs the GET request with the filter parameters
        $response = $this->get('/api/company/list?' . http_build_query($queryParams), $headers);

        // Verifies that the response has a 422 status code
        $this->assertResponseStatus(422);

        // Verifies that the response contains the expected JSON error message
        $this->seeJson($dataCompare);
    }

    public function testGetCompanyListWithBearerTokenAndMissingRangeForBetweenRule()
    {
        $dataCompare = [
            "filters.range" => [
                "Range - Required when Rule is \"between\""
            ]
        ];
        $this->assertMissingBillionsForRule('between', $dataCompare, $this);
    }

    public function testGetCompanyListWithBearerTokenAndInvalidRangeForBetweenRuleWithSingleItem()
    {
        $dataCompare = [
            "filters.range" => [
                "Range - Must be an array with exactly 2 items when Rule is \"between\""
            ]
        ];

        $queryParams = [
            'filters' => [
                'rule' => 'between',
                'range' => [1]
            ]
        ];

        $headers = $this->getAuthHeaders();

        // Performs the GET request with the filter parameters
        $response = $this->get('/api/company/list?' . http_build_query($queryParams), $headers);

        // Verifies that the response has a 422 status code
        $this->assertResponseStatus(422);

        // Verifies that the response contains the expected JSON error message
        $this->seeJson($dataCompare);
    }
}
