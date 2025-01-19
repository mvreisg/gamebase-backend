<?php
    namespace Gamebase\Infrastructure\Http;

    class HttpResponse 
    {
        private array $headers;
        private string $body;

        public function __construct(array $headers = [], string $body = "")
        {
            $this->headers = $headers;
            $this->body = $body;
        }

        public function appendString(string $data)
        {
            $this->body .= $data;
            return $this;
        }

        public function appendArray(array $data)
        {
            $this->body .= json_encode($data);
            return $this;
        }

        public function addHeader(string $header)
        {
            $this->headers[] = $header;
            return $this;
        }

        public function status(string $status)
        {
            header($status);            
            return $this;
        }

        public function send()
        {
            foreach ($this->headers as $header)
            {
                header($header);
            }
            print($this->body);            
        }

        public function sendJSON()
        {
            header(HEADER_CONTENT_TYPE_APPLICATION_JSON);            
            foreach ($this->headers as $header)
            {
                header($header);
            }
            print($this->body);            
        }
    }
?>