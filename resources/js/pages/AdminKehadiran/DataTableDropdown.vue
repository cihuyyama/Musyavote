<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { MoreVertical, Edit, Trash2, KeyRound, Power } from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

interface Props {
    adminId: string;
    adminStatus?: string;
}

const props = defineProps<Props>();

const isActive = ref(props.adminStatus === 'active');
const isLoading = ref(false);

const toggleStatus = async () => {
    if (isLoading.value) return;

    isLoading.value = true;
    const newStatus = !isActive.value;
    const routeName = newStatus ? `/admin-presensi/${props.adminId}/activate` : `/admin-presensi/${props.adminId}/deactivate`;

    try {
        await router.post(routeName, {}, {
            onSuccess: () => {
                isActive.value = newStatus;
                const action = newStatus ? 'diaktifkan' : 'dinonaktifkan';
                toast.success(`Admin berhasil ${action}`);
            },
            onError: () => {
                const action = newStatus ? 'mengaktifkan' : 'menonaktifkan';
                toast.error(`Gagal ${action} admin`);
            },
            onFinish: () => {
                isLoading.value = false;
            }
        });
    } catch (error) {
        toast.error('Terjadi kesalahan');
        isLoading.value = false;
    }
};

// const resetPassword = () => {
//     router.post(`/admin-presensi/${props.adminId}/reset-password`, {}, {
//         onSuccess: (page) => {
//             const newPassword = page.props.flash?.new_password;
//             if (newPassword) {
//                 toast.success(`Password berhasil direset: ${newPassword}`, {
//                     duration: 10000,
//                     closeButton: true
//                 });
//             }
//         },
//         onError: () => {
//             toast.error('Gagal reset password');
//         }
//     });
// };
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="m-0 h-8 w-8 cursor-pointer p-0">
                    <span class="sr-only">Open menu</span>
                    <MoreVertical class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" class="w-56">
                <DropdownMenuLabel>Aksi Admin</DropdownMenuLabel>

                <!-- Switch Toggle Status -->
                <DropdownMenuItem class="flex items-center justify-between px-3 py-2 hover:bg-slate-100">
                    <div class="flex items-center space-x-2">
                        <Power class="h-4 w-4" />
                        <span>{{ isActive ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <button @click="toggleStatus" :disabled="isLoading"
                        class="relative cursor-pointer inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50"
                        :class="[
                            isActive
                                ? 'bg-green-600 focus:ring-green-600 hover:bg-green-700'
                                : 'bg-gray-200 focus:ring-gray-400 hover:bg-gray-300'
                        ]">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                            :class="[
                                isActive ? 'translate-x-6' : 'translate-x-1',
                                isLoading ? 'opacity-50' : ''
                            ]">
                            <span v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                                <div class="h-3 w-3 animate-spin rounded-full border-2 border-gray-300 border-t-white">
                                </div>
                            </span>
                        </span>
                    </button>
                </DropdownMenuItem>

                <DropdownMenuSeparator />

                <!-- Edit Admin -->
                <DropdownMenuItem class="cursor-pointer hover:bg-slate-100">
                    <Link :href="`/admin-presensi/${adminId}/edit/`" class="flex w-full items-center space-x-2">
                        <Edit class="h-4 w-4" />
                        <span>Edit Admin</span>
                    </Link>
                </DropdownMenuItem>

                <!-- Reset Password -->
                <!-- <DropdownMenuItem 
                    @click="resetPassword"
                    class="cursor-pointer hover:bg-slate-100"
                >
                    <div class="flex items-center space-x-2">
                        <KeyRound class="h-4 w-4" />
                        <span>Reset Password</span>
                    </div>
                </DropdownMenuItem> -->

                <DropdownMenuSeparator />

                <!-- Delete Admin -->
                <DropdownMenuItem class="cursor-pointer text-red-600 hover:bg-red-50">
                    <AlertDialogTrigger as-child>
                        <div class="flex items-center space-x-2">
                            <Trash2 class="h-4 w-4" />
                            <span>Hapus Admin</span>
                        </div>
                    </AlertDialogTrigger>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                <AlertDialogDescription>
                    Data admin yang dihapus tidak dapat dikembalikan.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel class="cursor-pointer">
                    Batal
                </AlertDialogCancel>
                <AlertDialogAction>
                    <Link :href="`/admin-presensi/${adminId}`" class="w-full cursor-pointer text-left" method="delete"
                        as="button">
                        Hapus
                    </Link>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

<style scoped>
.switch-toggle {
    transition: background-color 0.2s ease-in-out;
}

.switch-thumb {
    transition: transform 0.2s ease-in-out;
}

.switch-container {
    position: relative;
    display: inline-flex;
    height: 1.5rem;
    width: 2.75rem;
    align-items: center;
    border-radius: 9999px;
    transition: background-color 0.2s ease-in-out;
}

.switch-thumb {
    position: absolute;
    height: 1rem;
    width: 1rem;
    border-radius: 50%;
    background-color: white;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    transition: transform 0.2s ease-in-out;
}

.switch-thumb.active {
    transform: translateX(1.25rem);
}

.switch-loading {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>