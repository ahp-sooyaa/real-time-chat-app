<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({ user: Object, email: String, token: String });

const form = useForm({
    email: props.email,
    token: props.token,
});

const submitForm = () => {
    form.post(route("chatsession.store"));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add friend
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                {{ user.name }}
                <button @click="submitForm">Add</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
