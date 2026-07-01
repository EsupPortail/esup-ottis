#!/bin/bash

# PHASE 2 - Automated Testing Script
# Tests security fixes for PIN synchronization and translation
# Generated: 24 June 2026

echo "=========================================="
echo "PHASE 2 - Security Testing"
echo "=========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
TOTAL=0

# Test results directory
RESULTS_DIR="/home/omist/audit/phase_2/test_results"
mkdir -p "$RESULTS_DIR"

# Log file
LOG_FILE="$RESULTS_DIR/test_phase2_$(date +%Y%m%d_%H%M%S).log"
echo "Test log: $LOG_FILE" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Function to log and display
log_test() {
    local test_name="$1"
    local result="$2"
    TOTAL=$((TOTAL + 1))
    
    if [ "$result" = "PASS" ]; then
        echo -e "${GREEN}✓ PASS${NC}: $test_name" | tee -a "$LOG_FILE"
        PASSED=$((PASSED + 1))
    elif [ "$result" = "WARN" ]; then
        echo -e "${YELLOW}⚠ WARN${NC}: $test_name" | tee -a "$LOG_FILE"
        PASSED=$((PASSED + 1))
    else
        echo -e "${RED}✗ FAIL${NC}: $test_name" | tee -a "$LOG_FILE"
        FAILED=$((FAILED + 1))
    fi
}

echo "--- PHP Syntax Validation ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Test PHP syntax for all modified files
php_files=(
    "/home/omist/synchronize_savepin.php"
    "/home/omist/Translate.php"
    "/home/omist/chat.php"
    "/home/omist/remote.php"
    "/home/omist/conference.php"
    "/home/omist/index.php"
    "/home/omist/Student.php"
    "/home/omist/includes/header.php"
)

for file in "${php_files[@]}"; do
    if php -l "$file" 2>&1 | grep -q "No syntax errors"; then
        log_test "PHP Syntax: $(basename $file)" "PASS"
    else
        log_test "PHP Syntax: $(basename $file)" "FAIL"
        php -l "$file" 2>&1 | tee -a "$LOG_FILE"
    fi
done

echo "" | tee -a "$LOG_FILE"
echo "--- Security Checks ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for CSRF includes
if grep -q "require_once.*Security/CSRF.php" /home/omist/synchronize_savepin.php; then
    log_test "CSRF include in synchronize_savepin.php" "PASS"
else
    log_test "CSRF include in synchronize_savepin.php" "FAIL"
fi

if grep -q "requireCsrf('POST')" /home/omist/synchronize_savepin.php; then
    log_test "CSRF validation in synchronize_savepin.php" "PASS"
else
    log_test "CSRF validation in synchronize_savepin.php" "FAIL"
fi

if grep -q "requireCsrf('POST')" /home/omist/Translate.php; then
    log_test "CSRF validation in Translate.php" "PASS"
else
    log_test "CSRF validation in Translate.php" "FAIL"
fi

# Check for input validation in synchronize_savepin.php
if grep -q "validateRoomId" /home/omist/synchronize_savepin.php; then
    log_test "ROOMID validation in synchronize_savepin.php" "PASS"
else
    log_test "ROOMID validation in synchronize_savepin.php" "FAIL"
fi

if grep -q "preg_match.*pincode" /home/omist/synchronize_savepin.php; then
    log_test "Pincode validation in synchronize_savepin.php" "PASS"
else
    log_test "Pincode validation in synchronize_savepin.php" "FAIL"
fi

# Check for race condition prevention
if grep -q "flock" /home/omist/synchronize_savepin.php; then
    log_test "File locking in synchronize_savepin.php" "PASS"
else
    log_test "File locking in synchronize_savepin.php" "FAIL"
fi

# Check for HTTP method validation
if grep -q "REQUEST_METHOD.*POST" /home/omist/synchronize_savepin.php; then
    log_test "HTTP method validation in synchronize_savepin.php" "PASS"
else
    log_test "HTTP method validation in synchronize_savepin.php" "FAIL"
fi

if grep -q "REQUEST_METHOD.*POST" /home/omist/Translate.php; then
    log_test "HTTP method validation in Translate.php" "PASS"
else
    log_test "HTTP method validation in Translate.php" "FAIL"
fi

echo "" | tee -a "$LOG_FILE"
echo "--- GET vs POST Parameter Checks ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for remaining GET parameters in Translate.php
if grep -q '\$_GET\["texte"\]' /home/omist/Translate.php; then
    log_test "Translate.php uses GET for texte parameter" "FAIL"
else
    log_test "Translate.php uses POST for texte parameter" "PASS"
fi

if grep -q '\$_GET\["apikey"\]' /home/omist/Translate.php; then
    log_test "Translate.php uses GET for apikey parameter" "FAIL"
else
    log_test "Translate.php uses POST for apikey parameter" "PASS"
fi

if grep -q '\$_POST\["texte"\]' /home/omist/Translate.php; then
    log_test "Translate.php reads texte from POST" "PASS"
else
    log_test "Translate.php reads texte from POST" "FAIL"
