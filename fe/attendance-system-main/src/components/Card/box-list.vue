<template>
	<div>
		<Card className=" cursor-pointer">
			<!-- header -->
			<header class="flex justify-between items-center">
				<div class="flex space-x-3 items-center rtl:space-x-reverse">
					<div class="flex-none">
						<div
							class="h-10 w-10 rounded-md text-lg bg-slate-100 text-slate-900 dark:bg-slate-600 dark:text-slate-200 flex flex-col items-center justify-center font-normal capitalize">
							{{
								element?.name?.charAt(0) +
								element?.name?.charAt(1)
							}}
						</div>
					</div>
					<div class="flex-1 font-medium text-base leading-6">
						<div
							:content="element.name ?? 'name'"
							v-tippy="{
								placement: 'top',
							}"
							class="board-title dark:text-slate-200 text-slate-900 max-w-[160px] truncate">
							{{ element?.name ?? 'name' }}
						</div>
					</div>
				</div>
			</header>
			<!-- description -->
			<div class="text-slate-600 dark:text-slate-400 text-sm pt-4 pb-8 h-[120px]">
				{{ truncateText(element?.description ?? 'description', 120) }}
			</div>
			<!--  date -->
			<div
				v-if="element.startdate"
				class="flex space-x-4 rtl:space-x-reverse">
				<!-- start date -->
				<div>
					<span class="block date-label">Start date</span>
					<span class="block date-text">{{
						formatedDate(element.startdate)
					}}</span>
				</div>
				<!-- end date -->
				<div>
					<span class="block date-label">Start date</span>
					<span class="block date-text">{{
						formatedDate(element.targetdate)
					}}</span>
				</div>
			</div>
			<!-- progress -->
			<div v-if="type === 'project-list' && false" class="mt-3">
				<span class="text-sm">Progress</span>
				<ProgressBar height="h-3" parentClass="mt-2">
					<Bar :value="80" barColor="bg-[#CE7722]" showValue />
					<Bar
						:value="10"
						barColor="bg-[#FFE5B4]"
						showValue
						colorTextClass="text-black" />
				</ProgressBar>
			</div>
			<div v-if="type === 'project-list' && false" class="mt-3">
				<span>Deviasi</span>
				<ProgressBar height="h-3" parentClass="mt-2">
					<Bar :value="40" barColor="bg-[#CE7722]" showValue />
					<Bar
						:value="40"
						barColor="bg-[#FFE5B4]"
						showValue
						colorTextClass="text-black" />
				</ProgressBar>
			</div>
			<!-- assign and time count -->
			<div class="grid grid-cols-2 gap-4 mt-6">
				<!-- assign -->
				<div>
					<div
						class="text-slate-400 dark:text-slate-400 text-sm font-normal mb-3">
						Assigned to
					</div>
					<div class="flex justify-start -space-x-1.5">
						<div
							class="h-6 w-6 rounded-full ring-1 ring-slate-100"
							v-for="(user, userIndex) in element?.assignto"
							:key="userIndex"
                        >
							<img
								:src="user.image ?? userDummyImage"
                                :content="user.name"
                                v-tippy="{
                                    placement: 'top'
                                }"
								:alt="user.name"
								class="w-full h-full rounded-full" />
						</div>
						<div
							class="bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-300 text-xs ring-2 ring-slate-100 dark:ring-slate-700 rounded-full h-6 w-6 flex flex-col justify-center items-center">
							+{{ element?.assignto?.length ?? 0 }}
						</div>
					</div>
				</div>

				<!-- total date -->
				<div
					v-if="element.startdate"
					class="ltr:text-right rtl:text-left">
					<span
						class="inline-flex items-center space-x-1 bg-danger-500 bg-opacity-[0.16] text-danger-500 text-xs font-normal px-2 py-1 rounded-full rtl:space-x-reverse">
						<span> <Icon icon="heroicons-outline:clock" /></span>
						<span>{{
							totalDate(element.startdate, element.targetdate)
						}}</span>
						<span>days left</span></span
					>
				</div>
			</div>
		</Card>
	</div>
</template>
<script>
import dayjs from 'dayjs';
import Card from '@/components/Card';
import Dropdown from '@/components/Dropdown';
import Icon from '@/components/Icon';
import ProgressBar from '@/components/ProgressBar';
import Bar from '@/components/ProgressBar/Bar';
import { MenuItem } from '@headlessui/vue';
import { ref } from 'vue';
import { useKanbanStore } from '@/store/kanban';
import av1Img from '@/assets/images/avatar/av-1.svg';
import av2Img from '@/assets/images/avatar/av-2.svg';

export default {
	name: 'BoxList',
	components: {
		Card,
		ProgressBar,
		Icon,
		Dropdown,
		MenuItem,
		Bar,
	},
	props: {
		element: {
			type: Object,
			required: true,
		},
        type: {
            type: String,
        }
	},
	setup() {
		const store = useKanbanStore();
		const userDummyImage =
			'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';

		const assignto = [
			{
				image: av1Img,
				title: 'Mahedi Amin',
			},
			{
				image: av2Img,
				title: 'Sovo Haldar',
			},
			{
				image: av2Img,
				title: 'Rakibul Islam',
			},
		];

		const formatedDate = (date) => dayjs(date).format('DD/MM/YYYY');
        const truncateText = (text, maxLength) => {
            return text.length > maxLength ? text.slice(0, maxLength) + '...' : text;
        }

		const totalDate = (start, end) => {
			const startDate = new Date(start);
			const endDate = new Date(end);
			const diffDays = endDate.getDate() - startDate.getDate();
			return diffDays;
		};

		const actions = ref([
			{
				name: 'Edit',
				icon: 'heroicons-outline:pencil-alt',
				doit: (data) => {
					store.updateTask(data);
				},
			},
			{
				name: 'Delete',
				icon: 'heroicons-outline:trash',
				doit: (data) => {
					store.removeTask(data);
				},
			},
		]);
		return {
			totalDate,
			actions,
			assignto,
			formatedDate,
            truncateText,
			userDummyImage
		};
	},
};
</script>
<style lang="scss" scoped>
.date-label {
	@apply text-xs text-slate-400 dark:text-slate-400 mb-1;
}
.date-text {
	@apply text-xs text-slate-600 dark:text-slate-300 font-medium;
}
</style>
