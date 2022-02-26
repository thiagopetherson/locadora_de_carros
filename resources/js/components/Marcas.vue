<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <!-- Início do Card de Busca -->
                <card-component titulo="Busca de Marcas">
                    <template v-slot:conteudo>
                        <div class="form-row">
                            <div class="col mb-3">
                                <input-container-component titulo="ID" id="inputId" id-help="idHelp" texto-ajuda="Opcional. Informe o ID do registro">
                                    <input type="number" v-model="busca.id" class="form-control" id="inputId" aria-describedby="idHelp" placeholder="ID">
                                </input-container-component>
                            </div>
                            <div class="col mb-3">
                                <input-container-component titulo="Nome da Marca" id="inputNome" id-help="nomeHelp" texto-ajuda="Opcional. Informe o Nome da Marca">
                                    <input type="text" v-model="busca.nome" class="form-control" id="inputNome" aria-describedby="nomeHelp" placeholder="Nome da Marca">
                                </input-container-component>
                            </div>
                        </div>
                    </template>

                    <template v-slot:rodape>
                        <button type="submit" class="btn btn-primary btn-sm float-right" @click="pesquisar()">Pesquisar</button>
                    </template>
                </card-component>
                <!-- Fim do Card de Busca -->

                <!-- Início do Card de Listagem de Marcas -->
                <card-component titulo="Relação de Marcas">
                    <template v-slot:conteudo>
                        <table-component :dados="marcas.data"
                        :visualizar="{ visivel: true, dataToggle: 'modal', dataTarget: '#modalMarcaVisualizar' }"
                         :atualizar="{ visivel: true, dataToggle: 'modal', dataTarget: '#modalMarcaAtualizar' }"
                         :remover="{ visivel: true, dataToggle: 'modal', dataTarget: '#modalMarcaRemover' }"
                         :titulos="{
                            id: {titulo: 'ID', tipo: 'texto'},
                            nome: {titulo: 'Nome', tipo: 'texto'},
                            imagem: {titulo: 'Imagem', tipo: 'imagem'},
                            created_at: {titulo: 'Data da Criação', tipo: 'data'},
                        }">
                        </table-component>
                    </template>

                    <template v-slot:rodape>
                        <div class="row">
                            <div class="col-10">
                                <paginate-component>
                                    <li v-for="l, key in marcas.links" :key="key" class="page-item"
                                        @click="paginacao(l)" :class="l.active ? 'page-item active' : 'page-item'">
                                        <a class="page-link" v-html="l.label"></a>
                                    </li>
                                </paginate-component>
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalMarca">
                                    Adicionar
                                </button>
                            </div>

                        </div>
                    </template>
                </card-component>
                <!-- Fim do Card de Listagem de Marcas -->

            </div>
        </div>

        <!-- Início do modal de inclusão de marca -->
        <modal-component id="modalMarca" titulo="Adicionar Marca">

            <template v-slot:alertas>
                <alert-component tipo="success" :detalhes="transacoesDetalhes" titulo="Cadastro Realizado Com Sucesso" v-if="transacaoStatus == 'adicionado'"></alert-component>
                <alert-component tipo="danger" :detalhes="transacoesDetalhes" titulo="Erros ao Cadastrar a Marca" v-if="transacaoStatus == 'erro'"></alert-component>
            </template>

            <template v-slot:conteudo>
                <div class="form-group">
                    <input-container-component titulo="Nome da Marca" id="novoNome" id-help="novoNomeHelp" texto-ajuda="Informe o Nome da Marca">
                        <input type="text" class="form-control" id="novoNome" aria-describedby="novoNomeHelp" placeholder="Nome da Marca" v-model="nomeMarca">
                    </input-container-component>
                </div>

                <div class="form-group">
                    <input-container-component titulo="Imagem" id="novoImagem" id-help="novoImagemHelp" texto-ajuda="Selecione uma Imagem no Formato PNG">
                        <input type="file" class="form-control-file" id="novoImagem" aria-describedby="novoImagemHelp"
                               placeholder="Selecione uma Imagem" @change="carregarImagem($event)">
                    </input-container-component>
                </div>
            </template>

            <template v-slot:rodape>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" @click="salvar()">Salvar</button>
            </template>
        </modal-component>
        <!-- Final do modal de inclusão de marca -->

        <!-- Início do modal de visualização da marca -->
        <modal-component id="modalMarcaVisualizar" titulo="Visualizar Marca">
            <template v-slot:alertas></template>
            <template v-slot:conteudo>
                <input-container-component titulo="ID">
                    <input type="text" class="form-control" :value="$store.state.item.id" disabled />
                </input-container-component>
                <input-container-component titulo="Nome da Marca">
                    <input type="text" class="form-control" :value="$store.state.item.nome" disabled />
                </input-container-component>
                <input-container-component titulo="Imagem">
                    <img :src="'storage/'+$store.state.item.imagem" v-if="$store.state.item.imagem" />
                </input-container-component>
                <input-container-component titulo="Data de Criação">
                    <input type="text" class="form-control" :value="$store.state.item.created_at" disabled />
                </input-container-component>
            </template>
            <template v-slot:rodape>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </template>
        </modal-component>
        <!-- Final do modal de visualização da marca -->


        <!-- Início do modal de remoção da marca -->
        <modal-component id="modalMarcaRemover" titulo="Remover Marca">
            <template v-slot:alertas>
                <alert-component v-if="$store.state.transacao.status == 'sucesso'" tipo="success" titulo="Transação realizada com sucesso"
                                 :detalhes="$store.state.transacao">
                </alert-component>
                <alert-component v-if="$store.state.transacao.status == 'erro'" tipo="danger" titulo="Erro ao realizar a transação"
                                 :detalhes="$store.state.transacao">
                </alert-component>
            </template>
            <template v-slot:conteudo v-if="$store.state.transacao.status != 'sucesso'">
                <input-container-component titulo="ID">
                    <input type="text" class="form-control" :value="$store.state.item.id" disabled />
                </input-container-component>
                <input-container-component titulo="Nome da Marca">
                    <input type="text" class="form-control" :value="$store.state.item.nome" disabled />
                </input-container-component>
            </template>
            <template v-slot:rodape>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-secondary" @click="remover()" v-if="$store.state.transacao.status != 'sucesso'">remover</button>
            </template>
        </modal-component>
        <!-- Final do modal de remoção da marca -->

        <!-- Início do modal de atualização de marca -->
        <modal-component id="modalMarcaAtualizar" titulo="Atualizar Marca">

            <template v-slot:alertas>
                <alert-component v-if="$store.state.transacao.status == 'sucesso'" tipo="success" titulo="Transação realizada com sucesso"
                                 :detalhes="$store.state.transacao">
                </alert-component>
                <alert-component v-if="$store.state.transacao.status == 'erro'" tipo="danger" titulo="Erro ao realizar a transação"
                                 :detalhes="$store.state.transacao">
                </alert-component>
            </template>

            <template v-slot:conteudo>
                <div class="form-group">
                    <input-container-component titulo="Nome da Marca" id="atualizarNome" id-help="atualizarNomeHelp" texto-ajuda="Informe o Nome da Marca">
                        <input type="text" class="form-control" id="atualizarNome"
                               aria-describedby="atualizarNomeHelp" placeholder="Nome da Marca" v-model="$store.state.item.nome">
                    </input-container-component>
                </div>

                <div class="form-group">
                    <input-container-component titulo="Imagem" id="atualizarImagem" id-help="atualizarImagemHelp" texto-ajuda="Selecione uma Imagem no Formato PNG">
                        <input type="file" class="form-control-file" id="atualizarImagem" aria-describedby="atualizarImagemHelp"
                               placeholder="Selecione uma Imagem" @change="carregarImagem($event)">
                    </input-container-component>
                </div>

            </template>

            <template v-slot:rodape>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" @click="atualizar()">Atualizar</button>
            </template>
        </modal-component>
        <!-- Final do modal de atualização de marca -->

    </div>
