<?php

require_once "Configurations/Connection.php";

class VendaModel extends Connection
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id)
    {
        try {
            $sql = "SELECT * FROM pedido WHERE pedido.codigo = :codigo";
            $prepareVenda = $this->connection->prepare($sql);
            $prepareVenda->bindValue(":codigo", $id);
            $prepareVenda->execute();

            if ($prepareVenda->rowCount() == 1) {
                $venda = $prepareVenda->fetch(PDO::FETCH_OBJ);

                $itens = $this->connection->query("SELECT * FROM produto_pedido WHERE produto_pedido.pedido = $venda->codigo")->fetchAll(PDO::FETCH_OBJ);

                foreach ($itens as $item) {
                    $produto = $this->connection->query("SELECT * FROM produto WHERE produto.codigo = {$item->produto}")->fetch(PDO::FETCH_OBJ);
                    $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = {$produto->tipo}")->fetch(PDO::FETCH_OBJ);

                    $produto->tipo = $tipo;
                    $item->produto = $produto;
                };

                $venda->itens = $itens;
                return array("status" => "success", "data" => $venda);
            }

            http_response_code(404);
            return array("status" => "Error", "message" => "Objeto solicitado não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "Error", "message" => "Algo de errado aconteceu, desculpe!");
        }
    }

    public function create($validatedData)
    {
        try {
            $this->connection->beginTransaction();

            $datetime = date("Y-m-d H:i:s");
            $sqlNewVenda = $this->connection->prepare("INSERT INTO pedido (data, total) VALUES ('$datetime', :total)");
            $sqlNewVenda->bindValue(":total", $validatedData["total"]);
            $sqlNewVenda->execute();

            if ($sqlNewVenda->rowCount() == 1) {
                $idPedido = $this->connection->lastInsertId();

                foreach ($validatedData["itens"] as $item) {
                    $sqlItensPedido = "INSERT INTO produto_pedido (pedido, produto, quantidade, total) VALUES ($idPedido, :codigoProduto, :quantidade, :total)";
                    $prepareItensPedido = $this->connection->prepare($sqlItensPedido);
                    $prepareItensPedido->bindValue(":codigoProduto", $item->produto->codigo);
                    $prepareItensPedido->bindValue(":quantidade", $item->quantidade);
                    $prepareItensPedido->bindValue(":total", $item->total);

                    $prepareItensPedido->execute();
                }

                $this->connection->commit();

                http_response_code(201);
                return array("status" => "success", "message" => "Nova venda criada com sucesso!");
            }

            http_response_code(500);
            return array("status" => "error", "message" => "Falha ao inserir Nova Venda!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "error", "message" => "Algo falhou!");
        }
    }

    public function getAll()
    {
        try {
            $this->connection->beginTransaction();

            $prepareVendas = $this->connection->prepare("SELECT * FROM pedido");
            $prepareVendas->execute();
            $vendas = $prepareVendas->fetchAll(PDO::FETCH_OBJ);

            foreach ($vendas as $venda) {
                $prepareItensVenda = $this->connection->prepare("SELECT * FROM produto_pedido WHERE produto_pedido.pedido = :codigo");
                $prepareItensVenda->bindValue(":codigo", $venda->codigo);
                $prepareItensVenda->execute();

                $itensVenda = $prepareItensVenda->fetchAll(PDO::FETCH_OBJ);

                foreach ($itensVenda as $itemVenda) {
                    $produto = $this->connection->query("SELECT * FROM produto WHERE produto.codigo = $itemVenda->produto")->fetch(PDO::FETCH_OBJ);
                    $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = {$produto->tipo}")->fetch(PDO::FETCH_OBJ);

                    $produto->tipo = $tipo;
                    $itemVenda->produto = $produto;
                }

                $venda->itens = $itensVenda;
            }

            $this->connection->commit();

            http_response_code(200);
            return array("status" => "success", "data" => $vendas);
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "success", "message" => "Algo falhou!");
        }
    }
}
