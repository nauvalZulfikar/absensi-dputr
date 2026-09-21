<template>
	<div>
		<modal
			:active-modal="activeModal"
			sizeClass="max-w-full"
			title="Membuat Shift"
			@close="close">
			<div class="grid grid-cols-12">
				<div class="col-span-8 border-r">
					<div class="p-2">
						<div class="flex justify-between">
							<div></div>
							<div>
								<vue-button
									text="Buat Shift"
									btn-class="btn btn-sm btn-primary light"
									@click="isFormVisible = !isFormVisible" />
							</div>
						</div>
						<!--  -->

						<!-- Form Create Shift -->
						<div v-if="isFormVisible" class="p-2 mt-2">
							<div class="mb-2">
								<span class="text-sm">Buat Shift</span>
							</div>
							<div>
								<!-- Vue Select -->
								<vue-select>
									<vSelect
										v-model="typeShift"
										placeholder="Masukan Type"
										:options="['Reguler', 'Lembur']">
										<template #option="{ label }">
											<div>
												{{ label }}
											</div>
										</template>
									</vSelect>
								</vue-select>

								<div
									v-if="typeShift === 'Lembur'"
									class="p-4 mt-2 mb-2">
									<div class="mb-2">
										<span class="text-sm">
											Waktu Lembur
										</span>
									</div>
									<FormGroup label="Tanggal Dimulai" name="d1">
										<flat-pickr
											v-model="startdate"
											class="form-control"
											id="d1"
											placeholder="Tanggal Di Mulai" />
									</FormGroup>

									<FormGroup label="Tanggal SelesaiOut" name="d1">
										<flat-pickr
											v-model="targetdate"
											class="form-control"
											id="d1"
											placeholder="Tanggal Selesai" />
									</FormGroup>
								</div>

								<FormGroup label="Timein" name="d1">
									<flat-pickr
										v-model="timeIn"
										class="form-control"
										id="d1"
										placeholder="hh: mm"
										:config="{
											enableTime: true,
											noCalendar: true,
											dateFormat: 'H:i',
										}" />
								</FormGroup>

								<FormGroup label="Time Out" name="d1">
									<flat-pickr
										v-model="timeOut"
										class="form-control"
										id="d1"
										placeholder="yyyy, dd M"
										:config="{
											enableTime: true,
											noCalendar: true,
											dateFormat: 'H:i',
										}" />
								</FormGroup>

								<div class="flex justify-end mt-2">
									<vue-button
										text="Submit"
										btn-class="btn-sm btn-primary"
										@click="insertShift" 
									/>
								</div>
							</div>
						</div>
						<!-- End Form Create Shift -->

						<!-- List SHift -->
						<hr class="my-2" />
						<div v-if="!isFormVisible" class="mt-4">
							<div v-if="shifts.length > 0">
								<div
									v-for="(shift, index) in shifts"
									:key="index"
									class="p-2 border-b hover:bg-gray-200 cursor-pointer"
									:class="[{'bg-gray-200': shift.id === selectedShift?.id}]"
								>
									<header
										class="flex justify-between items-center">
										<div
											class="flex space-x-2 sm:space-x-3 items-center rtl:space-x-reverse w-full"
											@click="setSelectedShift(shift, index)"
										>
											<div class="flex-none">
												<div
													class="h-10 w-10 rounded-md sm:text-lg bg-slate-100 text-slate-900 dark:bg-slate-600 dark:text-slate-200 flex flex-col items-center justify-center font-normal capitalize text-sm">
													{{ index + 1 }}
												</div>
											</div>
											<div>
												<div class="text-sm font-bold">
													Time In :
													{{ shift?.timeIn }} WIB -
													TimeOut :
													{{ shift?.timeOut }} WIB
												</div>
												<div class="mt-2">
													<div class="my-2">
														<span
															class="block w-full">
															<span
																class="inline-block text-success-500 bg-success-300 px-3 min-w-[90px] text-center mx-auto py-1 rounded-[999px] bg-opacity-25 text-sm">
																Berlangsung
															</span>
														</span>
													</div>

													<!-- Assign -->
													<div
														class="mt-2 ml-4 flex justify-start -space-x-1.5">
														<div
															v-for="(
																user, userIndex
															) in shift?.user_shift"
															:key="userIndex"
															class="h-6 w-6 rounded-full ring-1 ring-slate-100 cursor-pointer">
															<img
																:src="
																	user?.user
																		?.profile
																		?.medias
																		?.url ??
																	userDummyImage
																"
																:content="
																	user?.user
																		?.name
																"
																v-tippy="{
																	placement:
																		'bottom',
																}"
																:alt="
																	user?.user
																		?.name
																"
																class="w-full h-full rounded-full" />
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="flex gap-4">
											<vue-button
												text="Hapus Shift"
												btn-class="btn-sm light btn-danger light"
												icon-class="text-sm text-red-500"
												@click="removeShift(shift.id)"
											/>
										</div>
									</header>
								</div>
							</div>
							<div
								v-else-if="shifts.length === 0"
								class="flex justify-center">
								<span>Tidak ada shift</span>
							</div>
							<page-loader v-else />
						</div>
						<!-- End List Shift -->
					</div>
				</div>

				<div class="col-span-4">
					<!-- Tabs -->
					<div class="p-2 border-b">
						<div class="flex justify-between">
							<div
								class="p-2 text-sm text-center hover:text-primary-500 w-100 border-b-2 border-b-[#ffff] hover:border-b-primary-500 cursor-pointer">
								<span
									class="text-sm text-center whitespace-nowrap"
								>
									{{titleUserTab}}
								</span>
							</div>
							<div>
								<vue-button
									v-if="selectedShift"
									text="Tambah Anggota"
									btn-class="btn-sm btn-primary btn light"
									@click="addMember" 
								/>
							</div>
						</div>
					</div>
					<!-- End Tabs -->

					<!-- Content -->
					<div>
						<div v-if="listUserVisible">
							<div v-if="userOptions.length > 0 && !isLoading">
								<div
								v-for="(user, index) in userOptions"
								:key="index"
								class="border-b p-2 mt-2 grid grid-cols-12 hover:bg-gray-200 gap-3"
							>
								<div class="col-span-1">
									<img
										:src="
											user?.profile?.medias?.url ??
											userDummyImage
										"
										width="60"
										class="object-cover rounded-full" />
								</div>
								<div class="flex items-center col-span-8">
									<span class="text-sm">
										{{ user?.name }}
									</span>
								</div>
							</div>
							</div>
							<div v-else-if="userOptions?.length === 0 && !isLoading" class="flex justify-center p-2">
								<span>Tidak ada Anggota</span>
							</div>
							<div v-else>
								<page-loader />
							</div>
						</div>
						<div v-else class="flex p-4 justify-center">
							<span class="text-sm">
								Pilih Shift Untuk melihat Anggota
							</span>
						</div>
					</div>
					<!-- End Content -->
				</div>
			</div>

			<template #footer>
				<VueButton
					text="Cancel"
					btn-class="btn-sm btn btn-danger"
					@click="close" />
			</template>
		</modal>
	</div>
