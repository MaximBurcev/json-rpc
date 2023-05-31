<?php

namespace App;

class Helper
{
    const CLASS_NAME = "App\JsonRPC";

    /**
     * Used for generic failures.
     *
     * @param int       $errorCode   according to JSON-RPC specification
     * @return array   Response object for this error
     */
    public static function errorResponse($errorCode, $id = null, $data = null): array
    {
        $response = [
            "jsonrpc" => "2.0",
            "error" => [
                "code" => $errorCode,
                "message" => ''
            ],
            "id" => $id
        ];
        if ($data) {
            $response["error"]["data"] = $data;
        }
        switch ($errorCode) {
            case '-32600':
                $response["error"]["message"] = "Invalid Request";
                break;
            case '-32700':
                $response["error"]["message"] = "Parse error";
                break;
            case '-32601':
                $response["error"]["message"] = "Method not found";
                break;
            case '-32602':
                $response["error"]["message"] = "Invalid params";
                break;
            case '-32603':
                $response["error"]["message"] = "Internal error";
                break;
            default:
                $response["error"]["message"] = "Internal error";
                break;
        }
        return $response;
    }

    /**
     * Process single JSON-RPC request.
     *
     * @param $payload
     * @return array   Response object
     */
    public static function processRequest($payload) {
        if (!is_array($payload)) {
            return Helper::errorResponse(-32700);
        }
        if (!array_key_exists("jsonrpc", $payload) && !array_key_exists("method", $payload)) {
            return Helper::errorResponse(-32600);
        }

        $methodName = $payload['method'];
        $params = $payload['params'];
        $className = self::CLASS_NAME;
        $outcome = (new $className())->$methodName($params);

        if (!array_key_exists("id", $payload ) || !$outcome) {
            return null;
        }

        $data = [
            "jsonrpc" => "2.0",
            "id" => $payload['id']
        ];
        return array_merge($data, $outcome);
    }
}


