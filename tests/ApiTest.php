<?php

/**
 * Unit Tests for API Endpoint
 *
 * Run with: php tests/ApiTest.php
 */

class ApiTest {
    private $baseUrl = 'http://localhost:8000';
    private $validApiKey = 'your_secret_api_key_change_this_in_production';
    private $testsPassed = 0;
    private $testsFailed = 0;

    public function run() {
        echo "\n=== API Tests ===\n\n";

        $this->testValidRequest();
        $this->testValidRequestWithNavBtc();
        $this->testInvalidApiKey();
        $this->testMissingParameters();
        $this->testInvalidStrategyName();
        $this->testInvalidNav();
        $this->testInvalidNavBtc();
        $this->testInvalidDatetime();
        $this->testUpsertFunctionality();
        $this->testUpsertWithNavBtc();

        $this->printResults();
    }

    private function testValidRequest() {
        echo "Test 1: Valid API request (without NAV-BTC)\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy_1',
            'nav' => 10250.45678,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'success', 'Valid request should succeed');
    }

    private function testValidRequestWithNavBtc() {
        echo "Test 2: Valid API request (with NAV-BTC)\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy_with_btc',
            'nav' => 10250.45678,
            'nav_btc' => 0.25678,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'success', 'Valid request with NAV-BTC should succeed');
    }

    private function testInvalidApiKey() {
        echo "Test 3: Invalid API key\n";

        $data = [
            'api_key' => 'invalid_api_key',
            'strategy_name' => 'test_strategy',
            'nav' => 10250.45,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 401, 'Invalid API key should be rejected');
    }

    private function testMissingParameters() {
        echo "Test 4: Missing required parameters\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy'
            // Missing nav and timestamp
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 400, 'Missing parameters should be rejected');
    }

    private function testInvalidStrategyName() {
        echo "Test 5: Invalid strategy name (too long)\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => str_repeat('a', 101), // > 100 chars
            'nav' => 10250.45,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 400, 'Invalid strategy name should be rejected');
    }

    private function testInvalidNav() {
        echo "Test 6: Invalid NAV (non-numeric)\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy',
            'nav' => 'not_a_number',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 400, 'Invalid NAV should be rejected');
    }

    private function testInvalidNavBtc() {
        echo "Test 7: Invalid NAV-BTC (non-numeric)\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy',
            'nav' => 10250.45,
            'nav_btc' => 'not_a_number',
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 400, 'Invalid NAV-BTC should be rejected');
    }

    private function testInvalidDatetime() {
        echo "Test 8: Invalid datetime format\n";

        $data = [
            'api_key' => $this->validApiKey,
            'strategy_name' => 'test_strategy',
            'nav' => 10250.45,
            'timestamp' => 'invalid-datetime'
        ];

        $response = $this->makeRequest($data);
        $this->assert($response['status'] === 'error' && $response['status_code'] === 400, 'Invalid datetime should be rejected');
    }

    private function testUpsertFunctionality() {
        echo "Test 9: UPSERT functionality (insert then update)\n";

        $strategyName = 'upsert_test_' . time();
        $nav1 = 5000.0;
        $nav2 = 6000.0;

        // First request: INSERT
        $data1 = [
            'api_key' => $this->validApiKey,
            'strategy_name' => $strategyName,
            'nav' => $nav1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response1 = $this->makeRequest($data1);
        $insertSuccess = $response1['status'] === 'success';

        // Second request: UPDATE same strategy with different NAV
        $data2 = [
            'api_key' => $this->validApiKey,
            'strategy_name' => $strategyName,
            'nav' => $nav2,
            'timestamp' => date('Y-m-d H:i:s', time() + 60)
        ];

        $response2 = $this->makeRequest($data2);
        $updateSuccess = $response2['status'] === 'success';

        $this->assert($insertSuccess && $updateSuccess, 'UPSERT functionality should work correctly');
    }

    private function testUpsertWithNavBtc() {
        echo "Test 10: UPSERT functionality with NAV-BTC\n";

        $strategyName = 'upsert_btc_test_' . time();
        $nav1 = 5000.0;
        $navBtc1 = 0.1;
        $nav2 = 6000.0;
        $navBtc2 = 0.15;

        // First request: INSERT with NAV-BTC
        $data1 = [
            'api_key' => $this->validApiKey,
            'strategy_name' => $strategyName,
            'nav' => $nav1,
            'nav_btc' => $navBtc1,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $response1 = $this->makeRequest($data1);
        $insertSuccess = $response1['status'] === 'success';

        // Second request: UPDATE same strategy with different NAV and NAV-BTC
        $data2 = [
            'api_key' => $this->validApiKey,
            'strategy_name' => $strategyName,
            'nav' => $nav2,
            'nav_btc' => $navBtc2,
            'timestamp' => date('Y-m-d H:i:s', time() + 60)
        ];

        $response2 = $this->makeRequest($data2);
        $updateSuccess = $response2['status'] === 'success';

        $this->assert($insertSuccess && $updateSuccess, 'UPSERT with NAV-BTC should work correctly');
    }

    private function makeRequest($data) {
        $url = $this->baseUrl . '/api/update.php';
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonResponse = json_decode($response, true) ?? [];
        $jsonResponse['status_code'] = $httpCode;

        return $jsonResponse;
    }

    private function assert($condition, $message) {
        if ($condition) {
            echo "  ✓ PASS: $message\n";
            $this->testsPassed++;
        } else {
            echo "  ✗ FAIL: $message\n";
            $this->testsFailed++;
        }
    }

    private function printResults() {
        $total = $this->testsPassed + $this->testsFailed;
        echo "\n=== Results ===\n";
        echo "Passed: {$this->testsPassed}/$total\n";
        echo "Failed: {$this->testsFailed}/$total\n";

        if ($this->testsFailed === 0) {
            echo "\n✓ All tests passed!\n\n";
        } else {
            echo "\n✗ Some tests failed.\n\n";
        }
    }
}

// Run tests if file is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $test = new ApiTest();
    $test->run();
}
