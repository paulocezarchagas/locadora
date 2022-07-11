<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $fillable = ['nome','imagem'];

     /*    Após o 'unique:'
        1) tabela
        2) nome da coluna que será pesquisada na tabela3
        3) id do registro que será desconsiderado na pesquisa
    */
    public function rules(){
        return [
            'nome'=>'required|unique:marcas,nome,'.$this->id.'|min:3', //obrigatório|unico:dentro da talela marcas,nome do campo a ser ignorado(para update)|minimo 3 caracteres
            'imagem'=>'required|file|mimes:png'
        ];
    }

    public function feedback(){
        return [
            'required'=>'O campo :attribute é obrigatório',
            'nome.unique'=>'O nome da marca já existe',
            'nome.min'=>'O nome deve ter no mínimo 3 caracteres',
            'imagem.mimes'=>'O arquivo deve ser uma imagem do tipo .png'
        ];
    }

    public function modelos(){
        //UMA marca POSSUI MUITOS modelos
        return $this->hasMany('App\Models\Modelo');
    }
}
