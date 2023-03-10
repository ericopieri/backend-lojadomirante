<?php
require_once "Configurations/Connection.php";

class ProdutoModel extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function delete($codigo)
    {
        try {
            $sql = "UPDATE produto SET deletadoEm = NOW() WHERE produto.codigo = :codigo";
            $prepareDelete = $this->connection->prepare($sql);
            $prepareDelete->bindValue(":codigo", $codigo);
            $prepareDelete->execute();

            if ($prepareDelete->rowCount() > 0) {
                http_response_code(200);
                return array("status" => "success", "message" => "Produto deletado com sucesso!");
            }

            http_response_code(400);
            return array("status" => "error", "message" => "Produto não encontrado para deletar!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function get($id)
    {
        try {
            $sql = "SELECT pr.*, tp.nome as tipo_nome, tp.percentual_imposto, tp.codigo as tipo_codigo FROM produto pr INNER JOIN tipo_produto tp ON pr.tipo = tp.codigo WHERE pr.codigo = :id";
            $prepareProduto = $this->connection->prepare($sql);
            $prepareProduto->bindValue(":id", $id);
            $prepareProduto->execute();

            if ($prepareProduto->rowCount() > 0) {
                $produto = $prepareProduto->fetch(PDO::FETCH_OBJ);

                // metodo antigo, pesado
                // $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = $produto->tipo")->fetch(PDO::FETCH_OBJ);

                // $produto->tipo = $tipo;

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
            $produtos = $this->connection->query("SELECT pr.*, tp.nome as tipo_nome, tp.codigo as tipo_codigo, tp.percentual_imposto FROM produto pr INNER JOIN tipo_produto tp ON tp.codigo = pr.tipo WHERE pr.deletadoEm IS NULL")->fetchAll(PDO::FETCH_OBJ);

            // foreach ($produtos as $produto) { // primeira lógica, pesada
            //     $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = $produto->tipo")->fetch(PDO::FETCH_OBJ);
            //     $produto->tipo = $tipo;
            // }

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
            $prepareUpdate = $this->connection->prepare($sql);
            $prepareUpdate->bindValue(":nome", $validatedData["nome"]);
            $prepareUpdate->bindValue(":valor", $validatedData["valor"]);
            $prepareUpdate->bindValue(":tipo", $validatedData["tipo"]);
            $prepareUpdate->bindValue(":id", $id);
            $prepareUpdate->execute();

            if ($prepareUpdate->rowCount() > 0) {
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
