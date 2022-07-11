<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Modelo;
use Illuminate\Http\Request;
use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{

    public function __construct(Modelo $modelo) {
        $this->modelo = $modelo;
    }


    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        //É possivel passar por parametro somente o atributos_modelos=campo ou atributos_modelos=campo e atributos=campo
        if($request->has('atributos_marca')) {//has() verifica se existe $request atributos_modelos (parametro passado no postman) 
            $atributos_marca = 'marca:id,'.$request->atributos_marca;            
            $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
        } else {
            $modeloRepository->selectAtributosRegistrosRelacionados('marca');
        }

        if ($request->has('filtro')) {
            $modeloRepository->filtro($request->filtro);
         }

            //É possivel passar por parametro somente o atributos=campo ou atributos_marca=campo e atributos_modelos=campo
        if($request->has('atributos')) {//has() verifica se existe $request atributos (parametro passado no postman) 
            $modeloRepository->selectAtributos($request->atributos); 
        } 

        //$this->modelo->with('marca')->get()
        return response()->json($modeloRepository->getResultado(), 200);
        //all() -> criando um obj de consulta + get() = collection
        //get() -> modificar a consulta -> collection
    }


    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());
        $image = $request->file('imagem');                
        $imagem_urn = $image->store('imagens/modelos', 'public'); //caminho storage/app/public->cria a pasta imagens -> 'public' é um dos caminhos opcionais do arquivo config/filesystems.php (public,local ou s3)      
        
        $modelo = $this->modelo->create([
                'marca_id'=>$request->marca_id,
                'nome'=>$request->nome,
                'imagem'=>$imagem_urn,
                'numero_portas'=>$request->numero_portas,
                'lugares'=>$request->lugares,
                'air_bag'=>$request->air_bag,
                'abs'=>$request->abs
        ]);
        return response()->json($modelo, 201);
    }


    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if ($modelo === null) {
            return response()->json(['erro'=>'Recurso pesquisado não existe'],404);
        } 
        return response()->json($modelo, 200);
    }



    public function update(Request $request, $id)
    {
        $modelo = $this->modelo->find($id);
        if($modelo === null) {
            return response()->json(['erro' => 'Impossível realizar a atualização. O recurso solicitado não existe'], 404);
        }
        if($request->method() === 'PATCH') {
            $regrasDinamicas = array();
            //percorrendo todas as regras definidas no Model
            foreach($modelo->rules() as $input => $regra) {
                //coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }
            $request->validate($regrasDinamicas);
        } else {
            $request->validate($modelo->rules());
        }

        //remove o arquivo antigo caso tenha sido enviado um novo arquivo no request
        if($request->file('imagem')){
            Storage::disk('public')->delete($modelo->imagem);
        }        

        $image = $request->file('imagem');                
        $imagem_urn = $image->store('imagens/modelos', 'public'); //caminho storage/app/public->cria a pasta imagens -> 'public' é um dos caminhos opcionais do arquivo config/filesystems.php (public,local ou s3)      
        
        $modelo->update([
            'marca_id'=>$request->marca_id,
            'nome'=>$request->nome,
            'imagem'=>$imagem_urn,
            'numero_portas'=>$request->numero_portas,
            'lugares'=>$request->lugares,
            'air_bag'=>$request->air_bag,
            'abs'=>$request->abs
        ]);
        return response()->json($modelo, 200);
    }

    
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);
        if ($modelo === null) {
            return response()->json(['erro'=>'Não foi possível excluir. Recurso solicitado não existe'],404);
        }
        
        //remove o arquivo antigo
        Storage::disk('public')->delete($modelo->imagem);
        $modelo->delete();
        return response()->json(['msg' => 'A modelo foi removida com sucesso!'], 200);
    }
}
