<template>
    <div>
        <h1 class="text-2xl font-semibold mb-4">Profile</h1>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- We'll integrate with the existing Laravel Blade profile page -->
            <div class="p-6">
                <p>
                    This page would integrate with your existing profile
                    functionality.
                </p>
                <p class="mt-4">
                    <a
                        :href="'/profile'"
                        class="text-blue-600 hover:text-blue-800"
                    >
                        Go to profile settings
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Profile",
};
</script>
