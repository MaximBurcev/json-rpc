<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use App\Helper;
use App\JsonRPC;

require __DIR__ . '/../vendor/autoload.php';



$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);





/*
 * Request object

jsonrpc
method
params
id


Response object

jsonrpc
result
error
id


POST / {"jsonrpc": "2.0", "method": "substract", "params": [42, 23], "id": 1}
POST / {"jsonrpc": "2.0", "method": "add", "params": [42, 23], "id": 1}

* */

$app->post('/', function (Request $request, Response $response) {

    try {
        $payloadData = $request->getParsedBody();
    } catch (\Throwable $th) {
        $response->getBody()->write(json_encode(Helper::errorResponse(-32700)));
    }

    try {
        // batch payload
        if (is_array($payloadData) && !array_key_exists('jsonrpc', $payloadData)) {
            if (count($payloadData) == 0) {
                $response->getBody()->write(json_encode(Helper::errorResponse(-32600)));
            }
            foreach ($payloadData as $payload) {
                $singleResponse = Helper::processRequest($payload);
                if ($singleResponse != null) {
                    $arResponse[] = $singleResponse;
                }
            }
            if (count($arResponse) > 0) {
                $response->getBody()->write(json_encode($arResponse));
            }
            // single request
        } else if (array_key_exists('jsonrpc', $payloadData)) {
            $arResponse = Helper::processRequest($payloadData);
            $response->getBody()->write(json_encode($arResponse));
        } else {
            $response->getBody()->write(json_encode(Helper::errorResponse(-32700)));
        }
    } catch (\Throwable $th) {
        $response->getBody()->write(json_encode(Helper::errorResponse(-32603, null, [
            "msg" => $th->getMessage(),
            "trace" => $th->getTrace()
        ])));
    }

    return $response;
});

$app->run();

