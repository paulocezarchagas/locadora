<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carro extends Model
{
    use HasFactory;

    protected $fillable = ['modelo_id','placa','disponivel','km'];

     /*    Após o 'unique:'
        1) tabela
        2) nome da coluna que será pesquisada na tabela3
        3) id do registro que será desconsiderado na pesquisa
    */
    public function rules(){
        return [
            'modelo_id'=>'exists:modelos,id',
            'placa'=>'required',
            'disponivel'=>'required'
            //'km'=>'required'            
        ];
    }

    public function modelo(){
        return $this->belongsTo('\App\Models\Modelo');
    }

}
