export const formatInformations = [
	{
		colorCode: '#CE7722',
		label: 'Progress',
	},
	{
		colorCode: '#FFE5B4',
		label: 'Deviasi',
	},
];

export const menuItems = [
	// Admin
	{
		isHeader: true,
		title: 'Admin',
		link: null,
		allowedRoles: ['admin', 'superadmin'],
	},
	{
		title: 'Divisi',
		icon: 'fluent:group-20-filled',
		link: '/admin/division',
		allowedRoles: ['superadmin', 'admin'],
	},
	{
		title: 'Project',
		icon: 'eos-icons:project-outlined',
		link: '/admin/project',
		allowedRoles: ['superadmin', 'admin'],
	},
	{
		title: 'Akun',
		icon: 'mdi:user-outline',
		link: '/admin/user',
		allowedRoles: ['superadmin', 'admin'],
	},
	{
		title: 'Catatan Absensi',
		icon: 'gg:list',
		link: '/admin/attendance-list',
		allowedRoles: ['superadmin', 'admin'],
	},
	{
		title: 'Download Manager',
		icon: 'el:download',
		link: '/admin/file-manager',
		allowedRoles: ['superadmin', 'admin'],
	},
	// Attendance System
	{
		link: null,
		isHeader: true,
		title: 'Home Attendance System',
		allowedRoles: ['user', 'user_admin'],
	},
	{
		title: 'Profile',
		icon: 'heroicons-outline:home',
		link: '/',
		allowedRoles: ['user', 'user_admin'],
	},
	{
		title: 'Home',
		icon: 'grommet-icons:projects',
		link: '/divisions',
		allowedRoles: ['user', 'user_admin'],
	},
	{
		isHeader: true,
		title: 'Pengaturan',
		link: null,
		allowedRoles: ['user', 'user_admin', 'admin', 'superadmin'],
	},
	{
		title: 'Logout',
		icon: 'material-symbols:logout',
		link: '/logout',
		allowedRoles: ['user', 'user_admin', 'admin', 'superadmin'],
	}
];

export const topMenu = [
	{
		title: 'Profile',
		icon: 'heroicons-outline:home',
		link: '/',
		allowedRoles: ['user', 'user_admin'],
	},
	{
		title: 'Home',
		icon: 'grommet-icons:projects',
		link: '/divisions',
		allowedRoles: ['user', 'user_admin'],
	},
];

export const routeAccessRules = {
	'/admin/user': {
		superadmin: true,
		admin: true,
		user: false,
	},
};

export const userDummyImage ='https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';
