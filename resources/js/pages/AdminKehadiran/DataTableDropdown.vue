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
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link } from '@inertiajs/vue3';
import { MoreVertical } from 'lucide-vue-next';

const props = defineProps<{
    adminId: string;
}>();
</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="m-0 h-8 w-4 cursor-pointer p-0">
                    <span class="sr-only">Open menu</span>
                    <MoreVertical class="h-4 w-0" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <DropdownMenuItem class="w-full hover:bg-slate-100">
                    <Link
                        :href="`/admin-presensi/${props.adminId}/edit/`"
                        class="w-full"
                    >
                        Edit Admin
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem
                    class="w-full cursor-pointer hover:bg-slate-100"
                >
                    <AlertDialogTrigger as-child>
                        <button
                            variant="default"
                            class="w-full cursor-pointer text-left"
                        >
                            Hapus Admin
                        </button>
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
                    <Link
                        :href="`/admin-presensi/${props.adminId}`"
                        class="w-full cursor-pointer text-left"
                        method="delete"
                        as="button"
                    >
                        Hapus
                    </Link>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>