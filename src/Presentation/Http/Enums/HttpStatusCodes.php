<?php

declare(strict_types=1);

namespace Mvreisg\GamebaseBackend\Presentation\Http\Enums;

enum HttpStatusCodes: string
{
    case Ok = "HTTP/1.1 200 OK";
    case Created = "HTTP/1.1 201 Created";
    case NoContent = "HTTP/1.1 204 No Content";
    case BadRequest = "HTTP/1.1 400 Bad Request";
    case Unauthorized = "HTTP/1.1 401 Unauthorized";
    case Forbidden = "HTTP/1.1 403 Forbidden";
    case NotFound = "HTTP/1.1 404 Not Found";
    case InternalServerError = "HTTP/1.1 500 Internal Server Error";
}
