import WsRouter from './ws-router';

let app = {
    router: null,
    config: {uri: null, port: null},
    conns: {},
    messageForm: null,
    messageBox: null,
    outputBox: null,
    connectionsListBox: null,


    init: (
        config,
        messageForm,
        messageBox,
        outputBox,
        connectionsListBox,
    ) => {
        console.log('init WS');
        app.config = config;
        app.messageForm = messageForm;
        app.messageBox = messageBox;
        app.outputBox = outputBox;
        app.connectionsListBox = connectionsListBox;

        app.router = new WsRouter(config, {
            //event handlers
            'onReady': app.handleOnReady,
            'onClose': app.handleOnClose,
            // action handlers
            'welcome-action': app.handleWelcomeConnection,
            'new-connection-action': app.handleNewConnection,
            'closed-connection-action': app.handleClosedConnection,
            'broadcast-action': app.handleBroadcast,
            'secondary-broadcast-action': app.handleBroadcast,

        });

        app.listenEvents();
    },

    listenEvents: () => {
        app.messageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            // app.router.ws.send(app.messageBox.value, 'secondary-broadcast-action');
            app.router.send(app.messageBox.value, 'secondary-broadcast-action');
        }, false);
    },
    handleOnReady: () => {
        console.log('Connected to WS server ready');
    },
    handleOnClose: (e) => {
        console.log('Disconnected from WS server');
    },

    handleWelcomeConnection: (ws, parsedData) => {
        console.log('handleWelcomeConnection', parsedData);
        let message = JSON.parse(parsedData.data);
        //Add new connection and all existing connections to the list
        app.conns[parsedData.fd] = [parsedData.fd];
        Object.keys(message.connections).forEach((fd) => app.conns[fd] = fd);
        app.connectionsListBox.innerHTML = Object.keys(app.conns).join(', ');
    },

    handleNewConnection: (ws, parsedData) => {
        app.conns[parsedData.fd] = parsedData.fd;
        app.connectionsListBox.innerHTML = Object.keys(app.conns).join(', ');
    },

    handleClosedConnection: (ws, parsedData) => {
        delete app.conns[parsedData.fd];
        app.connectionsListBox.innerHTML = Object.keys(app.conns).join(', ');
    },
    handleBroadcast: (ws, parsedData) => {
        let input = document.createElement('li');
        input.innerText = '(' + parsedData.fd + ')' + parsedData.data;
        app.outputBox.appendChild(input);
    },
}

window.app = app;