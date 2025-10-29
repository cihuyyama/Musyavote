<script setup lang="ts">
import { MoreVertical } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Link } from '@inertiajs/vue3';
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
} from "@/components/ui/alert-dialog"

const props = defineProps<{
    pesertaId: string
}>()

</script>

<template>
    <AlertDialog>
        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="w-4 h-8 p-0 m-0 cursor-pointer">
                    <span class="sr-only">Open menu</span>
                    <MoreVertical class="w-0 h-4 " />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <DropdownMenuItem class="w-full hover:bg-slate-100">
                    <Link :href="`/peserta/${props.pesertaId}/edit/`" class="w-full">
                    Edit Peserta
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem class="w-full cursor-pointer hover:bg-slate-100">
                    <AlertDialogTrigger as-child>
                        <button variant="default" class="w-full text-left cursor-pointer">
                            Hapus Peserta
                        </button>
                    </AlertDialogTrigger>

                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete your
                    account and remove your data from our servers.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>
                    Cancel
                </AlertDialogCancel>
                <AlertDialogAction>
                    <Link :href="`/peserta/${props.pesertaId}`" class="w-full text-left cursor-pointer" method="delete"
                        as="button">
                    Delete
                    </Link>
                </AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
        
    </AlertDialog>
</template>