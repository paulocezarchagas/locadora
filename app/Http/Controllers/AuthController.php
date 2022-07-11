<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){

        //autenticação email e senha
        $credenciais = $request->all(['email', 'password']);
        $token = auth('api')->attempt($credenciais);

        if($token){//usuario autenticado com sucesso
            return response()->json(['token'=>$token], 200);
        }else{//erro de credenciais
            return response()->json(['erro'=>'Usuário ou senha inválido'], 403); 
            
            //403 = forbidden -> proibido (login invalido)
            //401 = unauthorized -> não autorizado (pode estar logado, mas nao tem autorização de acesso)
        }

        
    }

    public function refresh(){
        $token = auth('api')->refresh();//necessário que seja encaminhado um token valido
        return response()->json(['token' => $token]);
    }

    public function me(){
        //dd(auth()->user());
        return response()->json(auth()->user());        
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['msg'=>'Logout foi realizado com sucesso!']); 
    }
}