</template>

<script>
    export default {
        data () {
            return {
                urlBase: 'http://127.0.0.1:8000/api/v1/marca',
                urlPaginacao: '',
                urlFiltro: '',
                nomeMarca: '',
                arquivoImagem: [],
                transacaoStatus: '', // Define o tipo de alerta que será mostrado
                transacoesDetalhes: {},
                marcas: { data: [] }, // Usamos o objeto e o array dessa forma por causa da listagem assincrona da table
                busca: { id: '', nome: '' }
            }
        },
        methods: {
            atualizar () {

                let formData = new FormData()
                formData.append('_method', 'patch')
                formData.append('nome', this.$store.state.item.nome)

                if (this.arquivoImagem[0]) {
                    formData.append('imagem', this.arquivoImagem[0])
                }

                let config = {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }

                let url = this.urlBase + '/' + this.$store.state.item.id

                axios.post(url, formData, config)
                    .then(response => {
                        this.$store.state.transacao.status = "sucesso"
                        this.$store.state.transacao.mensagem = 'Registro de marca atualizado com sucesso'
                        atualizarImagem.value = "" // Limpar o campo de seleção de arquivos
                        this.carregarLista()
                    })
                    .catch(errors => {
                        this.$store.state.transacao.status = 'erro'
                        this.$store.state.transacao.mensagem = errors.response.data.message
                        this.$store.state.transacao.dados = errors.response.data.errors

                        console.log('Erro de atualização'. errors.response)
                    })
            },
            pesquisar () {

                let filtro = ''

                for (let chave in this.busca) {

                    if (this.busca[chave]) {
                        if (filtro != '') {
                            filtro += ';'
                        }

                        filtro += chave + ':like:' + this.busca[chave]
                    }
                }

                if(filtro != '') {
                    this.urlPaginacao = 'page=1'
                    this.urlFiltro = '&filtro='+filtro
                } else {
                    this.urlFiltro = ''
                }

                this.carregarLista()
            },
            paginacao (l) {
                if (l.url) {
                    //this.urlBase = l.url // Ajustando a url com o parâmetro de página
                    this.urlPaginacao = l.url.split('?')[1] // Pegando só a página
                    this.carregarLista() // Requisitando novamente os dados para nossa API
                }
            },
            carregarLista () {

                let url = this.urlBase + '?' + this.urlPaginacao + this.urlFiltro

                axios.get(url)
                .then(response => {
                    this.marcas = response.data
                })
                .catch(errors => {
                    console.log(errors)
                })
            },
            carregarImagem (e) {
                this.arquivoImagem = e.target.files
            },
            salvar () {

                let formData = new FormData()
                formData.append('nome',this.nomeMarca)
                formData.append('imagem',this.arquivoImagem[0])

                let config = {
                    headers: {
                        'Content-Type': 'multipart/form-data', // Conteúdo da requisição
                    }
                }

                axios.post(this.urlBase, formData, config)
                .then(response => {
                    this.transacaoStatus = 'adicionado'
                    this.transacoesDetalhes = {
                        mensagem: "ID do Registro: " + response.data.id
                    }
                    console.log(response)
                })
                .catch(errors => {
                    this.transacaoStatus = 'erro'
                    this.transacoesDetalhes = {
                        mensagem: errors.response.data.message,
                        dados: errors.response.data.errors
                    }
                    // errors.response.data.message
                })
            },
            remover () {
                if(!confirm('Tem certeza que deseja remover esse registro ?'))
                    return false;

                let formData = new FormData()
                formData.append('_method', 'delete')

                let url = this.urlBase + '/' + this.$store.state.item.id

                axios.post(url, formData)
                    .then(response => {
                        this.$store.state.transacao.status = "sucesso"
                        this.$store.state.transacao.mensagem = response.data.msg
                        this.carregarLista()
                    })
                    .catch(errors => {
                        this.$store.state.transacao.status = "erro"
                        this.$store.state.transacao.mensagem = errors.response.data.erro
                    })
            }
        },
        mounted () {
            this.carregarLista()
        }
    }
</script>