fi

# Check for GET parameters in JS files (should be none)
js_files=(
    "/home/omist/js/conference-func.js"
    "/home/omist/js/conf-func.js"
    "/home/omist/js/auditeur-func.js"
    "/home/omist/js/chat-common.js"
)

GET_PARAMS_FOUND=0
for file in "${js_files[@]}"; do
    if grep -q "Translate\.php?texte=" "$file" 2>/dev/null; then
        GET_PARAMS_FOUND=1
        echo "  Found GET params in: $(basename $file)" | tee -a "$LOG_FILE"
    fi
done

if [ $GET_PARAMS_FOUND -eq 0 ]; then
    log_test "No GET parameters in JS translation calls" "PASS"
else
    log_test "No GET parameters in JS translation calls" "FAIL"
fi

echo "" | tee -a "$LOG_FILE"
echo "--- CSRF Token Distribution Checks ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for CSRF_TOKEN in PHP files that need it
for file in "${php_files[@]}"; do
    if [[ "$file" == *"synchronize_savepin.php"* ]] || [[ "$file" == *"Translate.php"* ]]; then
        # These files need CSRF validation
        if grep -q "requireCsrf" "$file"; then
            log_test "CSRF validation in $(basename $file)" "PASS"
        else
            log_test "CSRF validation in $(basename $file)" "FAIL"
        fi
    fi
done

# Check for CSRF_TOKEN in HTML files
html_php_files=(
    "/home/omist/chat.php"
    "/home/omist/remote.php"
    "/home/omist/index.php"
    "/home/omist/Student.php"
    "/home/omist/includes/header.php"
)

for file in "${html_php_files[@]}"; do
    if grep -q "CSRF_TOKEN" "$file"; then
        log_test "CSRF_TOKEN defined in $(basename $file)" "PASS"
    else
        log_test "CSRF_TOKEN defined in $(basename $file)" "FAIL"
    fi
done

echo "" | tee -a "$LOG_FILE"
echo "--- Error Handling Checks ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for proper error handling in synchronize_savepin.php
http_codes=(400 403 405 500)
for code in "${http_codes[@]}"; do
    if grep -q "http_response_code($code)" /home/omist/synchronize_savepin.php; then
        log_test "HTTP $code error handling in synchronize_savepin.php" "PASS"
    else
        log_test "HTTP $code error handling in synchronize_savepin.php" "WARN"
    fi
done

if grep -q "http_response_code(405)" /home/omist/Translate.php; then
    log_test "HTTP 405 error handling in Translate.php" "PASS"
else
    log_test "HTTP 405 error handling in Translate.php" "WARN"
fi

echo "" | tee -a "$LOG_FILE"
echo "--- FormData Usage in JS Files ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for FormData usage in JS files
for file in "${js_files[@]}"; do
    if grep -q "FormData()" "$file" 2>/dev/null; then
        log_test "FormData used in $(basename $file)" "PASS"
    else
        log_test "FormData used in $(basename $file)" "WARN"
    fi
done

# Check for csrf_token in FormData
for file in "${js_files[@]}"; do
    if grep -q "csrf_token.*CSRF_TOKEN" "$file" 2>/dev/null; then
        log_test "CSRF token in FormData in $(basename $file)" "PASS"
    else
        log_test "CSRF token in FormData in $(basename $file)" "WARN"
    fi
done

echo "" | tee -a "$LOG_FILE"
echo "--- Comment Language Checks ---" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check for French comments in modified files
french_comments=0
for file in "${php_files[@]}"; do
    if grep -q "//.*[àâäéèêëîïôöùûüÿçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ]" "$file" 2>/dev/null; then
        french_comments=1
        echo "  Found French comments in: $(basename $file)" | tee -a "$LOG_FILE"
    fi
done

if [ $french_comments -eq 0 ]; then
    log_test "No French comments in PHP files" "PASS"
else
    log_test "No French comments in PHP files" "FAIL"
fi

echo "" | tee -a "$LOG_FILE"
echo "==========================================" | tee -a "$LOG_FILE"
echo "TEST SUMMARY" | tee -a "$LOG_FILE"
echo "==========================================" | tee -a "$LOG_FILE"
echo "Total tests: $TOTAL" | tee -a "$LOG_FILE"
echo -e "${GREEN}Passed: $PASSED${NC}" | tee -a "$LOG_FILE"
echo -e "${RED}Failed: $FAILED${NC}" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Calculate percentage
if [ $TOTAL -gt 0 ]; then
    PERCENTAGE=$(( (PASSED * 100) / TOTAL ))
    echo "Success rate: ${PERCENTAGE}%" | tee -a "$LOG_FILE"
else
    echo "No tests run" | tee -a "$LOG_FILE"
fi

echo "" | tee -a "$LOG_FILE"

# Exit with appropriate code
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}All tests passed!${NC}" | tee -a "$LOG_FILE"
    exit 0
else
    echo -e "${RED}Some tests failed. Check log for details.${NC}" | tee -a "$LOG_FILE"
    exit 1
fi
