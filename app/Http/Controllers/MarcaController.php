<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Marca;
use Illuminate\Http\Request;
use App\Repositories\MarcaRepository;

class MarcaController extends Controller
{

    protected $marca;

    //Injeção de Dependência
    public function __construct(Marca $marca){
        $this->marca = $marca;
    }

    public function index(Request $request)
    {
        $marcaRepository = new MarcaRepository($this->marca);

        if($request->has('atributos_modelos')) {
            $atributos_modelos = 'modelos:id,'.$request->atributos_modelos;
            $marcaRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        } else {
            $marcaRepository->selectAtributosRegistrosRelacionados('modelos');
        }

        if($request->has('filtro')) {
            $marcaRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $marcaRepository->selectAtributos($request->atributos);
        }

        return response()->json($marcaRepository->getResultadoPaginado(3), 200);
    }

    public function store(Request $request)
    {
        $request->validate($this->marca->rules(), $this->marca->feedback());

        $image = $request->file('imagem');
        $imagem_urn = $image->store('imagens','public'); // O retorno desse método é o nome do arquivo que foi salvo

        $marca = $this->marca->create([
            'nome' => $request->nome,
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca, 200);
    }

    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);
        if ($marca === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'], 404);
        }
        return response()->json($marca, 200);
    }

    public function update(Request $request, $id)
    {
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'],404);
        }

        if ($request->method() === "PATCH") {

            $regrasDinamicas = array();

            //Percorrendo todas as regras definidas no Model
            foreach($marca->rules() as $input => $regra) {

                // Coletar apenas as regras aplicáveis aos parâmetros parciais da requisição PATCH
                if(array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas, $marca->feedback());
        }
        else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        //preenchendo o objeto $marca com todos os dados do request
        $marca->fill($request->all());

        //se a imagem foi encaminhada na requisição
        if($request->file('imagem')) {
            //remove o arquivo antigo
            Storage::disk('public')->delete($marca->imagem);

            $imagem = $request->file('imagem');
            $imagem_urn = $imagem->store('imagens', 'public');
            $marca->imagem = $imagem_urn;
        }

        $marca->save();
        return response()->json($marca, 200);
    }

    public function destroy($id)
    {
        $marca = $this->marca->find($id);
        if ($marca === null) {
            return response()->json(['mensagem' => 'Recurso Pesquisado não existe'],404);
        }

        // Remove um arquivo antigo, caso o registro tenha imagem
        Storage::disk('public')->delete($marca->imagem); // Passando a url do arquivo que será apagado (está no campo do banco)

        $marca->delete();
        return response()->json(['mensagem' => 'Marca removida com sucesso'],404);
    }
}
