<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ModeloRepository;

class ModeloController extends Controller
{

    protected $modelo;

    //Injeção de Dependência
    public function __construct(Modelo $modelo){
        $this->modelo = $modelo;
    }

    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        if($request->has('atributos_marca')) {
            $atributos_marca = 'marca:id,'.$request->atributos_marca;
            $modeloRepository->selectAtributosRegistrosRelacionados($atributos_marca);
        } else {
            $modeloRepository->selectAtributosRegistrosRelacionados('marca');
        }

        if($request->has('filtro')) {
            $modeloRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $modeloRepository->selectAtributos($request->atributos);
        }

        return response()->json($modeloRepository->getResultado(), 200);
    }

    public function store(Request $request)
    {
        $request->validate($this->modelo->rules());

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens/modelos','public'); // O retorno desse método é o nome do arquivo que foi salvo

        $modelo = $this->modelo->create([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' =>$request->abs
        ]);

        return response()->json($modelo, 200);
    }

    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);
        if ($modelo === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'], 404);
        }
        return response()->json($modelo, 200);
    }


    public function update(Request $request, $id)
    {
        $modelo = $this->modelo->find($id);
        if ($modelo === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'],404);
        }

        if ($request->method() === "PATCH") {

            $regrasDinamicas = array();

            //Percorrendo todas as regras definidas no Model
            foreach($modelo->rules() as $input => $regra) {

                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);
        }
        else {
            $request->validate($modelo->rules());
        }

        // Remove um arquivo antigo, caso tenha sido enviado uma imagem na requisição
        if ($request->file('imagem')) {
            Storage::disk('public')->delete($modelo->imagem); // Passando a url do arquivo que será apagado (está no campo do banco)
        }

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens/modelos','public'); // O retorno desse método é o nome do arquivo que foi salvo
        /*
        $modelo = $modelo->update([
            'marca_id' => $request->marca_id,
            'nome' => $request->nome,
            'imagem' => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares' => $request->lugares,
            'air_bag' => $request->air_bag,
            'abs' =>$request->abs
        ]);
        */

        // Preencher o objeto modelo com os dados do request
        $modelo->fill($request->all());
        if ($imagem_urn) {
            $modelo->imagem = $imagem_urn;
        }
        $modelo->save();

        return response()->json($modelo, 200);
    }

    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);
        if ($modelo === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'],404);
        }

        // Remove um arquivo antigo, caso o registro tenha imagem
        Storage::disk('public')->delete($modelo->imagem); // Passando a url do arquivo que será apagado (está no campo do banco)

        $modelo->delete();
        return response()->json(['mensagem' => 'O modelo foi removido com sucesso'],404);
    }
}
