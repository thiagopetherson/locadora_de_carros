window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

// Interceptar os requests da aplicação
axios.interceptors.request.use(
    config => {
            // Definindo para todas as requisições os parâmetros de accept
            config.headers['Accept'] = 'application/json'

            // Pegando o token que está cookie
            let token = document.cookie.split(';').find(indice => {
                return indice.includes('token=')
            })
            token = token.split('=')[1]
            token = 'Bearer ' + token
            // Terminando de pegar o token e já configurando o Bearer

            // Definindo para todas as requisições os parâmetros de authorization
            config.headers.Authorization = token

            console.log('Interceptando o request antes do envio', config)
            return config
    },
    error => {
        console.log('Erro na requisição: ', error)
        return Promise.reject(error)
    }
)

// Interceptar os responses da aplicação
axios.interceptors.response.use(
    response => {
        console.log('Interceptando a resposta antes da aplicação', response)
        return response
    },
    error => {
        console.log('Erro na resposta: ', error)
        // Verificando se o token está expirado
        if (error.response.status == 401 && erros.response.data.message == 'Token has expired') {
            axios.post('http://localhost:8000/api/refresh')
                .then(response => {
                    console.log('Refresh com sucesso: ', response)
                    document.cookie = 'token'+response.data.token
                    window.location.reload() // Para forçar um reload na página e a próxima requisição usar o token novo
                })
        }

        return Promise.reject(error)
    }
)
