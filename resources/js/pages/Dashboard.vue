<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Users,
    Trophy,
    Users2,
    Building,
    PieChart,
    Calendar,
    User,
    UserCheck,
    TrendingUp,
    Activity,
    BarChart3
} from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps<{
    peserta: Array<any>;
    totalPeserta: number;
    kehadiranStats: any;
    jenisKelaminStats: any;
    bilikStats: any;
    calonStats: any;
}>();

// Calculate bilik ratio
const bilikRatio = computed(() => {
    if (!props.bilikStats?.total || props.bilikStats.total === 0) return 0;
    return Math.round(props.totalPeserta / props.bilikStats.total);
});

// Calculate attendance percentages
const attendancePercentages = computed(() => {
    const percentages: any = {};
    Object.keys(props.kehadiranStats || {}).forEach(key => {
        percentages[key] = props.totalPeserta > 0
            ? Math.round((props.kehadiranStats[key] / props.totalPeserta) * 100)
            : 0;
    });
    return percentages;
});

// Calculate total attendance
const totalAttendance = computed(() => {
    return Object.values(props.kehadiranStats || {}).reduce((sum: number, val: any) => sum + val, 0);
});

// Calculate attendance rate
const attendanceRate = computed(() => {
    const totalSessions = 4;
    const totalPossibleAttendance = props.totalPeserta * totalSessions;
    return totalPossibleAttendance > 0
        ? Math.round((totalAttendance.value / totalPossibleAttendance) * 100)
        : 0;
});

// Gender percentages
const malePercentage = computed(() => {
    return props.jenisKelaminStats?.laki_laki?.percentage || 0;
});

const femalePercentage = computed(() => {
    return props.jenisKelaminStats?.perempuan?.percentage || 0;
});

// Recent participants
const recentParticipants = computed(() => {
    return [...props.peserta]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 5);
});

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

// Stats cards data
const statsCards = computed(() => [
    {
        title: 'Total Peserta',
        value: props.totalPeserta,
        icon: Users,
        color: 'bg-blue-500',
        textColor: 'text-blue-500',
        bgColor: 'bg-blue-50',
        darkBgColor: 'dark:bg-blue-900/20',
        description: 'Orang terdaftar'
    },
    {
        title: 'Total Calon Ketua',
        value: props.calonStats?.calon_ketua || 0,
        icon: Trophy,
        color: 'bg-amber-500',
        textColor: 'text-amber-500',
        bgColor: 'bg-amber-50',
        darkBgColor: 'dark:bg-amber-900/20',
        description: 'Kandidat'
    },
    {
        title: 'Total Calon Formatur',
        value: props.calonStats?.calon_formatur || 0,
        icon: Users2,
        color: 'bg-purple-500',
        textColor: 'text-purple-500',
        bgColor: 'bg-purple-50',
        darkBgColor: 'dark:bg-purple-900/20',
        description: 'Kandidat'
    },
    {
        title: 'Total Bilik',
        value: props.bilikStats?.total || 0,
        icon: Building,
        color: 'bg-emerald-500',
        textColor: 'text-emerald-500',
        bgColor: 'bg-emerald-50',
        darkBgColor: 'dark:bg-emerald-900/20',
        description: `${bilikRatio.value} peserta/bilik`
    }
]);

