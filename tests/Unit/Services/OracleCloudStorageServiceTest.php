<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\OracleCloudStorageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

final class OracleCloudStorageServiceTest extends TestCase
{
    private OracleCloudStorageService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new OracleCloudStorageService;

        Storage::fake('local');
    }

    public function test_list_files_parses_s3_xml_response_correctly(): void
    {
        $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Contents>
    <Key>batch-daily/actual_2025-11-03.json.gz</Key>
    <LastModified>2025-11-03T03:00:06.000Z</LastModified>
    <Size>128754</Size>
  </Contents>
  <Contents>
    <Key>batch-init/init_2025-11-01_001.json.gz</Key>
    <LastModified>2025-11-02T21:00:11.000Z</LastModified>
    <Size>101148382</Size>
  </Contents>
</ListBucketResult>';

        Http::fake([
            '*' => Http::response($xmlResponse, 200),
        ]);

        $files = $this->service->listFiles();

        $this->assertCount(2, $files);
        $this->assertEquals('batch-daily/actual_2025-11-03.json.gz', $files[0]['key']);
        $this->assertEquals(128754, $files[0]['size']);
        $this->assertEquals('batch-init/init_2025-11-01_001.json.gz', $files[1]['key']);
        $this->assertEquals(101148382, $files[1]['size']);
    }

    public function test_list_files_throws_exception_on_http_error(): void
    {
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch bucket listing: HTTP 500');

        $this->service->listFiles();
    }

    public function test_list_files_throws_exception_on_invalid_xml(): void
    {
        Http::fake([
            '*' => Http::response('invalid xml', 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse bucket XML');

        $this->service->listFiles();
    }

    public function test_get_latest_daily_file_returns_most_recent(): void
    {
        $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Contents>
    <Key>batch-daily/actual_2025-11-01.json.gz</Key>
    <LastModified>2025-11-01T03:00:06.000Z</LastModified>
    <Size>100000</Size>
  </Contents>
  <Contents>
    <Key>batch-daily/actual_2025-11-03.json.gz</Key>
    <LastModified>2025-11-03T03:00:06.000Z</LastModified>
    <Size>128754</Size>
  </Contents>
  <Contents>
    <Key>batch-daily/actual_2025-11-02.json.gz</Key>
    <LastModified>2025-11-02T03:00:06.000Z</LastModified>
    <Size>120000</Size>
  </Contents>
</ListBucketResult>';

        Http::fake([
            '*' => Http::response($xmlResponse, 200),
        ]);

        $latestFile = $this->service->getLatestDailyFile();

        $this->assertNotNull($latestFile);
        $this->assertEquals('batch-daily/actual_2025-11-03.json.gz', $latestFile['key']);
    }

    public function test_get_latest_daily_file_returns_null_when_no_daily_files(): void
    {
        $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Contents>
    <Key>batch-init/init_2025-11-01_001.json.gz</Key>
    <LastModified>2025-11-02T21:00:11.000Z</LastModified>
    <Size>101148382</Size>
  </Contents>
</ListBucketResult>';

        Http::fake([
            '*' => Http::response($xmlResponse, 200),
        ]);

        $latestFile = $this->service->getLatestDailyFile();

        $this->assertNull($latestFile);
    }

    public function test_get_batch_init_files_returns_sorted_files(): void
    {
        $xmlResponse = '<?xml version="1.0" encoding="UTF-8"?>
<ListBucketResult>
  <Contents>
    <Key>batch-init/init_2025-11-01_003.json.gz</Key>
    <LastModified>2025-11-02T21:00:13.000Z</LastModified>
    <Size>100000</Size>
  </Contents>
  <Contents>
    <Key>batch-init/init_2025-11-01_001.json.gz</Key>
    <LastModified>2025-11-02T21:00:11.000Z</LastModified>
    <Size>100000</Size>
  </Contents>
  <Contents>
    <Key>batch-daily/actual_2025-11-03.json.gz</Key>
    <LastModified>2025-11-03T03:00:06.000Z</LastModified>
    <Size>128754</Size>
  </Contents>
  <Contents>
    <Key>batch-init/init_2025-11-01_002.json.gz</Key>
    <LastModified>2025-11-02T21:00:12.000Z</LastModified>
    <Size>100000</Size>
  </Contents>
</ListBucketResult>';

        Http::fake([
            '*' => Http::response($xmlResponse, 200),
        ]);

        $initFiles = $this->service->getBatchInitFiles();

        $this->assertCount(4, $initFiles);
        $this->assertEquals('batch-init/init_2025-11-01_001.json.gz', $initFiles[0]['key']);
        $this->assertEquals('batch-init/init_2025-11-01_002.json.gz', $initFiles[1]['key']);
        $this->assertEquals('batch-init/init_2025-11-01_003.json.gz', $initFiles[2]['key']);
    }

    public function test_download_and_stream_json_processes_gzipped_json_lines(): void
    {
        $jsonData = [
            ['ico' => '12345678', 'name' => 'Company 1'],
            ['ico' => '87654321', 'name' => 'Company 2'],
        ];

        // Oracle format: {"exportDate":"...","results":[{...},{...}]}
        $oracleFormat = json_encode([
            'exportDate' => '2025-11-03',
            'results' => $jsonData,
        ], JSON_THROW_ON_ERROR);

        $gzippedContent = gzencode($oracleFormat);

        Http::fake([
            '*test.json.gz' => Http::response($gzippedContent, 200),
        ]);

        $results = [];
        foreach ($this->service->downloadAndStreamJson('test.json.gz') as $data) {
            $results[] = $data;
        }

        $this->assertCount(2, $results);
        $this->assertEquals('12345678', $results[0]['ico']);
        $this->assertEquals('Company 1', $results[0]['name']);
        $this->assertEquals('87654321', $results[1]['ico']);
        $this->assertEquals('Company 2', $results[1]['name']);

        $this->service->cleanup();
    }

    public function test_cleanup_removes_downloaded_files(): void
    {
        $gzippedContent = gzencode('test');

        Http::fake([
            '*' => Http::response($gzippedContent, 200),
        ]);

        // Trigger a download to create files
        iterator_to_array($this->service->downloadAndStreamJson('test1.json.gz'));
        iterator_to_array($this->service->downloadAndStreamJson('test2.json.gz'));

        $this->assertTrue(Storage::disk('local')->exists('temp/company-sync/test1.json.gz'));
        $this->assertTrue(Storage::disk('local')->exists('temp/company-sync/test2.json.gz'));

        $this->service->cleanup();

        $this->assertFalse(Storage::disk('local')->exists('temp/company-sync'));
    }
}
