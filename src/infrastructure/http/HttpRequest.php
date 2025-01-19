<?php
    namespace Gamebase\Infrastructure\Http;

    class HttpRequest 
    {
        private string $method;
        private string $route;
        private array $queries;
        private array $params;
        private string $body;

        public function __construct(string $method, string $route, array $queries = [], array $params = [], string $body = "")
        {
            $this->method = $method;
            $this->route = $route;
            $this->queries = $queries;
            $this->params = $params;
            $this->body = $body;
        }

        public function getMethod()
        {
            return $this->method;
        }

        public function getRoute()
        {
            return $this->route;
        }

        public function getQueries()
        {
            return $this->queries;
        }

        public function getParams()
        {
            return $this->params;
        }

        public function getBody()
        {
            return $this->body;
        }

        public function parseBodyFromJSON()
        {
            return json_decode($this->body, true);
        }
    }
?>