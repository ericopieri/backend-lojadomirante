<?php
require_once "Models/TipoModel.php";

class TipoController
{
    private $model;

    public function __construct()
    {
        $this->model = new TipoModel();
    }

    public function delete($id)
    {
        if (is_array($this->model->delete($id))) {
            echo json_encode($this->model->delete($id));
        }
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

    // public function create()
    // {
    //     $validatedData = $this->validateInfo();

    //     if (is_array($validatedData)) {
    //         echo json_encode($this->model->create($validatedData));
    //         return null;
    //     }

    //     http_response_code(400);
    //     die(json_encode(array("status" => "error", "message" => "Requisição mal feita!")));
    // }

    private function validateInfo()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["nome"]) and isset($_POST["percentual_imposto"])) {
                $nome = $_POST["nome"];
                $percentual_imposto = $_POST["percentual_imposto"];

                if ($percentual_imposto < 0 or !is_numeric($percentual_imposto)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "Imposto negativo ou não-númerico!")));
                }

                if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $nome)) {
                    http_response_code(400);
                    die(json_encode(array("status" => "error", "message" => "Nome com caracteres especiais!")));
                }

                return array(
                    "nome" => trim($nome),
                    "percentual_imposto" => $percentual_imposto
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
