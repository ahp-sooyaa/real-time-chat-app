<script setup>
import { ref, watch, computed, onMounted } from "vue";
import TextInput from "./TextInput.vue";

const props = defineProps({ selectedMembers: Object });

const search = ref("");
const searchResults = ref([]);
const show = ref(false);
const hightlightIndex = ref(0);

const isExists = (member) => {
    return props.selectedMembers.some(
        (selectedMember) => selectedMember == member
    );
};

const hightlightDown = () => {
    if (hightlightIndex.value == searchResults.value.length - 1) {
        hightlightIndex.value = 0;
        return;
    }

    hightlightIndex.value++;
};

const hightlightUp = () => {
    if (hightlightIndex.value == 0) {
        hightlightIndex.value = searchResults.value.length - 1;
        return;
    }

    hightlightIndex.value--;
};

const selectHightlightedMember = () => {
    if (isExists(member)) return;

    props.selectedMembers.push(searchResults.value[hightlightIndex.value]);
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

onMounted(() => {
    axios.get(route("user.index")).then((response) => {
        searchResults.value = response.data.users;
    });
});
</script>

<template>
    <div class="mt-6">
        <div
            v-show="show"
            @click="show = false"
            class="absolute inset-0 z-10"
        ></div>
        <div class="w-full lg:w-3/4">
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
                placeholder="Add friends to group"
                @focus="show = true"
                @keydown.arrow-up.prevent="hightlightUp"
                @keydown.arrow-down.prevent="hightlightDown"
                @keydown.enter.prevent="selectHightlightedMember"
            />
            <div
                v-show="show"
                class="relative z-20 bg-white shadow py-2 text-sm -mt-1 border rounded-b-md"
            >
                <div v-if="searchResults.length">
                    <div
                        v-for="(searchResult, index) in searchResults"
                        :key="searchResult.id"
                        @click="selectMember(searchResult)"
                        class="flex justify-between hover:bg-indigo-100 text-gray-900 px-3 py-2 cursor-pointer"
                        :class="{ 'bg-indigo-100': index == hightlightIndex }"
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
                <div class="hidden md:flex space-x-5 px-3 pt-2">
                    <div class="flex space-x-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-5 h-5 transform rotate-180 text-gray-900"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 000 12h3"
                            />
                        </svg>
                        <p class="text-gray-500">to select</p>
                    </div>
                    <div class="flex">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-5 h-5 text-gray-900"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"
                            />
                        </svg>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-5 h-5 text-gray-900"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"
                            />
                        </svg>

                        <p class="text-gray-500 ml-2">to navigate</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