// Attendance session data
const attendanceSessions = computed(() => [
    {
        name: 'Pleno 1',
        value: props.kehadiranStats?.pleno_1 || 0,
        percentage: attendancePercentages.value.pleno_1 || 0,
        color: 'bg-blue-500',
        iconColor: 'text-blue-600 dark:text-blue-400'
    },
    {
        name: 'Pleno 2',
        value: props.kehadiranStats?.pleno_2 || 0,
        percentage: attendancePercentages.value.pleno_2 || 0,
        color: 'bg-emerald-500',
        iconColor: 'text-emerald-600 dark:text-emerald-400'
    },
    {
        name: 'Pleno 3',
        value: props.kehadiranStats?.pleno_3 || 0,
        percentage: attendancePercentages.value.pleno_3 || 0,
        color: 'bg-amber-500',
        iconColor: 'text-amber-600 dark:text-amber-400'
    },
    {
        name: 'Pleno 4',
        value: props.kehadiranStats?.pleno_4 || 0,
        percentage: attendancePercentages.value.pleno_4 || 0,
        color: 'bg-purple-500',
        iconColor: 'text-purple-600 dark:text-purple-400'
    }
]);
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats Grid - 4 Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="(card, index) in statsCards" :key="index"
                    class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900" :class="[
                        card.bgColor,
                        card.darkBgColor,
                        'border border-gray-200 dark:border-gray-800'
                    ]">
                    <div class="flex items-start justify-between">
                        <div class="flex flex-col">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ card.title }}
                            </p>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                {{ card.value }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ card.description }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg" :class="card.bgColor">
                            <component :is="card.icon" class="h-6 w-6" :class="card.textColor" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demografi & Kehadiran dalam Satu Baris -->

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Kolom 1: Presentasi Kehadiran -->
                <div
                    class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-emerald-50 p-2 dark:bg-emerald-900/20">
                                <BarChart3 class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Presentasi Kehadiran
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Kehadiran per sesi pleno
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Stats -->
                    <div class="space-y-4">
                        <div v-for="session in attendanceSessions" :key="session.name" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg flex items-center justify-center"
                                        :class="session.color.replace('bg-', 'bg-').replace('-500', '-100') + ' dark:' + session.color.replace('bg-', 'bg-').replace('-500', '-900/30')">
                                        <UserCheck class="h-4 w-4" :class="session.iconColor" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ session.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ session.value }} dari {{ totalPeserta }} peserta
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ session.percentage }}%
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Presentase
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-full rounded-full transition-all duration-500" :class="session.color"
                                    :style="{ width: `${session.percentage}%` }"></div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- Kolom 2: Demografi Jenis Kelamin -->
                <div
                    class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
                                <PieChart class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Demografi Jenis Kelamin
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Distribusi peserta berdasarkan gender
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pie Chart dan Stats -->
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                        <!-- Pie Chart -->
                        <div class="relative">
                            <svg viewBox="0 0 100 100" class="h-48 w-48 md:h-56 md:w-56">
                                <!-- Background Circle -->
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f3f4f6" stroke-width="20" />

                                <!-- Male Segment -->
                                <circle v-if="malePercentage > 0" cx="50" cy="50" r="40" fill="none" stroke="#3b82f6"
                                    stroke-width="20" stroke-dasharray="251.2"
                                    :stroke-dashoffset="251.2 - (251.2 * malePercentage / 100)" stroke-linecap="round"
                                    transform="rotate(-90 50 50)" />

                                <!-- Female Segment -->
                                <circle v-if="femalePercentage > 0" cx="50" cy="50" r="40" fill="none" stroke="#ec4899"
                                    stroke-width="20" stroke-dasharray="251.2"
                                    :stroke-dashoffset="251.2 - (251.2 * femalePercentage / 100)" stroke-linecap="round"
                                    :transform="`rotate(${-90 + (360 * malePercentage / 100)} 50 50)`" />

                            </svg>
                        </div>

                        <!-- Gender Stats -->
                        <div class="flex-1 min-w-0">
                            <div class="space-y-6">
                                <!-- Male Stats -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Laki-laki
                                            </p>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ jenisKelaminStats?.laki_laki?.count || 0 }}
                                            </span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                orang
                                            </span>
                                        </div>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-full rounded-full bg-blue-500 transition-all duration-500"
                                            :style="{ width: `${malePercentage}%` }"></div>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Persentase
                                        </span>
                                        <span class="font-medium text-blue-600 dark:text-blue-400">
                                            {{ malePercentage }}%
                                        </span>
                                    </div>
                                </div>

                                <!-- Female Stats -->
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 w-3 rounded-full bg-pink-500"></div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Perempuan
                                            </p>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                                {{ jenisKelaminStats?.perempuan?.count || 0 }}
                                            </span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                orang
                                            </span>
                                        </div>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-full rounded-full bg-pink-500 transition-all duration-500"
                                            :style="{ width: `${femalePercentage}%` }"></div>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">
                                            Persentase
                                        </span>
                                        <span class="font-medium text-pink-600 dark:text-pink-400">
                                            {{ femalePercentage }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Smooth transitions */
* {
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.dark ::-webkit-scrollbar-track {
    background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark ::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Animation for progress bars */
.progress-bar {
    transition: width 1s ease-in-out;
}
</style>