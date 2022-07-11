<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use App\Repositories\CarroRepository;
use Illuminate\Http\Request;

class CarroController extends Controller
{
    public function __construct(Carro $carro) {
        $this->carro = $carro;
    }

    public function index(Request $request)
    {
        $carroRepository = new CarroRepository($this->carro);
        
        if($request->has('atributos_modelo')) {
            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;            
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        } else {
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');
        }

        if ($request->has('filtro')) {
            $carroRepository->filtro($request->filtro);
         }

            
        if($request->has('atributos')) {
            $carroRepository->selectAtributos($request->atributos); 
        } 

        //$this->modelo->with('carro')->get()
        return response()->json($carroRepository->getResultado(), 200);
        //all() -> criando um obj de consulta + get() = collection
        //get() -> modificar a consulta -> collection
    }



    public function store(Request $request)
    {        
        //$carro = Carro::create($request->all());
        $request->validate($this->carro->rules());
        
        $carro = $this->carro->create([
                'modelo_id'=>$request->modelo_id,
                'placa'=>$request->placa,
                'disponivel'=>$request->disponivel,
                'km'=>$request->km                
        ]);
        return response()->json($carro, 201);
    }


    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);
        if ($carro === null) {
            return response()->json(['erro'=>'Recurso pesquisado não existe'],404);
        } 
        return response()->json($carro, 200);
    }



    public function update(Request $request, $id)
    {
        $carro = $this->carro->find($id);
        
        if($carro === null) {
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }
        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();
            //percorrendo todas as regras definidas no Model
            foreach($carro->rules() as $input => $regra) {
                //coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }
            $request->validate($regrasDinamicas);
        } else {
            $request->validate($carro->rules());
        }

      
        $carro->update([
            'modelo_id'=>$request->modelo_id,
            'placa'=>$request->placa,
            'disponivel'=>$request->disponivel,
            'km'=>$request->km                
        ]);
        return response()->json($carro, 200);
    }


    public function destroy($id)
    {
        $carro = $this->carro->find($id);
        if ($carro === null) {
            return response()->json(['erro'=>'Não foi possível excluir. Recurso solicitado não existe'],404);
        }
        $carro->delete();
        return response()->json(['msg' => 'O carro foi removido com sucesso!'], 200);
        
    }
}
