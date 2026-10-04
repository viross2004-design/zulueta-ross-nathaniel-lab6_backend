<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Send CORS headers before database or signing-key initialization can fail.
        handle_cors();
        $this->call->database();
        $this->call->library('api');
    }

    public function register()
    {
        $this->api->rate_limit('register:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 3600);
        $data = $this->api->body();
        $username = trim($data['username'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';

        if (strlen($username) < 2 || strlen($username) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $this->api->respond_error('Enter a username, valid email, and password with at least 8 characters.', 422);
        }

        $existing = $this->db->raw('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1', [$username, $email])->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $this->api->respond_error('That username or email is already registered.', 409);
        }

        $this->db->raw('INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)', [$username, $email, password_hash($password, PASSWORD_DEFAULT), 'user']);
        $user = ['id' => (int) $this->db->last_id(), 'username' => $username, 'email' => $email, 'role' => 'user'];
        $tokens = $this->api->issue_tokens(['id' => $user['id'], 'role' => 'user', 'scopes' => ['read', 'write', 'delete']]);
        $this->api->respond(['message' => 'Account created.', 'user' => $user, 'tokens' => $tokens], 201);
    }

    public function login()
    {
        $this->api->rate_limit('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 20, 900);
        $data = $this->api->body();
        $login = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $stmt = $this->db->raw('SELECT id, username, email, password, role, is_active FROM users WHERE email = ? OR username = ? LIMIT 1', [$login, $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !(int) $user['is_active'] || !password_verify($password, $user['password'])) {
            $this->api->respond_error('Email or password is incorrect.', 401);
        }

        $tokens = $this->api->issue_tokens(['id' => (int) $user['id'], 'role' => $user['role'], 'scopes' => ['read', 'write', 'delete']]);
        unset($user['password'], $user['is_active']);
        $this->api->respond(['message' => 'Signed in.', 'user' => $user, 'tokens' => $tokens]);
    }

    public function logout()
    {
        $this->api->require_jwt();
        $body = $this->api->body();
        if (!empty($body['refresh_token'])) {
            $this->api->revoke_refresh_token($body['refresh_token']);
        }
        $this->api->respond(['message' => 'Signed out.']);
    }

    public function options()
    {
        http_response_code(204);
        exit;
    }

    public function products()
    {
        $this->api->require_jwt();
        $rows = $this->db->raw('SELECT id, product_name, description, price, quantity, created_at FROM products ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
        $this->api->respond(['products' => $rows]);
    }

    public function create_product()
    {
        $this->api->require_jwt();
        $data = $this->validated_product($this->api->body());
        $this->db->raw('INSERT INTO products (product_name, description, price, quantity) VALUES (?, ?, ?, ?)', [$data['product_name'], $data['description'], $data['price'], $data['quantity']]);
        $product = $this->db->raw('SELECT id, product_name, description, price, quantity, created_at FROM products WHERE id = ?', [$this->db->last_id()])->fetch(PDO::FETCH_ASSOC);
        $this->api->respond(['message' => 'Product added.', 'product' => $product], 201);
    }

    public function update_product($id)
    {
        $this->api->require_jwt();
        $data = $this->validated_product($this->api->body());
        $stmt = $this->db->raw('UPDATE products SET product_name = ?, description = ?, price = ?, quantity = ? WHERE id = ?', [$data['product_name'], $data['description'], $data['price'], $data['quantity'], (int) $id]);
        if (!$stmt->rowCount()) {
            $exists = $this->db->raw('SELECT id FROM products WHERE id = ?', [(int) $id])->fetch(PDO::FETCH_ASSOC);
            if (!$exists) $this->api->respond_error('Product not found.', 404);
        }
        $product = $this->db->raw('SELECT id, product_name, description, price, quantity, created_at FROM products WHERE id = ?', [(int) $id])->fetch(PDO::FETCH_ASSOC);
        $this->api->respond(['message' => 'Product updated.', 'product' => $product]);
    }

    public function delete_product($id)
    {
        $this->api->require_jwt();
        $stmt = $this->db->raw('DELETE FROM products WHERE id = ?', [(int) $id]);
        if (!$stmt->rowCount()) $this->api->respond_error('Product not found.', 404);
        $this->api->respond(['message' => 'Product deleted.']);
    }

    private function validated_product(array $data)
    {
        $name = trim($data['product_name'] ?? '');
        $description = trim($data['description'] ?? '');
        $price = $data['price'] ?? null;
        $quantity = $data['quantity'] ?? null;

        if ($name === '' || strlen($name) > 100 || !is_numeric($price) || (float) $price < 0 || (float) $price >= 100000000 || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 0) {
            $this->api->respond_error('Enter a product name, non-negative price, and whole-number quantity.', 422);
        }

        return ['product_name' => $name, 'description' => $description, 'price' => number_format((float) $price, 2, '.', ''), 'quantity' => (int) $quantity];
    }
}
