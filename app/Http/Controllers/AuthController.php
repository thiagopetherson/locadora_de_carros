<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login (Request $request) {

        $credenciais = $request->all(['email','password']);

        // Autenticação (email e senha)
        // O método attempt realiza uma tentativa de autenticação
        // Se a autenticação der certo, o método attempt retornará um token
        // Se a autenticação der errado, retornará false
        $token = auth('api')->attempt($credenciais);

        // Verificando a autenticação
        if ($token) {
            return response()->json(['token' => $token], 200);
        } else {
            // Erro 403 é o erro Forbidden. Um potencial login inválido (o 401 é o não autorizado)
            return response()->json(['erro' => 'Usuário ou Senha inválidos'], 403);
        }
    }

    public function logout () {
        auth('api')->logout(); // Método de fazer o logout
        return response()->json(['msg' => "Logout efetuado com sucesso!"], 200);
    }

    public function refresh () {

        // Para o método refresh funcionar, é obrigatório que o client envie em token jwt na requisição
        $token = auth('api')->refresh();
        return response()->json(['token' => $token], 200);
    }

    public function me () {
        return response()->json(auth()->user());
    }

}
