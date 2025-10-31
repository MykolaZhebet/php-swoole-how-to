(function() {
    let app = {
        ws: null,

        config: {
            // url: 'ws://127.0.0.1',
            url: 'ws://localhost',
            // port: 8004
            port: window.WS_PORT
        },

        init: function() {
            app.connectToServer();
            app.listenEvents();
        },

        connectToServer: function() {
            let wsServer = app.config.url + ':' + app.config.port;
            try {
                if (window.WS_TOKEN) {
                    wsServer += '/?token=' + window.WS_TOKEN;
                }
                app.ws = new WebSocket(wsServer);

                app.ws.onopen = function(evt) {
                    console.log('Connected to ' + wsServer);
                }
                app.ws.onclose = function(evt) {
                    console.log('Disconnected from ' + wsServer);

                }
                app.ws.onmessage = function(evt) {
                    console.log('Received message: ' + evt.data);
                    app.handleMessage(evt.data);
                }
                app.ws.onerror = function(e) {
                    console.log('Error: ', e);
                }
            } catch(error) {
                console.log('Failed to connect to WS: ', error);
            }
        },

        listenEvents: function() {
            document.getElementById('message-form').addEventListener('submit', app.handleSubmit, false)
        },

        handleSubmit: function(e) {
            e.preventDefault();
            app.ws.send(document.getElementById('message-box').value);
        },

        handleMessage: function(data) {
            let parsedData = JSON.parse(data);
            app.addInputMessage(parsedData);

            console.log('message handled');
        },
        addInputMessage: function(parsedData) {
            let input = document.createElement('li');
            let user = document.createElement('strong');
            let message = document.createElement('span');
            user.innerText = parsedData.user + ': ';
            message.innerText = parsedData.message;
            // input.innerHTML = data;
            input.append(user, message);
            document.getElementById('output').appendChild(input);
        }

    }
    app.init();
})();