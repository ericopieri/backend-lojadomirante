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
            $prepareVenda->bindValue(":codigo", strip_tags($id));
            $prepareVenda->execute();

            if ($prepareVenda->rowCount() == 1) {
                $venda = $prepareVenda->fetch(PDO::FETCH_OBJ);

                $itens = $this->connection->query(
                    "SELECT pp.codigo, pp.quantidade, pp.total, pr.codigo as produto_codigo, pr.nome as produto_nome, pr.valor as produto_valor, pr.deletadoEm as produto_deletadoEm, tp.nome as produto_tipo_nome, tp.percentual_imposto, tp.codigo as produto_tipo_codigo
                    FROM produto_pedido pp
                    INNER JOIN produto pr ON pp.produto = pr.codigo
                    INNER JOIN tipo_produto tp on pr.tipo = tp.codigo
                    WHERE pp.pedido = " . strip_tags($id)
                )->fetchAll(PDO::FETCH_OBJ);

                // metodo antigo
                // foreach ($itens as $item) {
                //     $produto = $this->connection->query(
                //         "SELECT
                //             pr.*,
                //             tp.nome as tipo_nome, tp.codigo as tipo_codigo, tp.percentual_imposto
                //         FROM produto pr INNER JOIN tipo_produto tp ON tp.codigo = pr.tipo
                //         WHERE pr.codigo = $item->produto"
                //     )->fetch(PDO::FETCH_OBJ);

                //     $item->produto = $produto;
                // };

                $venda->itens = $itens;
                return array("status" => "success", "data" => $venda);
            }

            http_response_code(404);
            return array("status" => "Error", "message" => "Objeto solicitado não existe!");
        } catch (PDOException $err) {
            http_response_code(500);
            return array("status" => "Error", "message" => $err->getMessage());
            return array("status" => "Error", "message" => "Algo de errado aconteceu, desculpe!");
        }
    }

    public function create($validatedData)
    {
        try {
            $this->connection->beginTransaction();

            $sqlNewVenda = $this->connection->prepare("INSERT INTO pedido (data, total) VALUES (NOW(), :total)");
            $sqlNewVenda->bindValue(":total", $validatedData["total"]);
            $sqlNewVenda->execute();

            if ($sqlNewVenda->rowCount() == 1) {
                $idPedido = $this->connection->lastInsertId();

                foreach ($validatedData["itens"] as $item) {
                    $sqlItensPedido = "INSERT INTO produto_pedido (pedido, produto, quantidade, total) VALUES ($idPedido, :codigoProduto, :quantidade, :total)";
                    $prepareItensPedido = $this->connection->prepare($sqlItensPedido);
                    $prepareItensPedido->bindValue(":codigoProduto", strip_tags($item->produto->codigo));
                    $prepareItensPedido->bindValue(":quantidade", strip_tags($item->quantidade));
                    $prepareItensPedido->bindValue(":total", strip_tags($item->total));

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

            $sqlVendas = $this->connection->prepare("SELECT * FROM pedido");
            $sqlVendas->execute();
            $vendas = $sqlVendas->fetchAll(PDO::FETCH_OBJ);

            foreach ($vendas as $venda) {

                // metodo antigo
                // $prepareItensVenda = $this->connection->prepare("SELECT * FROM produto_pedido WHERE produto_pedido.pedido = :codigo");
                // $prepareItensVenda->bindValue(":codigo", $venda->codigo);
                // $prepareItensVenda->execute();

                // $itensVenda = $prepareItensVenda->fetchAll(PDO::FETCH_OBJ);

                // foreach ($itensVenda as $itemVenda) {
                //     $produto = $this->connection->query("SELECT * FROM produto WHERE produto.codigo = $itemVenda->produto")->fetch(PDO::FETCH_OBJ);
                //     $tipo = $this->connection->query("SELECT * FROM tipo_produto WHERE tipo_produto.codigo = {$produto->tipo}")->fetch(PDO::FETCH_OBJ);

                //     $produto->tipo = $tipo;
                //     $itemVenda->produto = $produto;
                // }

                $itens = $this->connection->query(
                    "SELECT pp.codigo, pp.quantidade, pp.total, pr.codigo as produto_codigo, pr.nome as produto_nome, pr.valor as produto_valor, pr.deletadoEm as produto_deletadoEm, tp.nome as produto_tipo_nome, tp.percentual_imposto, tp.codigo as produto_tipo_codigo
                    FROM produto_pedido pp
                    INNER JOIN produto pr ON pp.produto = pr.codigo
                    INNER JOIN tipo_produto tp on pr.tipo = tp.codigo
                    WHERE pp.pedido = $venda->codigo"
                )->fetchAll(PDO::FETCH_OBJ);

                $venda->itens = $itens;
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
