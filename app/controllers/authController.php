<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

class AuthController extends BaseController {
    protected array $jwtConfig;

    public function __construct(ContainerInterface $container){
        parent::__construct($container);        
        $this->jwtConfig = $this->settings['jwt']; // Retrieve JWT settings
    }
    // /////////////////////////////////////////////////////////////////////
    protected function getViewName(){
        return 'auth.login';
    }
    // /////////////////////////////////////////////////////////////////////
    protected function getPageVariables(){
        return []; // Returning an empty array
    }
    // /////////////////////////////////////////////////////////////////////
        // Handle login form submission
    public function login(Request $request, Response $response): Response {
        try {
            $isWebRequest = str_contains($request->getHeaderLine('User-Agent'), 'Mozilla');
            return $isWebRequest ? $this->handleWebLogin($request, $response) : $this->handleApiLogin($request, $response);
        } catch (\Exception $e) {
            error_log("Unexpected Login Error: " . $e->getMessage());
            return $this->renderLoginWithError($response, "An unexpected error occurred.");
        }
    }
    // /////////////////////////////////////////////////////////////////////
    private function renderLoginWithError(Response $response, string $errorMessage): Response {    
        $response->getBody()->write($this->render('auth.login', ['error' => $errorMessage]));
        return $response->withHeader('Content-Type', 'text/html')->withStatus(401);
    }
    // /////////////////////////////////////////////////////////////////////
        // Browser-Based (Session) Authentication
    private function handleWebLogin(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
    
        // Validate CSRF Token
        if(!isset($data['_token']) || $data['_token'] !== ($_SESSION['_token'] ?? '')){
            return $this->renderLoginWithError($response, "Invalid CSRF token. Please refresh the page and try again.");
        }
    
        if(!$username || !$password){
            return $this->renderLoginWithError($response, "Username and password are required.");
        }
    
        $user = $this->getUserByUsername($username);
        if(!$user || hash('sha256', $password, true) !== $user['password_hash']){
            return $this->renderLoginWithError($response, "Invalid username or password.");
        }
    
        // Secure Session Management
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
        
        return $response->withHeader('Location', $this->router->urlFor('dashboard'))->withStatus(302);
    }
    // /////////////////////////////////////////////////////////////////////
        // API-Based (JWT) Authentication
    private function handleApiLogin(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');
    
        if(!$username || !$password){
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Username and password are required.'], 400);
        }
    
        $user = $this->getUserByUsername($username);
        if(!$user || !password_verify($password, $user['password_hash'])){
            return $this->jsonResponse($response, ['status' => 'error', 'message' => 'Invalid username or password.'], 401);
        }
    
        // Generate JWT Token
        $token = JWT::encode([
            'iss' => $this->jwtConfig['issuer'],
            'iat' => time(),
            'exp' => time() + $this->jwtConfig['exp'],
            'sub' => $user['username'],
            'role' => $user['role'] ?? 'user'
        ], $this->jwtConfig['secret'], 'HS256');
    
        return $this->jsonResponse($response, ['status' => 'success', 'token' => $token], 200);
    }
    // /////////////////////////////////////////////////////////////////////
        // Helper: Fetch User by Username
    private function getUserByUsername(string $username): ?array {
        return $this->db->query("SELECT * FROM Users WHERE username = ?", [$username])[0] ?? null;
    }
    // /////////////////////////////////////////////////////////////////////
}