</template>
<script setup>
import TextInput from '@/components/Textinput';
import pageLoader from '../Loader/pageLoader.vue';
import Modal from '@/components/Modal';
import UserAssign from '@/components/DataTable/column/assign.vue';
import VueBadge from '@/components/Badge';
import VueButton from '@/components/Button';
import userApi from '@/helpers/user';
import { computed, onMounted, ref, watch } from 'vue';
import VueSelect from '@/components/Select/VueSelect';
import vSelect from 'vue-select';
import projectApi from '@/helpers/projects';
import { useDataTableStore } from '@/store/data-table';
import { useToast } from 'vue-toastification';
import { generateSlug } from '@/constant/helpers';
import { userDummyImage } from '@/constant/static';
import { getDataShifts,createShift, deleteShift } from '@/helpers/shift';
import FormGroup from '@/components/FromGroup';
import { useProjectStore } from '@/store/project';
import { useRoute, useRouter } from 'vue-router';

const timeIn = ref();
const timeOut = ref();
const startdate = ref();
const targetdate = ref();
const typeShift = ref('');
const users = ref([]);
const store = useDataTableStore();
const toast = useToast();
const tabLeft = ref('shift');
const shifts = ref([]);
const rightTabs = [
	{
		label: 'Anggota',
		key: 'anggota',
	},
];

