import { axiosHit } from "@/constant/helpers";

const endpoint = 'roles';

export const getRoles = (params, cb, errCb) => {
    axiosHit(endpoint,params, 'get', cb, errCb);
};
