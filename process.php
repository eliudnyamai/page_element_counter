<?php
/*here is where the processing occurs to get the element counts
All join statements you see here AI was consulted
I have used prepared statements throughout to prevent SQL injection
*/
require_once 'config.php';//here we import the config file that has the database connection and other settings

header('Content-Type: application/json');

// Enable CORS for cross-domain requests(Consulted stackoverflow)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {//For security we only allow POST requests
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $input = getAndValidateInput();//Just like we validate input on client side we also do it here for added security
    $result = processElementCount($input['url'], $input['element']);
    
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Error processing request: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function getAndValidateInput() {
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $element = filter_input(INPUT_POST, 'element', FILTER_SANITIZE_STRING);
    
    if (!$url || !$element) {
        throw new Exception('URL and element are required');
    }
    
    // Validate URL format again server-side
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }
    
    // Validate element name (only letters and numbers, starting with letter) again on server-side
    if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $element)) {
        throw new Exception('Invalid element name. Must contain only letters and numbers, starting with a letter.');
    }
    
    return [
        'url' => $url,
        'element' => strtolower($element)
    ];
}

function processElementCount($url, $element) {
    $pdo = Database::getInstance();
    
    // Check cache first as per Test requirements. We use MySql to cache results for 24 hours
    $cachedResult = getCachedResult($pdo, $url, $element);
    if ($cachedResult) {
        return $cachedResult;
    }
    
    // Fetch and parse the page
    $startTime = microtime(true);
    $htmlContent = fetchUrlContent($url);
    $elementCount = countElements($htmlContent, $element);
    $duration = round((microtime(true) - $startTime) * 1000);
    
    // Store in database
    storeRequest($pdo, $url, $element, $elementCount, $duration);
    
    // Get statistics
    $domain = parse_url($url, PHP_URL_HOST);
    $statistics = getStatistics($pdo, $domain, $element);
    
    return [
        'request' => [
            'url' => $url,
            'element' => $element,
            'count' => $elementCount,
            'duration' => $duration,
            'date' => date('d/m/Y H:i')
        ],
        'statistics' => $statistics
    ];
}

function getCachedResult($pdo, $url, $element) {
    $twentyFourHrsAgo = date('Y-m-d H:i:s', time() - Config::CACHE_DURATION);
    
    $stmt = $pdo->prepare("
        SELECT r.count, r.duration, r.created_at, d.name as domain 
        FROM requests r
        JOIN urls u ON r.url_id = u.id
        JOIN domains d ON u.domain_id = d.id
        JOIN elements e ON r.element_id = e.id
        WHERE u.url = ? AND e.name = ? AND r.created_at > ?
        ORDER BY r.created_at DESC
        LIMIT 1
    ");
    
    $stmt->execute([$url, $element, $twentyFourHrsAgo]);
    $cached = $stmt->fetch();
    
    if ($cached) {
        $domain = parse_url($url, PHP_URL_HOST);
        $statistics = getStatistics($pdo, $domain, $element);
        
        return [
            'request' => [
                'url' => $url,
                'element' => $element,
                'count' => $cached['count'],
                'duration' => $cached['duration'],
                'date' => date('d/m/Y H:i', strtotime($cached['created_at']))
            ],
            'statistics' => $statistics
        ];
    }
    
    return null;
}

function fetchUrlContent($url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_ENCODING => '',
    ]);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($content === false) {
        throw new Exception('Failed to fetch URL: ' . $error);
    }
    
    if ($httpCode !== 200) {
        throw new Exception("HTTP error: {$httpCode}");
    }
    
    if (empty($content)) {
        throw new Exception('Empty response from server');
    }
    
    return $content;
}

function countElements($htmlContent, $element) {
    // I did research on stackoverflow to find the best way to parse HTML in PHP and settled on DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); 
    
    if (!$dom->loadHTML($htmlContent)) {
        throw new Exception('Failed to parse HTML content');
    }
    
    $elements = $dom->getElementsByTagName($element);
    return $elements->length;
}

