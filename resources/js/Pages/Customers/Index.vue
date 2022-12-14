<template>
    <admin-layout>

        <template v-slot:company_name>
            <div>{{ company.name }}</div>
        </template>

        <v-alert type="success" border="left" dismissible v-if="$page.props.flash.message">
            {{ $page.props.flash.message }}
        </v-alert>

        <!-- Alertas -->
        <div v-if="$page.props.errorBags.default">
            <div v-for="item in Object.keys($page.props.errors)" :key="item">
                <v-alert type="warning" border="left" dismissible>
                    {{ $page.props.errors[item] }}
                </v-alert>
            </div>
        </div>
        <!-- Fin de Alertas -->

        <v-data-table :headers="headers" :items="desserts" class="elevation-24" :search="search"
            :footer-props="{ itemsPerPageText: 'Filas por página' }">
            <template v-slot:top>
                <v-toolbar flat>
                    <!-- Comenario de prueba -->
                    <v-toolbar-title>Lista de Clientes</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-dialog v-model="dialog" max-width="600px">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                                Agregar Cliente
                            </v-btn>
                            <v-spacer></v-spacer>
                            <v-text-field v-model="search" append-icon="mdi-magnify" label="Buscar" single-line
                                hide-details></v-text-field>
                        </template>
                        <v-card>
                            <!-- Titulo de modal agregar  -->
                            <v-card-title>
                                <span class="text-h5">{{ formTitle }}</span>
                            </v-card-title>

                            <!-- contenido de modal agregar  -->
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12" sm="6" md="6" v-if="$page.props.user.role == 'master'">
                                            <v-select v-model="editedItem.companies_id" hint="Seleccione empresa"
                                                :items="companies" item-text="name" item-value="id"
                                                label="Seleccione Empresa" single-line></v-select>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.name" label="Nombre o Razon Social*"
                                                :rules="requiredField" required>
                                            </v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.document" type="number" label="DNI/RUC*"
                                                min="0" :rules="requiredDocument" required>
                                            </v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.phone" type="number" label="Teléfono"
                                                min="0" required>
                                            </v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.address" label="Dirección" required>
                                            </v-text-field>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="4">
                                            <v-text-field v-model="editedItem.email" type="email"
                                                label="Correo Electrónico" :rules="requiredEmail" required>
                                            </v-text-field>
                                        </v-col>
                                    </v-row>
                                </v-container>
                                <small>*Datos
                                    obligatorios</small>
                            </v-card-text>
                            <!-- Acciones de modal agregar  -->
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" text @click="close">
                                    Cancelar
                                </v-btn>
                                <v-btn color="blue darken-1" text @click="save">
                                    Guardar
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                    <!-- Mensaje de confirmacion de borrado de Marca -->
                    <v-dialog v-model="dialogDelete" max-width="500px">
                        <v-card>
                            <v-card-title class="text-h5">
                                ¿Está seguro de querer borrar el Cliente?
                            </v-card-title>
                            <div class="ml-12">
                                • Se borrará toda información relacionada con el Cliente
                            </div>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="red darken-1" text @click="closeDelete">Cancelar</v-btn>
                                <v-btn color="blue darken-1" text @click="deleteItemConfirm">Confirmar</v-btn>
                                <v-spacer></v-spacer>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-toolbar>
            </template>
            <!-- Acciones de la tabla -->
            <template v-slot:[`item.actions`]="{ item }">
                <v-icon small class="mr-2" @click="editItem(item)">
                    mdi-pencil
                </v-icon>
                <v-icon small @click="deleteItem(item)">
                    mdi-delete
                </v-icon>
            </template>
        </v-data-table>

    </admin-layout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout'
import route from '../../../../vendor/tightenco/ziggy/src/js'

export default {
    props: ['customers', 'companies', 'company'],
    components: {
        AdminLayout,
    },
    data() {
        return {
            // Validaciones
            requiredField: [
                v => !!v || '*Campo Ogligatorio',
            ],
            requiredDocument: [
                v => !!v || '*Campo Ogligatorio',
                v => (v && v.length == 11 || v.length == 8) || 'El documento debe tener 8 u 11 caracteres',
            ],
            requiredEmail: [
                v => /.+@.+\..+/.test(v) || 'Correo no válido',
            ],

            dialog: false,
            dialogDelete: false,
            search: '',
            snackbar: false,
            headers: [
                { text: 'NOMBRE', value: 'name' },
                { text: 'DOCUMENTO', value: 'document' },
                { text: 'TELÉFONO', value: 'phone' },
                { text: 'DIRECCIÓN', value: 'address' },
                { text: 'CORREO', value: 'email' },
                { text: 'ACCIONES', value: 'actions', sortable: false },
            ],
            desserts: [],

            editedIndex: -1,

            editedItem: {
                companies_id: this.$page.props.user.companies_id,
                name: '',
                document: '',
                phone: '',
                address: '',
                email: '',
            },

            defaultItem: {
                companies_id: this.$page.props.user.companies_id,
                name: '',
                document: '',
                phone: '',
                address: '',
                email: '',
            },

        }
    },

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Nuevo Cliente' : 'Editar Cliente'
        },
    },

    watch: {

        dialog(val) {
            val || this.close()
        },

        dialogDelete(val) {
            val || this.closeDelete()
        },
    },

    created() {
        this.initialize();
    },


    // Para que agregue en el data table despues de saber que no hay errores en
    // en el formulario de crear
    updated() {
        if (Object.values(this.$page.props.errors).length == 0) {

            this.initialize();

        } else {
            console.log('Hay ' + Object.values(this.$page.props.errors).length + ' errores');
            console.log(this.$page.props.errors)
        }

    },

    methods: {
        initialize() {
            this.desserts = this.customers
        },

        editItem(item) {
            this.editedIndex = this.desserts.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },

        deleteItem(item) {
            this.editedIndex = this.desserts.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialogDelete = true

        },

        deleteItemConfirm() {
            this.desserts.splice(this.editedIndex, 1)
            this.closeDelete()

            // ***************************************
            // enviando formulario de eliminar
            this.$inertia.delete(this.route('customers.destroy', this.editedItem))
        },

        close() {
            this.dialog = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        save() {
            if (this.editedIndex > -1) {

                // esto agragaba el item a la tabla con solo javascrip
                //pero ya no es necesario porque se renderiza el componente desde
                // el servidor
                // Object.assign(this.desserts[this.editedIndex], this.editedItem)

                // Update
                // ***************************************
                // enviado formulario de almacenar
                this.$inertia.patch(route('customers.update', this.editedItem), this.editedItem)

            } else {

                // Store
                // ***************************************
                // enviado formulario de almacenar
                this.$inertia.post(route('customers.store'), this.editedItem)

                // this.desserts.push(this.editedItem)

            }

            this.close()
        },
    },
}
</script>
