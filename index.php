<?php
require_once "headers.php";
require_once "Controllers/VendaController.php";
require_once "Controllers/ProdutoController.php";
require_once "Controllers/TipoController.php";
require_once "Models/VendaModel.php";

$url = explode("?", $_SERVER['REQUEST_URI']);

if (count($url) > 2) {
    http_response_code(400);
    die(json_encode(array("status" => "error", "message" => "URL mal formulada!")));
}

if (count($url) == 2) {
    array_pop($url);
}

$url_params = explode("/", implode("", $url));
array_shift($url_params);

if (count($url_params) > 2) {
    http_response_code(400);
    die(json_encode(array("status" => "error", "message" => "URL mal formulada!")));
}

$action = strtolower($_REQUEST["action"]);

if (!isset($action)) {
    http_response_code(400);
    die(json_encode(array("status" => "error", "message" => "Você deve informar a action!")));
}

function handleRequest($controller)
{
    global $action;
    global $url_params;

    if (isset($url_params[1])) {
        $id = $url_params[1];
    } else {
        $id = false;
    }

    switch ($action) {
        case "get":
            if ($id) {
                if (!is_numeric($id)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "O ID DEVE ser númerico!")));
                }

                $controller->get($url_params[1]);
                break;
            }

            $controller->get();
            break;
        case "create":
            $controller->create();
            break;
        case "update":
            if ($id) {
                if (!is_numeric($id)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "O ID DEVE ser númerico!")));
                }

                $controller->update($id);
                break;
            }

            http_response_code(400);
            die(json_encode(array("status" => "error", "message" => "Você DEVE informar um ID!")));
            break;
        case "delete":
            if ($id) {
                if (!is_numeric($id)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "O ID DEVE ser númerico!")));
                }

                $controller->delete($id);
                break;
            }

            http_response_code(400);
            die(json_encode(array("status" => "error", "message" => "Você DEVE informar um ID!")));
            break;
        default:
            http_response_code(405);
            die(json_encode(array("status" => "Error", "message" => "Action inválida!")));
            break;
    }
};

switch ($url_params[0]) {
    case "venda":
        handleRequest(new VendaController());
        break;
    case "produto":
        handleRequest(new ProdutoController());
        break;
    case "tipo":
        handleRequest(new TipoController());
        break;
    default:
        http_response_code(404);
        die(json_encode(array("status" => "Error", "message" => "URL não existe na aplicação!")));
        break;
};
