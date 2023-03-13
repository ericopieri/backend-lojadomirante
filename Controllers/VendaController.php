<?php
require_once "Models/VendaModel.php";

class VendaController
{
    private $model;

    public function __construct()
    {
        $this->model = new VendaModel();
    }

    public function get($id = null)
    {
        if ($id) {
            echo json_encode($this->model->get($id));
            return null;
        }

        echo json_encode($this->model->getAll());
    }

    public function update()
    {
        http_response_code(400);
        die(json_encode(array("status" => "error", "message" => "Impossível atualizar uma venda!")));
    }

    public function create()
    {
        $validatedData = $this->validateInfo();

        if (is_array($validatedData)) {
            echo json_encode($this->model->create($validatedData));
            return null;
        }

        http_response_code(400);
        die(json_encode(array("status" => "error", "message" => "Dados inválidos!")));
    }

    private function validateInfo()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["itens"]) and isset($_POST["total"])) {
                $itens = json_decode($_POST["itens"]);
                $total = $_POST["total"];

                if (sizeof($itens) === 0) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "Sem itens para esta Venda!")));
                }

                $erroItens = false;
                foreach ($itens as $item) {
                    if ($item->quantidade < 1 or $item->total < 0 or !is_numeric($item->quantidade) or !is_numeric($item->quantidade)) {
                        $erroItens = true;
                    }
                }

                if ($erroItens) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "Há erros nos seus items!")));
                }

                return array(
                    "itens" => $itens,
                    "total" => strip_tags(trim($total)),
                );
            }

            return null;
        }

        http_response_code(400);
        die(json_encode(array("status" => "error", "message" => "Para essa action, o método DEVE ser POST!")));
    }
}
