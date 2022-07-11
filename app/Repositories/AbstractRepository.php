<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository {
    
    public function __construct(Model $model){
        $this->model = $model;
    } 

    public function selectAtributosRegistrosRelacionados($atributos){
        $this->model = $this->model->with($atributos);
        //a query está sendo montada, por isso é necessário sempre atualizar o atributo $this->model
    }

    public function filtro($filtros){
         //dd(explode(':', $request->filtro)); //exibe o array
         $condicoes = explode(':', $filtros);
         $this->model = $this->model->where($condicoes[0],$condicoes[1],$condicoes[2]);
         //a query está sendo montada, por isso é necessário sempre atualizar o atributo $this->model
    }

    public function selectAtributos($atributos){
        $this->model = $this->model ->selectRaw($atributos);
    }

    public function getResultado(){
        return $this->model->get();
    }
} 

?>