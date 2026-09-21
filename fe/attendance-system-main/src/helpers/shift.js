import { axiosHit } from "@/constant/helpers";

const endpoint = 'shift';

// export const xx = () => {};

export const getDataShifts = (params, callback, errCb) => {
    axiosHit(endpoint, params, 'post', callback, errCb);
};

export const createShift = (params, callback, errCb) => {
    axiosHit(`${endpoint}/store`, params,'post' ,callback , errCb);
};

export const deleteShift = (id, callback, errCb) => {
    axiosHit(`${endpoint}/destroy/${id}`, null, 'delete', callback, errCb);
};

export const addUserShift = (params, callback, errCb) => {
    axiosHit(`${endpoint}/add-user`,params ,'post',callback, errCb);
};

export const deleteUserShift = (params, callback, errCb) => {
    axiosHit(`${endpoint}/delete-user`, params, 'post', callback, errCb);
};
