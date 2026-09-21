import client from "./http-client";
const url = 'media';

export const uploadMedia = (params, callback, errCallback) => {
    client.post(url, params).then(res => {
        if (callback) callback(res);
    }).catch(e => {
        if (errCallback) errCallback(e);
    });
};

export const uploadMediaImgur = (params, callback, errCb) => {
    const clientId = 'cb8c5d9613f3073';
    client.post('https://api.imgur.com/3/upload', params, {
        headers: {
            Authorization: `Client-ID ${clientId}`,
        },
    }).then(res => {
        if (callback) callback(res);
    }).catch(e => {
        if (errCb) errCb(e);
    });
}
