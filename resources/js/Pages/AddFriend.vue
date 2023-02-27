<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { useForm, Head } from "@inertiajs/vue3";

const props = defineProps({ user: Object, token: String });

const form = useForm({
    id: props.user.id,
    token: props.token,
});

const submitForm = () => {
    form.post(route("chatsession.store"));
};
</script>

<template>
    <GuestLayout>
        <Head title="QR code" />

        <header>
            <h2 class="text-lg font-medium text-gray-900 mb-3">
                Add Friend with QR code
            </h2>
        </header>
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div
                    class="inline-block rounded-full w-8 h-8 bg-gray-100 mr-3"
                ></div>
                {{ user.name }}
            </div>
            <PrimaryButton @click="submitForm">Add</PrimaryButton>
        </div>
    </GuestLayout>
</template>
