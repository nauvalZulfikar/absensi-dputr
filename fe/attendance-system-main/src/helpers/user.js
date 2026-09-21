import client from "./http-client";
import { axiosHit } from '@/constant/helpers.js'

const endpoint = 'profile';
const endpointUser = 'user';

export default {
    getMe(params,cb, errCB) {
        const url = `${endpoint}/me`;
        client.get(url, { params })
            .then(res => {
                cb(res);
            }).catch(e => {
                errCB(e);
            })
    },
    getAllUsers(params,cb, errCB) {
        const url = `${endpointUser}/all`
        // axiosHit(url, params, 'post', cb, errCB);
        client.post(url, params)
            .then(res => {
                cb(res);
            }).catch(e => {
                errCB(e);
            })
    },
    createUser(params, cb, errCB) {
        // axiosHit(endpointUser, params, 'post', cb, errCB);
        client.post(endpointUser, params)
            .then(res => {
                cb(res);
            }).catch(e => {
                errCB(e);
            })
    },
    getUserSummary(params, cb, errCb) {
        const url = `${endpointUser}/summary`;
        // axiosHit(url, params, 'get', cb, errCb);
        client.get(url, { params })
            .then(res => {
                cb(res);
            }).catch(e => {
                errCb(e);
            })
    },

    getUserSelected(params, cb, errCb) {
        const url = `${endpointUser}/selected`;
        // axiosHit(url, params, 'post', cb, errCb);
        client.post(url, params)
            .then(res => {
                cb(res);
            }).catch(e => {
                errCb(e);
            })
    },

    updateProfile(params, cb, errCb) {
        const url = `${endpointUser}/profile`;
        // axiosHit(url, params, 'post', cb, errCb);
        client.post(url, params)
            .then(res => {
                cb(res);
            }).catch(e => {
                errCb(e);
            })
    },

    changePassword(params, cb, errCb) {
        const endpoint = 'auth/change-password';
        // axiosHit(endpoint, params, 'post', cb, errCb);
        client.post(endpoint, params)
            .then(res => {
                cb(res);
            }).catch(e => {
                errCb(e);
            })
    }
}
