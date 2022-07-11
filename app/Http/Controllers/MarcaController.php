<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use App\Repositories\MarcaRepository;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function __construct(Marca $marca) {
        $this->marca = $marca;
    }

    public function index(Request $request)
    {
        $marcaRepository = new MarcaRepository($this->marca);

        //É possivel passar por parametro somente o atributos_marca=campo ou atributos_marca=campo e atributos=campo
        if($request->has('atributos_modelo')) {//has() verifica se existe $request atributos_marca (parametro passado no postman) 
            $atributos_modelo = 'modelo:id,'.$request->atributos_modelo;            
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelo);
        } else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelo');
        }

        if ($request->has('filtro')) {
            $marcaRepository->filtro($request->filtro);
         }

            //É possivel passar por parametro somente o atributos=campo ou atributos_marca=campo e atributos_marca=campo
        if($request->has('atributos')) {//has() verifica se existe $request atributos (parametro passado no postman) 
            $marcaRepository->selectAtributos($request->atributos); 
        } 

        //$this->modelo->with('marca')->get()
        return response()->json($marcaRepository->getResultado(), 200);
        //all() -> criando um obj de consulta + get() = collection
        //get() -> modificar a consulta -> collection
    }



    public function store(Request $request)
    {        
        //$marca = Marca::create($request->all());
        $request->validate($this->marca->rules(), $this->marca->feedback());
        $image = $request->file('imagem');                
        $imagem_urn = $image->store('imagens', 'public'); //caminho storage/app/public->cria a pasta imagens -> 'public' é um dos caminhos opcionais do arquivo config/filesystems.php (public,local ou s3)      
        
        $marca = $this->marca->create(
            [
                'nome'=>$request->nome,
                'imagem'=>$imagem_urn
        ]);
        return response()->json($marca, 201);
    }


    public function show($id)
    {        
        $marca = $this->marca->with('modelo')->find($id);
        if ($marca === null) {
            return response()->json(['erro'=>'Recurso pesquisado não existe'],404);
        } 
        return response()->json($marca, 200);
    }



    public function update(Request $request, $id)
    {
        $marca = $this->marca->find($id);
        if($marca === null) {
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }
        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();
            //percorrendo todas as regras definidas no Model
            foreach($marca->rules() as $input => $regra) {
                //coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }
            $request->validate($regrasDinamicas, $marca->feedback());
        } else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        //remove o arquivo antigo caso tenha sido enviado um novo arquivo no request
        if($request->file('imagem')){
            Storage::disk('public')->delete($marca->imagem);
        }        

        $image = $request->file('imagem');                
        $imagem_urn = $image->store('imagens', 'public'); //caminho storage/app/public->cria a pasta imagens -> 'public' é um dos caminhos opcionais do arquivo config/filesystems.php (public,local ou s3)      
        
        $marca->update([
                'nome'=>$request->nome,
                'imagem'=>$imagem_urn
        ]);
        return response()->json($marca, 200);
    }


    public function destroy($id)
    {
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(['erro'=>'Não foi possível excluir. Recurso solicitado não existe'],404);
        }
        
        //remove o arquivo antigo
            Storage::disk('public')->delete($marca->imagem);
        $marca->delete();
        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
        
    }
}
