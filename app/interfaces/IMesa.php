<?php
namespace App\Interfaces;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface IMesa
{
	public function Alta(Request $request, Response $response, Array $args): Response;
	public function TraerUno(Request $request, Response $response, Array $args): Response;
	public function TraerTodos(Request $request, Response $response, Array $args): Response;
	public function BorrarUno(Request $request, Response $response, Array $args): Response;
	public function CerrarMesa(Request $request, Response $response, Array $args): Response;

}