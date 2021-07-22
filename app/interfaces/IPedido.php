<?php
namespace App\Interface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface IPedido
{
	public function Alta(Request $request, Response $response, Array $args): Response;
	public function TraerUnoRequest(Request $request, Response $response, Array $args): Response;
	public function TraerTodos(Request $request, Response $response, Array $args): Response;
	public function CancelarPedido(Request $request, Response $response, Array $args): Response;
	public function TomarPedido(Request $request, Response $response, Array $args): Response;
	public function PedidoListo(Request $request, Response $response, Array $args): Response;
	public function EntregarPedido(Request $request, Response $response, Array $args): Response;
	public function AbonarPedido(Request $request, Response $response, Array $args): Response;
}
