<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MultiSelectUser from "@/Components/MultiSelectUser.vue";
import ChatSessionItem from "@/Components/ChatSessionItem.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { onMounted, ref } from "vue";

const props = defineProps({ chatSessions: Object });

const showingCreateGroupForm = ref(false);
const groupMembers = ref([]);

const showCreateGroupFrom = () => {
    showingCreateGroupForm.value = true;
};

const closeModal = () => {
    showingCreateGroupForm.value = false;
    groupMembers.value = [];
};

const form = useForm({
    name: "",
    users: groupMembers,
    is_group: true,
});

const createGroup = () => {
    form.post(route("chatsession.store"), {
        onSuccess: () => {
            form.reset();

            closeModal();
        },
    });
};

const chatSessionsList = (isGroup) => {
    return props.chatSessions.filter(
        (chatSession) => chatSession.is_group == isGroup
    );
};

onMounted(() => {
    window.Echo.channel("chatsession.created").listen(
        "NewChatSessionCreated",
        (e) => {
            props.chatSessions.push(e.chatSession);
        }
    );
});
</script>

<template>
    <Head title="Chats" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chats
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-3">
                            <h1
                                class="flex items-center text-gray-700 font-bold tracking-wider"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-6 h-6 mr-1"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"
                                    />
                                </svg>

                                Groups
                            </h1>
                            <button
                                class="flex items-center bg-gray-900 text-white rounded-md text-sm"
                                @click="showCreateGroupFrom"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-6 h-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v12m6-6H6"
                                    />
                                </svg>
                            </button>
                            <Modal
                                :show="showingCreateGroupForm"
                                @close="closeModal"
                            >
                                <div class="relative p-6">
                                    <h2
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Create Chat Group
                                    </h2>

                                    <p class="mt-1 text-sm text-gray-600">
                                        Once your account is deleted, all of its
                                        resources and data will be permanently
                                        deleted. Please enter your password to
                                        confirm you would like to permanently
                                        delete your account.
                                    </p>

                                    <div class="mt-6">
                                        <InputLabel
                                            for="name"
                                            value="Group Name"
                                            class="sr-only"
                                        />

                                        <TextInput
                                            id="name"
                                            ref="groupNameInput"
                                            v-model="form.name"
                                            type="text"
                                            class="relative z-20 mt-1 block w-full lg:w-3/4"
                                            placeholder="Group Name"
                                        />

                                        <InputError
                                            :message="form.errors.name"
                                            class="mt-2"
                                        />
                                    </div>

                                    <MultiSelectUser
                                        :selectedMembers="groupMembers"
                                    ></MultiSelectUser>

                                    <div class="mt-6 flex justify-end">
                                        <SecondaryButton
                                            @click="closeModal"
                                            class="relative z-20"
                                        >
                                            Cancel
                                        </SecondaryButton>

                                        <PrimaryButton
                                            class="relative z-20 ml-3"
                                            :class="{
                                                'opacity-25': form.processing,
                                            }"
                                            :disabled="form.processing"
                                            @click="createGroup"
                                        >
                                            Create Group
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </Modal>
                        </div>
                        <div
                            v-if="chatSessionsList(true).length"
                            class="space-y-3"
                        >
                            <ChatSessionItem
                                v-for="chatSession in chatSessionsList(true)"
                                :chatSession="chatSession"
                            >
                            </ChatSessionItem>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Your groups will appear here.
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1
                            class="flex items-center text-gray-700 mb-3 font-bold tracking-wider"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-6 h-6 mr-1"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                />
                            </svg>

                            Friends
                        </h1>
                        <div
                            v-if="chatSessionsList(false).length"
                            class="space-y-3"
                        >
                            <ChatSessionItem
                                v-for="chatSession in chatSessionsList(false)"
                                :chatSession="chatSession"
                            >
                            </ChatSessionItem>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            Your friend contact will appear here.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
