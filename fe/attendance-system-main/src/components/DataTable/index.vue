<template>
	<div class="mt-2">
		<Card noborder className="border-none">
			<div
				class="md:flex justify-between pb-6 md:space-y-0 space-y-3 items-center">
				<h5 class="capitalize">{{ title }}</h5>
				<div class="flex gap-4">
					<vue-button
						v-if="btnText"
						:text="btnText"
						:isDisabled="isLoading"
						:is-loading="isLoading"
						btn-class="btn btn-dark px-4 py-2"
						@click="openModalAdd" />
					<InputGroup
						:disabled="isDisabled"
						v-model="searchTerm"
						placeholder="Search"
						type="text"
						prependIcon="heroicons-outline:search"
						merged />
					<date-picker-section
						v-if="isDateVisible"
						@set-date="(date) => $emit('set-date', date)" />
					<vue-button
						v-if="isExport"
						text="Export Data"
						btn-class="btn btn-success"
						@click="$emit('export-data')" 
					/>
				</div>
			</div>

			<div v-if="isLoading">
				<pageLoader />
			</div>

			<vue-good-table
				v-else-if="!isLoading"
				:columns="headers"
				styleClass="vgt-table bordered centered"
				:rows="datas"
				:pagination-options="{
					enabled: true,
					perPage: perpage,
				}"
				:search-options="{
					enabled: true,
					externalQuery: searchTerm,
				}"
				:select-options="{
					enabled: false,
					selectOnCheckboxOnly: true, // only select when checkbox is clicked instead of the row
					selectioninfoClass: 'custom-class',
					selectionText: 'rows selected',
					clearSelectionText: 'clear',
					disableSelectinfo: true, // disable the select info-500 panel on top
					selectAllByGroup: true, // when used in combination with a grouped table, add a checkbox in the header row to check/uncheck the entire group
				}">
				<template v-slot:table-row="props">
					<column-name
						v-if="props.column.field === 'name'"
						:data="props.row" />
					<column-role
						v-if="props.column.field === 'roles'"
						:data="props.row" />
					<column-status
						v-if="props.column.field === 'status'"
						:data="props.row" />
					<action-column
						v-if="props.column.field === 'actions'"
						:data="props.row" />
					<ColumnAssign
						v-if="props.column.field === 'assign'"
						:data="props.row" />
					<ColumnType
						v-if="props.column.field === 'type'"
						:data="props.row" />
					<ColumnShift
						v-if="props.column.field === 'shift'"
						:data="props.row" />
					<div v-if="isRowNotModify(props)">
						<span class="normal-case">
							{{ props.row[props.column.field] }}
						</span>
					</div>
				</template>
				<template #pagination-bottom="props">
					<div class="py-4 px-3">
						<Pagination
							:total="meta.total"
							:current="meta.current_page"
							:per-page="meta.per_page"
							:pageRange="pageRange"
							:pageChanged="props.pageChanged"
							:perPageChanged="props.perPageChanged"
							enableSearch
							enableSelect
							:options="options"
							@page-changed="pageChange"
							@change-per-page="onChangePerPage" />
					</div>
				</template>
			</vue-good-table>
		</Card>
	</div>
</template>

<script>
import Dropdown from '@/components/Dropdown';
import Card from '@/components/Card';
import Icon from '@/components/Icon';
import InputGroup from '@/components/InputGroup';
import Pagination from '@/components/Pagination';
import { MenuItem } from '@headlessui/vue';
import { advancedTable } from '@/constant/basic-tablle-data';
import VueButton from '@/components/Button';
import columnName from '@/components/DataTable/column/name.vue';
import { useDataTableStore } from '@/store/data-table.js';
import { computed } from 'vue';
import columnRole from '@/components/DataTable/column/roles.vue';
import actionColumn from '@/components/DataTable/column/actions.vue';
import ColumnStatus from '@/components/DataTable/column/status.vue';
import ColumnAssign from '@/components/DataTable/column/assign.vue';
import ColumnType from '@/components/DataTable/column/type.vue';
import pageLoader from '../Loader/pageLoader.vue';
import ColumnShift from '@/components/DataTable/column/shift.vue';
import { duplicateVar } from '@/constant/helpers';
import DatePickerSection from '@/components/Breadcrumbs/DatePicker.vue';
const actions = [
	{
		name: 'view',
		icon: 'heroicons-outline:eye',
	},
	{
		name: 'edit',
		icon: 'heroicons:pencil-square',
	},
	{
		name: 'delete',
		icon: 'heroicons-outline:trash',
	},
];
const options = [
	{
		value: '5',
		label: '5',
	},
	{
		value: '10',
		label: '10',
	},
	{
		value: '20',
		label: '20',
	},
];
const columns = [
	{
		label: 'Id',
		field: 'id',
	},
	{
		label: 'Order',
		field: 'order',
	},
	{
		label: 'Customer',
		field: 'customer',
	},
	{
		label: 'Date',
		field: 'date',
	},

	{
		label: 'Quantity',
		field: 'quantity',
	},

	{
		label: 'Amount',
		field: 'amount',
	},

	{
		label: 'Status',
		field: 'status',
	},
	{
		label: 'Action',
		field: 'action',
	},
];
export default {
	components: {
		Pagination,
		InputGroup,
		Dropdown,
		Icon,
		Card,
		MenuItem,
		VueButton,
		columnName,
		columnRole,
		actionColumn,
		ColumnStatus,
		ColumnAssign,
		ColumnType,
		ColumnShift,
		pageLoader,
		DatePickerSection,
	},

	props: {
		title: {
			type: String,
			default: () => '',
		},
		btnText: {
			type: String,
			default: '',
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
		isDateVisible: {
			type: Boolean,
			default: false,
		},
		isExport: {
			type: Boolean,
			default: false,
		},
	},

	data() {
		return {
			advancedTable,
			current: 1,
			perpage: 10,
			pageRange: 5,
			searchTerm: '',
			actions,
			options,
			columns,
			isModalActive: false,
		};
	},
	methods: {
		openModalAdd() {
			this.$emit('open-modal-add');
		},
	},
	setup() {
		const store = useDataTableStore();
		const headers = computed(() => store.headers);
		const datas = computed(() => store.datas);
		const isDisabled = computed(() => store.isDisabledSearch);
		const meta = computed(() => store.meta);
		const pageChange = (event) => {
			meta.value.current_page = event;
			store.setMeta(meta.value);
		};

		const onChangePerPage = (event) => {
			meta.value.per_page = event;
		};

		const isRowNotModify = (props) => {
			const forbiddenFields = [
				'name',
				'roles',
				'status',
				'type',
				'shift',
			];
			return !forbiddenFields.includes(props.column.field);
		};

		return {
			headers,
			datas,
			isRowNotModify,
			isDisabled,
			meta,
			pageChange,
			onChangePerPage,
		};
	},
};
</script>
<style lang="scss"></style>
