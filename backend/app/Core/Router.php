<?php
namespace App\Core;

use App\Helpers\Response;

final class Router
{
    private array $routes = [];

    public function get(string $p, array $h): void    { $this->add('GET', $p, $h); }
    public function post(string $p, array $h): void   { $this->add('POST', $p, $h); }
    public function put(string $p, array $h): void    { $this->add('PUT', $p, $h); }
    public function delete(string $p, array $h): void { $this->add('DELETE', $p, $h); }

    private function add(string $method, string $path, array $handler): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $this->routes[] = [$method, $regex, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes as [$m, $regex, $handler]) {
            if ($m === $method && preg_match($regex, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$class, $action] = $handler;
                (new $class())->$action(...array_map('intval', array_values($params)));
                return;
            }
        }
        Response::json(['ok' => false, 'message' => 'Endpoint tidak ditemukan.'], 404);
    }
}