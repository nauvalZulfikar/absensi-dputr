import { useToast } from "vue-toastification";
import client from "@/helpers/http-client.js";

/**
 * Display a toast message.
 *
 * @param {string} type - The type of the toast (success, error, info, warning).
 * @param {Number} duration - The duration of the toast message in milliseconds.
 * @param { String } message - Message of the the toast
 */
export const generateNotification = (type, duration, message) => {
  const toastInstance = useToast();

  // Ensure the provided type is one of the supported types
  const supportedTypes = ["success", "error", "info", "warning"];
  const isValidType = supportedTypes.includes(type);

  if (!isValidType) {
    console.error(
      "Invalid toast type. Supported types: success, error, info, warning",
    );
    return;
  }

  /**
   * Options for the toast message.
   *
   * @type {object}
   * @property {number} timeout - The duration of the toast message.
   */
  const toastOptions = {
    timeout: duration || 2000,
  };

  // Use dynamic property to select the appropriate toast method based on the provided type
  toastInstance[type](message, toastOptions);
};

export const axiosHit = (endpoint, params, type, callback, errCallback) => {
  const param = type === "get" ? { params } : params;
  client[type](endpoint, param)
    .then((res) => {
      if (callback) callback(res);
    })
    .catch((e) => {
      if (errCallback) errCallback(e);
    });
};

export const duplicateVar = (value) =>
  value ? JSON.parse(JSON.stringify(value)) : "";

/**
 * Creates a form field object based on the provided configuration.
 *
 * @param {Object} config - The configuration for the form field.
 * @param {string} config.type - The type of the form field (e.g., 'text', 'multiselect', 'textarea').
 * @param {string} [config.value=''] - The initial value of the form field.
 * @param {string} [config.error=''] - The error message associated with the form field.
 * @returns {Object} - The form field object.
 */
export const createFormField = (config) => ({
  type: config.type,
  value: config.value || "",
  error: config.error || "",
  options: [],
  ...config,
});

export const createOptionSelect = (id, label, image = null, roles) => ({
  id,
  label,
  image,
  roles,
});

export const setNameRoles = (role) => {
  let displayRole = role;
  if (role === "user_admin") {
    displayRole = "Pengawas";
  }

  return displayRole;
};

export const totalDate = (start, end) => {
  const startDate = new Date(start);
  const endDate = new Date(end);
  const diffDays = endDate.getDate() - startDate.getDate();
  return diffDays;
};

export const convertToShortFormat = (fullTime) => {
  const [hours, minutes] = fullTime.split(":");
  return `${hours.padStart(2, "0")}:${minutes.padStart(2, "0")}`;
};

export const generateSlug = (input) => {
  return input
    .toLowerCase()
    .trim()
    .replace(/\s+/g, "-")
    .replace(/[^\w\-]+/g, "")
    .replace(/\-\-+/g, "-")
    .replace(/^-+/, "")
    .replace(/-+$/, "");
};

/**
 * Download File URL.
 * Endpoint /export-data sekarang di-gate auth:api → tarik lewat http-client
 * (nempelin Bearer token) sebagai blob, bukan <a href> mentah yang ga bawa header.
 */
export const downloadFile = async (url, newTab) => {
  const link = url?.value ?? url; // toleran kalau dikirim ref
  try {
    const res = await client.get(link, { responseType: "blob" });
    const blobUrl = URL.createObjectURL(res.data);
    const a = document.createElement("a");
    if (newTab) a.target = "_blank";
    a.href = blobUrl;
    a.download = link.substr(link.lastIndexOf("/") + 1);
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(blobUrl);
  } catch (e) {
    generateNotification(
      "error",
      3000,
      "Gagal unduh file — butuh login / akses admin.",
    );
  }
};

/**
 * Mask NIK/ID: sisakan 4 digit terakhir, sisanya jadi bullet.
 */
export const maskNik = (nik) => {
  if (!nik) return "-";
  const s = String(nik);
  return s.length <= 4 ? s : "•".repeat(s.length - 4) + s.slice(-4);
};

/**
 * Format angka jadi ribuan gaya Indonesia (tanpa simbol Rp).
 */
export const formatRupiah = (value) =>
  value === null || value === undefined || value === ""
    ? "-"
    : Number(value).toLocaleString("id-ID");
