<?php
// Simple JWT implementation (without requiring Firebase library)

// Secret key for JWT - in production, store this in an environment variable
define('JWT_SECRET_KEY', 'vetcare_secret_jwt_key_2025'); 
define('JWT_EXPIRE_TIME', 60 * 60 * 24); // 24 hours (in seconds)

/**
 * Generate JWT token
 * 
 * @param array $userData User data to encode in the token
 * @return string JWT token
 */
function generateJWTToken($userData) {
    $issuedAt = time();
    $expirationTime = $issuedAt + JWT_EXPIRE_TIME;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'data' => [
            'id' => $userData['id'],
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ]
    ];
    
    // Encode Header
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
    
    // Encode Payload
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    
    // Create Signature
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    // Create JWT
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

/**
 * Validate JWT token
 * 
 * @param string $token JWT token to validate
 * @return array|bool User data if valid, false if invalid
 */
function validateJWTToken($token) {
    try {
        // Split the token
        $tokenParts = explode(".", $token);
        if (count($tokenParts) != 3) {
            return false;
        }
        
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];
        
        // Check the expiration time
        $decodedPayload = json_decode($payload, true);
        $expiration = $decodedPayload['exp'] ?? 0;
        
        if ($expiration < time()) {
            return false;
        }
        
        // Verify signature
        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET_KEY, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        if ($base64UrlSignature !== $signatureProvided) {
            return false;
        }
        
        return $decodedPayload['data'];
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get authorization header
 * 
 * @return string|null Authorization header or null if not found
 */
function getAuthorizationHeader() {
    $headers = null;
    
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        } elseif (isset($requestHeaders['authorization'])) {
            // Try lowercase version as well
            $headers = trim($requestHeaders['authorization']);
        }
    }
    
    // If header is still not found, check if it's in the $_SERVER['HTTP_*'] format
    if (!$headers) {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                if (strpos(strtolower($key), 'authorization') !== false) {
                    $headers = $value;
                    break;
                }
            }
        }
    }
    
    // Debug: Log all possible Authorization headers for troubleshooting
    if (!$headers) {
        error_log('DEBUG: Authorization header not found. $_SERVER dump: ' . print_r($_SERVER, true));
        if (function_exists('apache_request_headers')) {
            error_log('DEBUG: apache_request_headers: ' . print_r(apache_request_headers(), true));
        }
    }
    // Last resort - check in the $_GET parameters (not recommended but sometimes used)
    if (!$headers && isset($_GET['token'])) {
        $headers = 'Bearer ' . $_GET['token'];
    }
    
    return $headers;
}

/**
 * Get bearer token
 * 
 * @return string|null Bearer token or null if not found
 */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Authenticate user from JWT token
 * 
 * @return array|bool User data if authenticated, false if not
 */
function authenticateUser() {
    $token = getBearerToken();
    
    if (!$token) {
        return false;
    }
    
    return validateJWTToken($token);
}

/**
 * Check if user has required role
 * 
 * @param string|array $requiredRoles Required role(s)
 * @return bool True if user has required role, false if not
 */
function checkUserRole($requiredRoles) {
    $userData = authenticateUser();
    
    if (!$userData) {
        return false;
    }
    
    if (is_array($requiredRoles)) {
        return in_array($userData['role'], $requiredRoles);
    }
    
    return $userData['role'] === $requiredRoles || $userData['role'] === 'admin';
}