<template>
	<modal
		:active-modal="activeModal"
		:title="`Detail Kehadiran ${data?.project}`"
		sizeClass="max-w-full"
		@close="close"
	>
		<div>
			<div class="modal-content bg-white p-4">
				<div class="grid grid-cols-12">
					<div class="col-span-8 border-r-2">
						<div class="mb-2">
							<div class="mb-3">
								<span class="text-lg font-bold mb-2">
									Detail Pengguna
								</span>
							</div>
							<div>
								<span class="text-sm">
									Nama : {{ data?.name }}
								</span>
							</div>
							<div>
								<span class="text-sm">
									Email : {{ data?.user?.email }}
							</span>
							</div>
							<div>
								<span class="text-sm">
									Nomor Handphone :
									{{
										data?.user?.profile?.phoneNumber ?? '-'
									}}
								</span>
							</div>
						</div>
						<hr class="my-4" />
						<div class="mt-6">
							<div class="mb-3">
								<span class="text-lg font-bold mb-2">
									Detail Project
								</span>
							</div>
							<div>
								<span class="text-sm">
									Nama Project : {{ data?.project }}
								</span>
							</div>
							<div>
								<span class="text-sm">
									Alamat Project : {{ data?.project_address }}
								</span>
							</div>
							<div>
								<span class="text-sm">
									Deskripsi :
									{{ data?.project_decription ?? '-' }}
								</span>
							</div>
						</div>
						<hr class="mt-6" />
						<div class="mt-6">
							<div class="mb-3">
								<span class="text-lg font-bold mb-2">
									Detail Kehadiran
								</span>
							</div>
							<div>
								<span class="text-sm">
									Alamat Absen : {{ data?.full_address }}
								</span>
							</div>
							<div>
								<span class="text-sm capitalize">
									latitude : {{ data?.latitude }}
								</span>
							</div>
							<div>
								<span class="text-sm capitalize">
									longitude : {{ data?.longtitude }}
								</span>
							</div>
							<div>
								<span class="text-sm capitalize">
									Type : {{ data?.type }}
								</span>
							</div>
							<div>
								<span class="text-sm capitalize">
									Status : {{ data?.status ?? 'late' }}
								</span>
							</div>
							<div v-if="data?.date">
								<span class="text-sm">
									Tanggal Absen : {{ data?.date }}
								</span>
							</div>
							<div v-if="data?.time">
								<span class="text-sm">
									Waktu Absen : {{convertToShortFormat(data?.time) }}
								</span>
							</div>
						</div>

						<hr class="mt-6">
						<div v-if="data?.shift" class="mt-6">
							<div class="mb-3">
								<span class="text-lg font-bold mb-2">
									Detail Shift
								</span>
							</div>
							<div>
								<span class="text-sm">
									Type : {{ data?.shift?.type ?? 'Reguler' }}
								</span>
							</div>
							<div>
								<span class="text-sm">
									Waktu Masuk : {{ convertToShortFormat(data?.shift?.timeIn) }} WIB
								</span>
							</div>
							<div>
								<span class="text-sm">
									Waktu Keluar: {{ convertToShortFormat(data?.shift?.timeOut) }} WIB
								</span>
							</div>
							<div></div>
						</div>

						<div v-if="false" class="grid grid-cols-2 mt-4 gap-2">
							<vue-button
								text="Approve"
								btn-class="btn btn-primary light btn-sm"
								@click="submit" 
                            />
							<vue-button
								text="Reject"
								btn-class="btn btn-danger light btn-sm"
								@click="submit" 
                            />
						</div>
					</div>
					<div class="col-span-4">
						<div class="p-2">
							<div class="mt-2 text-center">
								<div class="mb-3">
									<span class="text-sm font-bold">
										Foto Absen
									</span>
								</div>
								<img
									:src="data?.media?.url"
									class="w-3/4 mx-auto"
									alt=""
									srcset="" />
							</div>

							<div class="mt-2 text-center">
								<div class="mb-3">
									<span class="text-sm font-bold">
										Foto Bukti
									</span>
								</div>
								<img
									:src="data?.media_proof"
									class="w-3/4 mx-auto"
									alt=""
									srcset="" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<template #footer>
			<div class="flex justify-end mt-4 gap-2">
				<vue-button
					text="Tutup"
					btn-class="btn btn-dark btn-sm"
					@click="$emit('close')" 
				/>
			</div>
		</template>
	</modal>
</template>

<script setup>
import Modal from '@/components/Modal/index.vue';
import VueButton from '@/components/Button';
import { useDataTableStore } from '@/store/data-table';
import { computed } from 'vue';
import { convertToShortFormat } from '@/constant/helpers';

const store = useDataTableStore();

const props = defineProps({
	activeModal: {
		type: Boolean,
	},
	title: {
		type: String,
	},
	text: {
		type: String,
	},
	data: {
		type: Object,
	},
});
const emits = defineEmits(['close', 'submit']);

const close = () => {
	emits('close');
};
const data = computed(() => store?.typeAction?.data || props?.data);
const type = computed(() => store?.typeAction?.key);
const submit = () => {
	emits('submit', data?.value, type?.value);
};
</script>
