<?php

namespace Bubasuma\RequestLogger;

use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Formats log messages using variable substitutions for requests, responses,
 * and other transactional data.
 *
 * The following variable substitutions are supported:
 *
 * - {request}:        Full HTTP request message
 * - {response}:       Full HTTP response message
 * - {ts}:             ISO 8601 date in GMT
 * - {date_iso_8601}   ISO 8601 date in GMT
 * - {date_common_log} Apache common log date using the configured timezone.
 * - {host}:           Host of the request
 * - {method}:         Method of the request
 * - {uri}:            URI of the request
 * - {host}:           Host of the request
 * - {version}:        Protocol version
 * - {target}:         Request target of the request (path + query + fragment)
 * - {hostname}:       Hostname of the machine that sent the request
 * - {code}:           Status code of the response (if available)
 * - {phrase}:         Reason phrase of the response  (if available)
 * - {error}:          Any error messages (if available)
 * - {req_header_*}:   Replace `*` with the lowercased name of a request header to add to the message
 * - {res_header_*}:   Replace `*` with the lowercased name of a response header to add to the message
 * - {req_headers}:    Request headers
 * - {res_headers}:    Response headers
 * - {req_body}:       Request body
 * - {res_body}:       Response body
 */
class MessageFormatter
{
    /**
     * Apache Common Log Format.
     * @link http://httpd.apache.org/docs/2.4/logs.html#common
     * @var string
     */
    const CLF = "{hostname} {req_header_User-Agent} - [{date_common_log}] \"{method} {target} HTTP/{version}\" {code}
     {res_header_Content-Length}";
    const DEBUG = ">>>>>>>>\n{request}\n<<<<<<<<\n{response}\n--------\n{error}";
    const SHORT = '[{ts}] "{method} {target} HTTP/{version}" {code}';
    /** @var string Template used to format log messages */
    private $template;

    /**
     * @param string $template Log message template
     */
    public function __construct($template = self::CLF)
    {
        $this->template = $template ?: self::CLF;
    }

    /**
     * Returns a formatted message string.
     *
     * @param Request $request
     * @param Response|ResponseTrait $response
     *
     * @return string
     */
    public function format(Request $request, Response $response = null)
    {
        $cache = [];

        return preg_replace_callback(
            '/{\s*([A-Za-z_\-\.0-9]+)\s*}/',
            function (array $matches) use ($request, $response, &$cache) {

                if (isset($cache[$matches[1]])) {
                    return $cache[$matches[1]];
                }

                $result = '';
                switch ($matches[1]) {
                    case 'request':
                        $result = $this->formatRequest($request);
                        break;
                    case 'response':
                        $result = $response ? $this->formatResponse($response) : 'NULL';
                        break;
                    case 'req_headers':
                        $result = $this->formatRequestHeaders($request);
                        break;
                    case 'res_headers':
                        $result = $response ? $this->formatResponseHeaders($response) : 'NULL';
                        break;
                    case 'req_body':
                        $result = json_encode($request->all());
                        break;
                    case 'res_body':
                        $result = $response ? $response->getContent() : 'NULL';
                        break;
                    case 'ts':
                    case 'date_iso_8601':
                        $result = gmdate('c');
                        break;
                    case 'date_common_log':
                        $result = date('d/M/Y:H:i:s O');
                        break;
                    case 'method':
                        $result = $request->getMethod();
                        break;
                    case 'uri':
                    case 'url':
                        $result = $request->getUri();
                        break;
                    case 'target':
                        $result = $request->getRequestUri();
                        break;
                    case 'res_version':
                        $result = $response ? $response->getProtocolVersion() : 'NULL';
                        break;
                    case 'host':
                        $result = $request->getHost();
                        break;
                    case 'version':
                        $result = str_replace('HTTP/', '', $request->server->get('SERVER_PROTOCOL'));
                        break;
                    case 'hostname':
                        $result = gethostname();
                        break;
                    case 'code':
                        $result = $response ? $response->getStatusCode() : 'NULL';
                        break;
                    case 'phrase':
                        $result = $response
                            ? (Response::$statusTexts[$response->getStatusCode()] ?? 'unknown status')
                            : 'NULL';
                        break;
                    case 'error':
                        $result = $response && property_exists($response, 'exception') && !is_null($response->exception)
                            ? $response->exception->getMessage()
                            : 'NULL';
                        break;
                    default:
                        // handle prefixed dynamic headers
                        if (strpos($matches[1], 'req_header_') === 0) {
                            $result = $request->header(substr($matches[1], 11));
                        } elseif (strpos($matches[1], 'res_header_') === 0) {
                            $result = $response
                                ? $response->headers->get(substr($matches[1], 11))
                                : 'NULL';
                        }

                        $result = is_array($result) ? implode(',', $result) : $result;
                }

                $cache[$matches[1]] = $result;

                return $result;
            },
            $this->template
        );
    }


    public function formatRequest(Request $request)
    {
        return "{$this->formatRequestHeaders($request)}\r\n\r\n" . json_encode($request->all());
    }

    public function formatResponse(Response $response)
    {
        return "{$this->formatResponseHeaders($response)}\r\n\r\n{$response->getContent()}";
    }

    public function formatRequestHeaders(Request $request)
    {
        $proto = $request->server->get('SERVER_PROTOCOL');

        return sprintf('%s %s %s', $request->getMethod(), $request->getRequestUri(), $proto)
            . "\r\n"
            . $request->headers;
    }

    public function formatResponseHeaders(Response $response)
    {
        $statusText = Response::$statusTexts[$response->getStatusCode()] ?? 'unknown status';

        return sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $statusText)
            . "\r\n"
            . $response->headers;
    }
}
