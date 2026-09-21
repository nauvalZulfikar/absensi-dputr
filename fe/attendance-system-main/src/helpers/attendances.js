import { axiosHit } from "@/constant/helpers";
import client from "./http-client";
const endpoint = 'attendance';

export const getAllData = (params, callback, errCallback) => {
    axiosHit(endpoint,params, 'get', callback, errCallback);
};

export const getDataAttendanceLogs = (params, callback, errCallback) => {
    const url = `${endpoint}/log`;
    client.get(url, { params })
        .then(res => {
            if (callback) callback(res);
        })
        .catch(e => {
            if (errCallback) errCallback(e)
        })
};

export const attendance = (params, callback, errCallback) => {
    client.post(endpoint, params)
        .then(res => {
            if (callback) callback(res);
        })
        .catch(e => {
            if (errCallback) errCallback(e)
        })
};

export const getDataSummary = (params, callback, errCallback) => {
    const url = `${endpoint}/summary`;
    client.get(url, {params}).then(res => {
        if (callback) callback(res);
    })
    .catch(e => {
        if (errCallback) errCallback(e);
    })

}

export const exportAttendance = (params, callback, errCallback) => {
    const url = `/export`;
    client.get(url, {params}).then(res => {
        if (callback) callback(res);
    })
    .catch(e => {
        if (errCallback) errCallback(e);
    })
};
