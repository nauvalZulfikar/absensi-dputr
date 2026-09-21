import client from "./http-client";

const endpoint = "/auth/login";

export default {
    login(params, cb, errCB) {
        client.post(endpoint, params)
            .then(res => {
                if (cb) cb(res);
            })
            .catch(e => {
                if (errCB) errCB(e);
            })
    },
    logout(params, cb ,errCb) {
        const url = '/auth/logout';
        client.post(url, params)
            .then(res => {
                if (cb) cb(res);
            })
            .catch(e => {
                if (errCb) errCb(e);
            })
    }
};
