import client from "./http-client";
const endpoint = '/files';

export const getFiles = (params, callback, errCb) => {
    client.get(endpoint, { params }).then(res => {
        if (callback) callback(res);
    }).catch(e => {
        if (errCb) errCb(e);
    })
};
