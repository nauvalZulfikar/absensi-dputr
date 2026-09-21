import { axiosHit } from '@/constant/helpers.js';
import client from './http-client';
const endpoint = '/project';
const endpointShift = '/shift';
export default {
	getDataProjects(params, callback, errCb) {
		// axiosHit(endpoint, params, "post", callback, errCB);
		client
			.post(endpoint, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	getDetailProject(id, params, callback, errCb) {
		const url = `${endpoint}/show/${id}`;
		// axiosHit(url, params, "get", callback, errCb);
		client
			.get(url, { params })
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	createProject(params, callback, errCb) {
		// axiosHit(`${endpoint}/store`, params, 'post', callback, errCb);
		client
			.post(`${endpoint}/store`, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	updateProject(id, params, callback, errCb) {
		const url = `${endpoint}/update/${id}`;
		// axiosHit(url, params, 'put', callback, errCb);
		client
			.put(url, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	deleteUserProject(id, callback, errCb) {
		// axiosHit(`user-project/${id}`, null, 'delete', callback, errCb);
		client
			.delete(`user-project/${id}`)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	insertUserProject(params, callback, errCb) {
		// axiosHit(`user-project`, params, 'post', callback, errCb);
		client
			.post(`user-project`, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	/**Bulk */
	insertUsersProject(params, callback, errCb) {
		// axiosHit(`user-project/inserts`, params, 'post', callback, errCb);
		client
			.post(`user-project/inserts`, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	createShift(params, callback, errCb) {
		const url = `${endpointShift}/store`;
		// axiosHit(url, params, 'post', callback, errCb);
		client
			.post(url, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},

	updateShift(id, params, callback, errCb) {
		const url = `${endpointShift}/update/${id}`;
		// axiosHit(url, params, 'put', callback, errCb);
		client
			.put(url, params)
			.then((res) => {
				if (callback) callback(res);
			})
			.catch((e) => {
				if (errCb) errCb(e);
			});
	},
};
