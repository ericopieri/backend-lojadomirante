<?php
require_once "Models/ProdutoModel.php";

class ProdutoController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProdutoModel();
    }

    public function delete($id)
    {
        echo json_encode($this->model->delete($id));
    }

    public function update($id)
    {
        $validatedData = $this->validateInfo();
        echo json_encode($this->model->update($validatedData, $id));
    }

    public function create()
    {
        $validatedData = $this->validateInfo();

        if (is_array($validatedData)) {
            echo json_encode($this->model->create($validatedData));
            return null;
        }

        http_response_code(400);
        die(array("status" => "error", "message" => "Requisição mal feita!"));
    }

    private function validateInfo()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST["nome"]) and isset($_POST["valor"]) and isset($_POST["tipo"])) {
                $tipo = $_POST["tipo"];
                $nome = $_POST["nome"];
                $valor = $_POST["valor"];

                if ($valor < 0 or !is_numeric($valor)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "O valor DEVE ser númerico e maior que 0!")));
                }

                if ($tipo < 0 or !is_numeric($tipo)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "O tipo deve ser um valor número e maior que -1!")));
                }

                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $nome)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "Nome com caracteres especiais!")));
                }

                return array(
                    "tipo" => $tipo,
                    "nome" => trim($nome),
                    "valor" => $valor
                );
            }

            return null;
        }

        http_response_code(400);
        die(json_encode(array("status" => "error", "message" => "Para essa action, o método DEVE ser POST!")));
    }

    public function get($id = null)
    {
        if ($id) {
            echo json_encode($this->model->get($id));
            return null;
        }

        echo json_encode($this->model->getAll());
    }
}
