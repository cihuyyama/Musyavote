<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const activeTab = ref('dashboard');

const tabConfig = {
    dashboard: {
        title: 'Admin Dashboard',
        description: 'Akses panel administrasi utama',
        loginRoute: '/login'
    },
    kehadiran: {
        title: 'Admin Kehadiran', 
        description: 'Kelola data kehadiran peserta',
        loginRoute: '/admin-kehadiran/login'
    },
    bilik: {
        title: 'Bilik Pemilihan',
        description: 'Akses bilik voting pemilihan',
        loginRoute: '/bilik/login'
    }
};

// Form untuk admin dashboard (default)
const dashboardForm = useForm({
    email: '',
    password: '',
    remember: false,
});

// Form untuk admin kehadiran
const kehadiranForm = useForm({
    username: '',
    password: '',
    remember: false,
});

// Form untuk bilik pemilihan  
const bilikForm = useForm({
    username: '',
    password: '',
    remember: false,
});

const submitDashboard = () => {
    dashboardForm.post(tabConfig.dashboard.loginRoute);
};

const submitKehadiran = () => {
    kehadiranForm.post(tabConfig.kehadiran.loginRoute);
};

const submitBilik = () => {
    bilikForm.post(tabConfig.bilik.loginRoute);
};
</script>

<template>
    <Head title="Login System - Musyavote" />
    
    <div class="flex min-h-screen items-center justify-center bg-linear-to-br from-blue-50 to-gray-100 p-4">
        <div class="w-full max-w-lg">
            <Card class="w-full shadow-lg">
                <CardHeader class="text-center">
                    <CardTitle class="text-2xl font-bold text-gray-800">
                        Sistem Musyavote
                    </CardTitle>
                    <p class="text-sm text-gray-600 mt-2">
                        Pilih tipe akses yang sesuai dengan peran Anda
                    </p>
                </CardHeader>

                <CardContent class="">
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="grid w-full grid-cols-3 mb-6">
                            <TabsTrigger 
                                value="dashboard"
                                class="text-xs data-[state=active]:bg-blue-600 data-[state=active]:text-white"
                            >
                                Dashboard
                            </TabsTrigger>
                            <TabsTrigger 
                                value="kehadiran"
                                class="text-xs data-[state=active]:bg-green-600 data-[state=active]:text-white"
                            >
                                Kehadiran
                            </TabsTrigger>
                            <TabsTrigger 
                                value="bilik"
                                class="text-xs data-[state=active]:bg-purple-600 data-[state=active]:text-white"
                            >
                                Bilik
                            </TabsTrigger>
                        </TabsList>

                        <!-- TAB DASHBOARD -->
                        <TabsContent value="dashboard" class="space-y-4">
                            <div class="text-center mb-4">
                                <h3 class="font-semibold text-gray-800">
                                    {{ tabConfig.dashboard.title }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ tabConfig.dashboard.description }}
                                </p>
                            </div>

                            <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                                {{ status }}
                            </div>

                            <form @submit.prevent="submitDashboard" class="flex flex-col gap-6">
                                <div class="grid gap-6">
                                    <div class="grid gap-2">
                                        <Label for="email">Email address</Label>
                                        <Input 
                                            id="email" 
                                            type="email" 
                                            v-model="dashboardForm.email"
                                            required 
                                            autofocus 
                                            :tabindex="1"
                                            autocomplete="email" 
                                            placeholder="email@example.com" 
                                        />
                                        <InputError :message="dashboardForm.errors.email" />
                                    </div>

                                    <div class="grid gap-2">
                                        <div class="flex items-center justify-between">
                                            <Label for="password">Password</Label>
                                        </div>
                                        <Input 
                                            id="password" 
                                            type="password" 
                                            v-model="dashboardForm.password"
                                            required 
                                            :tabindex="2"
                                            autocomplete="current-password" 
                                            placeholder="Password" 
                                        />
                                        <InputError :message="dashboardForm.errors.password" />
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <Label for="remember" class="flex items-center space-x-3">
                                            <Checkbox 
                                                id="remember" 
                                                v-model:checked="dashboardForm.remember" 
                                                :tabindex="3" 
                                            />
                                            <span>Remember me</span>
                                        </Label>
                                    </div>

                                    <Button 
                                        type="submit" 
                                        class="mt-4 w-full" 
                                        :tabindex="4" 
                                        :disabled="dashboardForm.processing"
                                        data-test="login-button"
                                    >
                                        <LoaderCircle v-if="dashboardForm.processing" class="h-4 w-4 animate-spin" />
                                        Log in Dashboard
                                    </Button>
                                </div>

                                <!-- <div class="text-center text-sm text-muted-foreground" v-if="canRegister">
                                    Don't have an account?
                                    <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
                                </div> -->
                            </form>
                        </TabsContent>

                        <!-- TAB KEHADIRAN -->
                        <TabsContent value="kehadiran" class="space-y-4">
                            <div class="text-center mb-4">
                                <h3 class="font-semibold text-gray-800">
                                    {{ tabConfig.kehadiran.title }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ tabConfig.kehadiran.description }}
                                </p>
                            </div>

                            <form @submit.prevent="submitKehadiran" class="flex flex-col gap-6">
                                <div class="grid gap-6">
                                    <div class="grid gap-2">
                                        <Label for="username-kehadiran">Username</Label>
                                        <Input 
                                            id="username-kehadiran" 
                                            type="text" 
                                            v-model="kehadiranForm.username"
                                            required 
                                            autofocus 
                                            autocomplete="username" 
                                            placeholder="Masukkan username admin kehadiran" 
                                        />
                                        <InputError :message="kehadiranForm.errors.username" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="password-kehadiran">Password</Label>
                                        <Input 
                                            id="password-kehadiran" 
                                            type="password" 
                                            v-model="kehadiranForm.password"
                                            required 
                                            autocomplete="current-password" 
                                            placeholder="Masukkan password" 
                                        />
                                        <InputError :message="kehadiranForm.errors.password" />
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="remember-kehadiran" 
                                            v-model:checked="kehadiranForm.remember" 
                                        />
                                        <Label for="remember-kehadiran" class="text-sm">
                                            Ingat saya
                                        </Label>
                                    </div>

                                    <Button 
                                        type="submit" 
                                        class="w-full" 
                                        :disabled="kehadiranForm.processing"
                                    >
                                        <LoaderCircle v-if="kehadiranForm.processing" class="h-4 w-4 animate-spin" />
                                        {{ kehadiranForm.processing ? 'Memproses...' : 'Login Admin Kehadiran' }}
                                    </Button>
                                </div>
                            </form>
                        </TabsContent>

                        <!-- TAB BILIK -->
                        <TabsContent value="bilik" class="space-y-4">
                            <div class="text-center mb-4">
                                <h3 class="font-semibold text-gray-800">
                                    {{ tabConfig.bilik.title }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ tabConfig.bilik.description }}
                                </p>
                            </div>

                            <form @submit.prevent="submitBilik" class="flex flex-col gap-6">
                                <div class="grid gap-6">
                                    <div class="grid gap-2">
                                        <Label for="username-bilik">Username Bilik</Label>
                                        <Input 
                                            id="username-bilik" 
                                            type="text" 
                                            v-model="bilikForm.username"
                                            required 
                                            autofocus 
                                            autocomplete="username" 
                                            placeholder="Masukkan username bilik" 
                                        />
                                        <InputError :message="bilikForm.errors.username" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="password-bilik">Password</Label>
                                        <Input 
                                            id="password-bilik" 
                                            type="password" 
                                            v-model="bilikForm.password"
                                            required 
                                            autocomplete="current-password" 
                                            placeholder="Masukkan password" 
                                        />
                                        <InputError :message="bilikForm.errors.password" />
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="remember-bilik" 
                                            v-model:checked="bilikForm.remember" 
                                        />
                                        <Label for="remember-bilik" class="text-sm">
                                            Ingat saya
                                        </Label>
                                    </div>

                                    <Button 
                                        type="submit" 
                                        class="w-full" 
                                        :disabled="bilikForm.processing"
                                    >
                                        <LoaderCircle v-if="bilikForm.processing" class="h-4 w-4 animate-spin" />
                                        {{ bilikForm.processing ? 'Memproses...' : 'Login Bilik Pemilihan' }}
                                    </Button>
                                </div>
                            </form>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </div>
    </div>
</template>