function storeRequest($pdo, $url, $element, $count, $duration) {
    $pdo->beginTransaction();
    
    try {
        // Get or create domain
        $domain = parse_url($url, PHP_URL_HOST);
        $domainId = getOrCreateDomain($pdo, $domain);
        
        // Get or create URL
        $urlId = getOrCreateUrl($pdo, $url, $domainId);
        
        // Get or create element
        $elementId = getOrCreateElement($pdo, $element);
        
        // Insert request
        $stmt = $pdo->prepare("
            INSERT INTO requests (url_id, element_id, count, duration, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$urlId, $elementId, $count, $duration]);
        $requestId = $pdo->lastInsertId();
        
        $pdo->commit();
        return $requestId;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
// if the domain already exists we get its id else we create it and return the new id
function getOrCreateDomain($pdo, $domain) {
    $stmt = $pdo->prepare("SELECT id FROM domains WHERE name = ?");
    $stmt->execute([$domain]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO domains (name) VALUES (?)");
    $stmt->execute([$domain]);
    return $pdo->lastInsertId();
}
// if the url already exists we get its id else we create it and return the new id
function getOrCreateUrl($pdo, $url, $domainId) {
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE url = ? AND domain_id = ?");
    $stmt->execute([$url, $domainId]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO urls (domain_id, url) VALUES (?, ?)");
    $stmt->execute([$domainId, $url]);
    return $pdo->lastInsertId();
}
// if the element already exists we get its id else we create it and return the new id
function getOrCreateElement($pdo, $element) {
    $stmt = $pdo->prepare("SELECT id FROM elements WHERE name = ?");
    $stmt->execute([$element]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO elements (name) VALUES (?)");
    $stmt->execute([$element]);
    return $pdo->lastInsertId();
}

function getStatistics($pdo, $domain, $element) {
    // Get domain ID
    $stmt = $pdo->prepare("SELECT id FROM domains WHERE name = ?");
    $stmt->execute([$domain]);
    $domainResult = $stmt->fetch();
    
    if (!$domainResult) {
        return [
            'domain' => $domain,
            'domain_urls' => 0,
            'avg_duration' => 0,
            'domain_element_count' => 0,
            'total_element_count' => 0
        ];
    }
    
    $domainId = $domainResult['id'];
    
    // Get element ID
    $stmt = $pdo->prepare("SELECT id FROM elements WHERE name = ?");
    $stmt->execute([$element]);
    $elementResult = $stmt->fetch();
    $elementId = $elementResult ? $elementResult['id'] : 0;
    
    // Count unique URLs for this domain
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT id) as count FROM urls WHERE domain_id = ?");
    $stmt->execute([$domainId]);
    $domainUrls = $stmt->fetch()['count'];
    
    // Average duration for this domain in last 24 hours
    $stmt = $pdo->prepare("
        SELECT AVG(duration) as avg_duration 
        FROM requests r 
        JOIN urls u ON r.url_id = u.id 
        WHERE u.domain_id = ? AND r.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$domainId]);
    $avgDuration = round($stmt->fetch()['avg_duration'] ?? 0);
    
    // Total element count for this domain
    $stmt = $pdo->prepare("
        SELECT SUM(count) as total 
        FROM requests r 
        JOIN urls u ON r.url_id = u.id 
        WHERE u.domain_id = ? AND r.element_id = ?
    ");
    $stmt->execute([$domainId, $elementId]);
    $domainElementCount = $stmt->fetch()['total'] ?? 0;
    
    // Total element count across all requests
    $stmt = $pdo->prepare("
        SELECT SUM(count) as total 
        FROM requests 
        WHERE element_id = ?
    ");
    $stmt->execute([$elementId]);
    $totalElementCount = $stmt->fetch()['total'] ?? 0;
    
    return [
        'domain' => $domain,
        'domain_urls' => $domainUrls,
        'avg_duration' => $avgDuration,
        'domain_element_count' => $domainElementCount,
        'total_element_count' => $totalElementCount
    ];
}
?>