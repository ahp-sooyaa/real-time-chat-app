<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import UpdateNicknameForm from "./Partials/UpdateNicknameForm.vue";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    chatSession: Object,
});

const editingMember = ref(null);

const form = useForm({
    name: props.chatSession.name,
});

const showing = ref(false);

const showNicknameForm = (member) => {
    showing.value = true;
    editingMember.value = member;
};

const closeModal = () => {
    showing.value = false;
};
</script>

<template>
    <Head :title="chatSession.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ chatSession.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Link
                    :href="route('chatsession.show', chatSession.id)"
                    class="inline-flex items-center"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-5 h-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 19.5L8.25 12l7.5-7.5"
                        />
                    </svg>

                    back
                </Link>
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                Group Chat Information
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Update your group chat's name.
                            </p>
                        </header>

                        <form
                            @submit.prevent="
                                form.patch(
                                    route(
                                        'chatsession.setting.update',
                                        chatSession.id
                                    )
                                )
                            "
                            class="mt-6 space-y-6"
                        >
                            <div>
                                <InputLabel for="name" value="Name" />

                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />

                                <InputError
                                    class="mt-2"
                                    :message="form.errors.name"
                                />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing"
                                    >Save</PrimaryButton
                                >

                                <Transition
                                    enter-from-class="opacity-0"
                                    leave-to-class="opacity-0"
                                    class="transition ease-in-out"
                                >
                                    <p
                                        v-if="form.recentlySuccessful"
                                        class="text-sm text-gray-600"
                                    >
                                        Saved.
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                Manage Members
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                Give nickname to group chat member, kick them
                                from group.
                            </p>
                        </header>

                        <div class="space-y-4 mt-6">
                            <div
                                v-for="member in chatSession.users"
                                class="flex items-center hover:bg-gray-100 p-3 -mx-3 rounded-lg cursor-pointer"
                                @click="showNicknameForm(member)"
                            >
                                <div
                                    class="h-10 w-10 rounded-full bg-gray-500"
                                ></div>
                                <div class="ml-2">
                                    <h1>{{ member.name }}</h1>
                                    <p class="text-xs text-gray-500">
                                        Set nickname
                                    </p>
                                </div>

                                <div
                                    v-if="member.pivot.is_owner"
                                    class="ml-auto text-sm text-gray-700 self-start tracking-wide"
                                >
                                    owner
                                </div>
                                <Link
                                    v-else
                                    :href="
                                        route('chatsession.member.destroy', [
                                            chatSession.id,
                                            member.id,
                                        ])
                                    "
                                    :data="{
                                        action:
                                            member.name +
                                            ' has been kicked from group by ' +
                                            $page.props.auth.user.name,
                                    }"
                                    method="delete"
                                    as="button"
                                    class="ml-auto"
                                >
                                    <DangerButton> Kick </DangerButton>
                                </Link>
                            </div>
                            <UpdateNicknameForm
                                :member="editingMember"
                                :chat-session="chatSession"
                                :show="showing"
                                @close="closeModal"
                            ></UpdateNicknameForm>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
