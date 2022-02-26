<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Modelo; // Importando o Model Modelo


class Marca extends Model
{
    use HasFactory;
    protected $fillable = ['nome','imagem'];

    public function rules () {
        return [
            'nome'=>'required|unique:marcas,nome,'.$this->id.'|min:3',
            'imagem' => 'required|file'
        ];
    }

    public function feedback () {
        return  [
            'required' => 'O campo :attribute é obrigatório',
            'imagem.mimes' => 'O arquivo deve ser uma imagem do tipo png, jpg ou jpeg',
            'nome.unique' => 'O nome da marca já existe',
        ];
    }

    // Uma marca POSSUI MUITOS modelos
    public function modelos () {
        return $this->hasMany(Modelo::class);
    }
}
