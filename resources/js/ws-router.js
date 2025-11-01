import Conveyor from 'socket-conveyor-client';
class WsRouter {

    constructor(config, appHandlers) {
        this.appHandlers = appHandlers;
        let options = {
            protocol: config.protocol ? config.protocol : 'ws',
            uri: config.uri ? config.uri : '127.0.0.1',
            port: config.port ? config.port : 8004,
            channel: config.channel ? config.channel : null,
            reconnect: true,
        }

        //events
        if(this.appHandlers.onOpen) {
            options.onOpen = this.appHandlers.onOpen;
        }

        if(this.appHandlers.onClose) {
            options.onClose = this.appHandlers.onClose;
        }

        if(this.appHandlers.onReady) {
            options.onReady = this.appHandlers.onReady;
        }

        options.onRawMessage = this.handleIncomingMessage.bind(this);
        options.onError = this.handleError.bind(this);

        this.ws = new Conveyor(options);
    }

    handleError(e) {
        console.log('error: ', e.data);
    }
    send(message, action) {
        console.log('Send from Router: ', message, action);
        let data = {
            action,
            data: message,
        }
        // this.ws.send(JSON.stringify(data));
        this.ws.send(message, action);
    }

    handleIncomingMessage(data) {
        let parsedData = JSON.parse(data);
        console.log('parsedData: handleIncomingMessage');
        console.log(parsedData);
        console.log('parsedDataEnd handleIncomingMessage');


        if(this.appHandlers[parsedData.action]) {
            this.appHandlers[parsedData.action](this.ws, parsedData);
            return;
        }

        console.log('unknown action: ', parsedData.action);
    }
}

export default WsRouter;