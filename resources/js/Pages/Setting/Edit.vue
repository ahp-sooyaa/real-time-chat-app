<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    chatSession: Object,
});

const form = useForm({
    name: props.chatSession.name,
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Setting
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Link :href="route('chatsession.show', chatSession.id)" class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
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

                        <div class="space-y-2 mt-6">
                            <div v-for="member in chatSession.users" class="space-x-2">
                                <TextInput
                                    type="text"
                                    v-model="member.pivot.nickname"
                                    placeholder="no nickname"
                                    class="text-xs"
                                />
                                <Link
                                    :href="
                                        route('chatsession.member.update', [
                                            chatSession.id,
                                            member.id,
                                        ])
                                    "
                                    :data="{ nickname: member.pivot.nickname }"
                                    method="patch"
                                    as="button"
                                >
                                    <PrimaryButton>
                                        Save
                                    </PrimaryButton>
                                </Link>
                                <Link
                                    v-if="member.id != chatSession.creator_id"
                                    :href="
                                        route('chatsession.member.destroy', [
                                            chatSession.id,
                                            member.id,
                                        ])
                                    "
                                    :data="{
                                        action:
                                            member.name +
                                            ' has been kicked from group by' +
                                            $page.props.auth.user.name,
                                    }"
                                    method="delete"
                                    as="button"
                                >
                                    <DangerButton>
                                        Kick
                                    </DangerButton>
                                </Link>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
