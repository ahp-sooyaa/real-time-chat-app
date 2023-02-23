<script setup>
import { ref, watch, computed } from "vue";
import TextInput from "./TextInput.vue";

const props = defineProps({ selectedMembers: Object });

const search = ref("");
const searchResults = ref([]);
const show = ref(false);

const isExists = (member) => {
    return props.selectedMembers.some(
        (selectedMember) => selectedMember == member
    );
};

const selectMember = (member) => {
    if (isExists(member)) return;

    props.selectedMembers.push(member);
};

const removeMember = (member) => {
    props.selectedMembers.splice(props.selectedMembers.indexOf(member), 1);
};

watch(
    () => search.value,
    (newValue, oldValue) => {
        axios
            .get(route("user.index", { search: newValue }))
            .then((response) => {
                searchResults.value = response.data.users;
            });
    }
);
</script>

<template>
    <div class="mt-6">
        <div
            v-show="show"
            @click="show = false"
            class="absolute inset-0 z-10"
        ></div>
        <div class="w-3/4">
            <div class="flex space-x-2">
                <div
                    v-for="selectedMember in selectedMembers"
                    :key="selectedMember.id"
                    class="relative z-20 inline-flex items-center bg-gray-100 pl-3 pr-2 py-1 rounded text-sm text-gray-600"
                >
                    {{ selectedMember.name }}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        class="w-4 h-4 ml-1 hover:text-gray-900 cursor-pointer"
                        @click="removeMember(selectedMember)"
                    >
                        <path
                            d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"
                        />
                    </svg>
                </div>
            </div>
            <TextInput
                v-model="search"
                type="text"
                class="relative z-20 w-full mt-3"
                placeholder="members"
                @focus="show = true"
            />
            <div
                v-show="search && show"
                class="relative z-20 bg-white shadow py-2 text-sm -mt-1 border rounded-b-md"
            >
                <div v-if="searchResults.length">
                    <div
                        v-for="searchResult in searchResults"
                        :key="searchResult.id"
                        @click="selectMember(searchResult)"
                        class="flex justify-between hover:bg-indigo-100 text-gray-900 px-3 py-2 cursor-pointer"
                    >
                        {{ searchResult.name }}
                        <svg
                            v-if="
                                selectedMembers.some(
                                    (member) => member.id == searchResult.id
                                )
                            "
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </div>
                <div v-else class="px-3 py-2 text-gray-500">no result</div>
            </div>
        </div>
    </div>
</template>
