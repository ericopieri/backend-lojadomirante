<?php
require_once "Configurations/Connection.php";

class TipoModel extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function update($validatedData, $id)
    {
        try {
            $sql = "UPDATE tipo_produto SET nome = :nome, percentual_imposto = :percentual_imposto WHERE tipo_produto.codigo = :id";
            $preparePatch = $this->connection->prepare($sql);
            $preparePatch->bindValue(":nome", $validatedData["nome"]);
            $preparePatch->bindValue(":percentual_imposto", $validatedData["percentual_imposto"]);
            $preparePatch->bindValue(":id", $id);
            $preparePatch->execute();

            if ($preparePatch->rowCount() > 0) {
                http_response_code(200);
                return array("status" => "success", "message" => "Informações do Tipo de Produto atualizadas com sucesso!");
            }

            http_response_code(400);
            return array("status" => "error", "message" => "Alguma das suas informações está errada ou o objeto não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function get($id)
    {
        try {
            $sql = "SELECT * FROM tipo_produto WHERE tipo_produto.codigo = :id";
            $prepareTipo = $this->connection->prepare($sql);
            $prepareTipo->bindValue(":id", $id);
            $prepareTipo->execute();

            if ($prepareTipo->rowCount() > 0) {
                $tipo = $prepareTipo->fetch(PDO::FETCH_OBJ);

                http_response_code(200);
                return array("status" => "success", "data" => $tipo);
            }

            http_response_code(404);
            return array("status" => "success", "message" => "Tipo não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function getAll()
    {
        try {
            $tipos = $this->connection->query("SELECT * FROM tipo_produto")->fetchAll(PDO::FETCH_OBJ);

            http_response_code(200);
            return array("status" => "success", "data" => $tipos);
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function create($validatedData)
    {
        try {
            $sql = "INSERT INTO tipo_produto (nome, percentual_imposto) VALUES (:nome, :percentual_imposto)";
            $insert = $this->connection->prepare($sql);
            $insert->bindValue(":nome", $validatedData["nome"]);
            $insert->bindValue(":percentual_imposto", $validatedData["percentual_imposto"]);
            $insert->execute();

            if ($insert->rowCount() > 0) {
                http_response_code(201);
                return array("status" => "success", "message" => "Tipo Produto inserido com sucesso!");
            }

            http_response_code(500);
            return array("status" => "error", "message" => "Falha ao inserir o produto!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }
}
