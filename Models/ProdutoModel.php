<?php
require_once "Configurations/Connection.php";

class ProdutoModel extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM produto WHERE produto.codigo = :codigo";
            $prepareDelete = $this->connection->prepare($sql);
            $prepareDelete->bindValue(":codigo", $id);
            $prepareDelete->execute();

            if ($prepareDelete->rowCount() > 0) {
                http_response_code(200);
                return array("status" => "success", "message" => "Tipo de Produto deletado com sucesso!");
            }

            http_response_code(400);
            return array("status" => "error", "message" => "Tipo de Produto não encontrado para deletar!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function get($id)
    {
        try {
            $sql = "SELECT * FROM produto WHERE produto.codigo = :id";
            $prepareProduto = $this->connection->prepare($sql);
            $prepareProduto->bindValue(":id", $id);
            $prepareProduto->execute();

            if ($prepareProduto->rowCount() > 0) {
                $produto = $prepareProduto->fetch(PDO::FETCH_OBJ);

                $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = $produto->tipo")->fetch(PDO::FETCH_OBJ);

                $produto->tipo = $tipo;

                http_response_code(200);
                return array("status" => "success", "data" => $produto);
            }

            http_response_code(404);
            return array("status" => "error", "message" => "Produto não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function getAll()
    {
        try {
            $produtos = $this->connection->query("SELECT * FROM produto")->fetchAll(PDO::FETCH_OBJ);

            foreach ($produtos as $produto) {
                $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = $produto->tipo")->fetch(PDO::FETCH_OBJ);
                $produto->tipo = $tipo;
            }

            http_response_code(200);
            return array("status" => "success", "data" => $produtos);
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function update($validatedData, $id)
    {
        try {
            $sql = "UPDATE produto SET nome = :nome, valor = :valor, tipo = :tipo WHERE produto.codigo = :id";
            $preparePatch = $this->connection->prepare($sql);
            $preparePatch->bindValue(":nome", $validatedData["nome"]);
            $preparePatch->bindValue(":valor", $validatedData["valor"]);
            $preparePatch->bindValue(":tipo", $validatedData["tipo"]);
            $preparePatch->bindValue(":id", $id);
            $preparePatch->execute();

            if ($preparePatch->rowCount() > 0) {
                http_response_code(200);
                return array("status" => "success", "message" => "Informações do Produto atualizadas com sucesso!");
            }

            http_response_code(400);
            return array("status" => "error", "message" => "Alguma das suas informações está errada ou o objeto não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function create($validatedData)
    {
        try {
            $sql = "INSERT INTO produto (tipo, nome, valor) VALUES (:tipo, :nome, :valor)";
            $prepareInsert = $this->connection->prepare($sql);
            $prepareInsert->bindValue(":tipo", $validatedData["tipo"]);
            $prepareInsert->bindValue(":nome", $validatedData["nome"]);
            $prepareInsert->bindValue(":valor", $validatedData["valor"]);
            $prepareInsert->execute();

            if ($prepareInsert->rowCount() > 0) {
                http_response_code(201);
                return array("status" => "success", "message" => "Produto inserido com sucesso!");
            }

            http_response_code(500);
            return array("status" => "error", "message" => "Falha ao inserir o produto!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }
}