const router = useRouter();
const route = useRoute();

const listUserVisible = ref(false);

const typeForm = ref('add');

const memberPropertyText = ref('Project');

const isFormVisible = ref(false);

const props = defineProps({
	activeModal: {
		type: Boolean,
		default: false,
	},
	projectId: {
		type: [String, Number],
		default: null,
	}
});

const selectedShift = ref();
const storeProject = useProjectStore();

watch(
	() => selectedShift,
	(value) => {
		if (value) {
			getDataShifts();
		}
	}
);

const project = computed(() => storeProject?.selectedProject)

const fetchParams = computed(() => ({
	entities: 'profile.medias, shift',
	project_ids: props?.projectId ? [props?.projectId] : [],
	shift_id: selectedShift?.value?.id,
}));

const emits = defineEmits(['submit', 'close']);

watch(
	() => props?.projectId,
	(value) => {
		if (value) {
			getDataShift();
			getUserSelected();
		}
	}
);

const isLoading = ref(false);
const isFetching = ref(false);
const userOptions = ref([]);

const getUserSelected = (projectId) => {
	isLoading.value = true;
	const params = fetchParams?.value;
	isFetching.value = true;
	const callback = (response) => {
		isLoading.value = false;
		if (response?.data?.meta?.status) {
			const data = response?.data?.data;
			userOptions.value = data;
		}
		isFetching.value = false;
	};
	const err = (e) => {
		console.error(e);
		isFetching.value = false;
	};

	userApi.getUserSelected(params, callback, err);
};

const close = () => {
	emits('close');
};

const getDataShift = () => {
	const params = {
		project_id: props?.projectId,
		entities: 'projectShift, userShift.user.profile.medias',
	};
	const callback = (res) => {
		if (res?.data?.meta?.status) {
			const data = res?.data?.data;
			shifts.value = data;
		}
	};
	const err = (e) => {
		console.error(e);
	};

	getDataShifts(params, callback, err);
};

const insertShift = () => {
	const params = {
		timeIn: timeIn.value,
		timeOut: timeOut.value,
		type: typeShift.value,
		startdate: startdate.value,
		targetdate: targetdate.value,
		project_id: props?.projectId,
	};
	const callback = (res) => {
		if (res?.data?.meta?.status) {
			const data = res?.data?.data;
			shifts.value.push(data);
			isFormVisible.value = false;
			getDataShift();
		}
	};
	const err = () => {
		console.log(e);
		toast?.error('Gagal Membuat SHift');
	};

	createShift(params, callback, err);
};

const removeShift = (id) => {
	const callback = (res) => {
		if (res?.data?.meta?.status) {
			getDataShift();
		}
	};
	const err = (e) => {
		console.log(e);
	};

	deleteShift(id, callback, err);
};

const titleUserTab = ref('');

const addMember = (shift) => {
	router?.push(`/admin/shift/${project?.value?.id}/${project?.value?.slug && project?.value?.slug !== -1 ? project?.value?.slug : generateSlug(project?.value?.name)}?shift_id=${selectedShift?.value?.id}`);
	titleUserTab.value = 'Tambah Pengguna';
	getUserSelected();
};

const setSelectedShift = (shift, index) => {
	selectedShift.value = shift;
	listUserVisible.value = true;
	getUserSelected();
	typeForm.value = 'add'
	memberPropertyText.value = `Shift ke ${index + 1}`;
};

const deleteMemberList = () => {
};

const submit = () => {
	const params = {
		timeIn: timeIn?.value,
		timeOut: timeOut?.value,
		id: props?.projectId,
	};

	emits('submit', params);
};

onMounted(() => {
	if (props?.activeModal) {
		getDataShift();
		// getUserSelected();
		// timeIn.value = store?.typeAction?.data?.timeIn;
		// timeOut.value = store?.typeAction?.data?.timeOut;
	}
});
</script>
