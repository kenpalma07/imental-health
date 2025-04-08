<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';

import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast';
import { toTypedSchema } from '@vee-validate/zod';
import { h } from 'vue';
import * as z from 'zod';
//import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { emit } from 'process';

const model = defineModel();

const emit = defineEmits(['submit', 'close']);

const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(2),
        guard_name: z.string().min(2),
    }),
);

function onSubmit(values: any) {
    axios
        .post('/iam/roles', values)
        .then((reponse) => {
            toast({
                title: 'Success',
                description: 'Successfully created.',
            });
            model.value = false;
            emit('submit');
        })
        .catch((error) => {
            toast({
                title: 'Uh oh! Something went wrong.',
                //description: 'There was a problem with your request.',
                variant: 'destructive',
                description: h(
                    'div',
                    { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
                    h(
                        'code',
                        { class: 'text-white' },
                        error.status == 500 ? error.response.statusText : JSON.stringify(error.response.data.errors, null, 2),
                    ),
                ),
            });
        });
}
</script>

<template>
    <Form v-slot="{ handleSubmit }" as="" keep-values :validation-schema="formSchema">
        <Dialog v-model:open="model">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Role Form</DialogTitle>
                    <DialogDescription> Create new role. </DialogDescription>
                </DialogHeader>

                <form id="dialogForm" @submit="handleSubmit($event, onSubmit)">
                    <FormField v-slot="{ componentField }" name="name">
                        <FormItem>
                            <FormLabel>Role Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-bind="componentField" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="guard_name">
                        <FormItem>
                            <FormLabel>Guard Name</FormLabel>
                            <FormControl>
                                <Input type="text" placeholder="web or api" v-bind="componentField" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </form>

                <DialogFooter>
                    <Button type="submit" form="dialogForm"> Create </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </Form>
</template>
