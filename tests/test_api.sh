#!/bin/bash

# API Test Script
# Tests the API endpoint with various requests
# Usage: bash tests/test_api.sh

BASE_URL="http://localhost:8000"
API_ENDPOINT="$BASE_URL/api/update.php"
VALID_API_KEY="your_secret_api_key_change_this_in_production"

echo "=== Hummingbot Dashboard API Tests ==="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

passed=0
failed=0

# Test 1: Valid request
echo -e "${YELLOW}Test 1: Valid API request${NC}"
response=$(curl -s -X POST "$API_ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "'$VALID_API_KEY'",
    "strategy_name": "btc_test_'$(date +%s)'",
    "nav": 10250.45678,
    "timestamp": "'$(date +'%Y-%m-%d %H:%M:%S')'"
  }')
echo "Response: $response"
if echo "$response" | grep -q '"status":"success"'; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Test 2: Invalid API key
echo -e "${YELLOW}Test 2: Invalid API key${NC}"
response=$(curl -s -X POST "$API_ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "invalid_key",
    "strategy_name": "test_strategy",
    "nav": 10250.45,
    "timestamp": "'$(date +'%Y-%m-%d %H:%M:%S')'"
  }')
echo "Response: $response"
if echo "$response" | grep -q '"status":"error"'; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Test 3: Missing parameters
echo -e "${YELLOW}Test 3: Missing required parameters${NC}"
response=$(curl -s -X POST "$API_ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "'$VALID_API_KEY'",
    "strategy_name": "test_strategy"
  }')
echo "Response: $response"
if echo "$response" | grep -q '"status":"error"'; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Test 4: Invalid NAV
echo -e "${YELLOW}Test 4: Invalid NAV (non-numeric)${NC}"
response=$(curl -s -X POST "$API_ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "'$VALID_API_KEY'",
    "strategy_name": "test_strategy",
    "nav": "not_a_number",
    "timestamp": "'$(date +'%Y-%m-%d %H:%M:%S')'"
  }')
echo "Response: $response"
if echo "$response" | grep -q '"status":"error"'; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Test 5: Invalid datetime format
echo -e "${YELLOW}Test 5: Invalid datetime format${NC}"
response=$(curl -s -X POST "$API_ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "'$VALID_API_KEY'",
    "strategy_name": "test_strategy",
    "nav": 10250.45,
    "timestamp": "invalid-datetime"
  }')
echo "Response: $response"
if echo "$response" | grep -q '"status":"error"'; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Test 6: GET request (should be rejected)
echo -e "${YELLOW}Test 6: GET request (should be rejected)${NC}"
http_code=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$API_ENDPOINT")
echo "HTTP Code: $http_code"
if [ "$http_code" = "405" ]; then
  echo -e "${GREEN}✓ PASS${NC}\n"
  ((passed++))
else
  echo -e "${RED}✗ FAIL${NC}\n"
  ((failed++))
fi

# Results
echo ""
echo "=== Results ==="
total=$((passed + failed))
echo "Passed: $passed/$total"
echo "Failed: $failed/$total"

if [ $failed -eq 0 ]; then
  echo -e "\n${GREEN}✓ All tests passed!${NC}\n"
  exit 0
else
  echo -e "\n${RED}✗ Some tests failed.${NC}\n"
  exit 1
fi
