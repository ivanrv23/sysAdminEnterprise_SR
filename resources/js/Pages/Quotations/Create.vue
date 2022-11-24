<template>
    <admin-layout>

        <v-alert type="success" border="left" dismissible v-if="$page.props.flash.message">
            {{ $page.props.flash.message }}
        </v-alert>

        <!-- Alertas -->
        <div v-if="$page.props.errorBags.default"
            style="position: absolute; right: 10px; top: 20px; z-index:3; opacity: 0.9;">
            <div v-for="item in Object.keys($page.props.errors)" :key="item">
                <v-alert type="warning" border="left" dismissible>
                    {{ $page.props.errors[item] }}
                </v-alert>
            </div>
        </div>
        <!-- Fin de Alertas -->
        <v-container>
            <v-layout>
                <v-flex>
                    <v-card class="overflow-hidden" elevation-10 loading>
                        <v-toolbar flat color="blue-grey lighten-4">
                            <v-icon>mdi-file-document-edit</v-icon>
                            <v-toolbar-title class="font-weight-light">
                                Nueva Cotización
                            </v-toolbar-title>
                            <v-spacer></v-spacer>
                            <inertia-link :href="route('quotations.index')">
                                <v-btn fab small>
                                    <v-icon color="primary">
                                        mdi-cards-variant
                                    </v-icon>
                                </v-btn>
                            </inertia-link>
                        </v-toolbar>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12">
                                        <h3>DATOS GENERALES</h3>
                                    </v-col>
                                    <v-col cols="12" sm="4" md="3">
                                        <v-text-field label="N° Cotización" v-model="form.nroCotizacion" outlined dense>
                                        </v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="3">
                                        <v-text-field type="date" v-model="form.date" label="Fecha" outlined dense>
                                        </v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="3">
                                        <v-autocomplete color="primary" :items="customers" item-text="document"
                                            v-model="datosCliente" item-value="id" label="Cliente" auto-select-first
                                            hide-no-data hide-selected placeholder="Buscar por Documento"
                                            persistent-hint return-object required outlined dense>
                                        </v-autocomplete>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="3">
                                        <v-text-field label="Nombre/Razon Social" :value="datosCliente.name" readonly
                                            outlined dense>
                                        </v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <h3>DATOS DE COTIZACIÓN</h3>
                                    </v-col>
                                    <!-- <v-col cols="12" sm="6" md="2">
                                        <v-select :items="coins" label="Moneda" item-text="code" @change="changeMoneda"
                                            item-value="code" v-model="form.coins" return-object outlined dense>
                                        </v-select>
                                    </v-col> -->
                                    <v-col cols="12" sm="2" md="2">
                                        <v-select v-if="parseFloat(form.total) > 0" readonly :items="coins"
                                            label="Moneda" item-text="code" item-value="code" v-model="form.coins"
                                            @change="changeMoneda" return-object outlined dense>
                                        </v-select>

                                        <v-select v-else :items="coins" label="Moneda" item-text="code"
                                            item-value="code" v-model="form.coins" @change="changeMoneda" return-object
                                            outlined dense>
                                        </v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="2" v-if="form.coins.code == 'USD'">
                                        <v-text-field v-if="parseFloat(form.total) > 0" readonly v-model="tipoCambio"
                                            label="Tipo Cambio" type="number" min="0" outlined dense>
                                        </v-text-field>
                                        <v-text-field v-else v-model="tipoCambio" label="Tipo Cambio" type="number"
                                            min="0" outlined dense>
                                        </v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="3">
                                        <v-select v-if="parseFloat(form.total) > 0" readonly :items="igvs"
                                            v-model="form.tipo_igv" item-text="tipo" item-value="tipo" label="Tipo IGV"
                                            outlined dense></v-select>
                                        <v-select v-else :items="igvs" v-model="form.tipo_igv" item-text="tipo"
                                            item-value="tipo" label="Tipo IGV" outlined dense></v-select>
                                    </v-col>
                                    <!-- <v-col cols="12" sm="6" md="2">
                                        <v-text-field label="Transporte" type="number" v-model="form.transporte"
                                            outlined dense>
                                        </v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="2">
                                        <v-text-field label="Descuento" type="number" v-model="form.descuento"
                                            outlined dense>
                                        </v-text-field>
                                    </v-col> -->
                                    <v-col cols="12" sm="6" md="3">
                                        <v-text-field label="Total" type="number" v-model="form.total" outlined
                                            :prefix="simboloMoneda" readonly dense>
                                        </v-text-field>
                                    </v-col>
                                    <!-- Dialog add Productos a carrito -->
                                    <v-col cols="12" sm="1" md="1">
                                        <template>
                                            <div class="text-center">
                                                <v-dialog v-model="dialogAddProducts" width="600" persistent>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-btn color="primary" class="white--text" fab v-bind="attrs"
                                                            v-on="on">
                                                            <v-icon dark>
                                                                mdi-cart
                                                            </v-icon>
                                                        </v-btn>
                                                    </template>
                                                    <v-card>
                                                        <v-card-title class="text-h5 grey lighten-2">
                                                            {{ formTitle }}
                                                        </v-card-title>
                                                        <v-card-text>
                                                            <v-container>
                                                                <div class="text-center">
                                                                    <v-row>
                                                                        <v-col cols="12" sm="6" md="6">
                                                                            <v-autocomplete color="primary"
                                                                                :items="products" item-value="id"
                                                                                v-model="editedItem.datosProducto"
                                                                                :item-text="getProductText"
                                                                                label="Producto" auto-select-first
                                                                                hide-no-data hide-selected
                                                                                placeholder="Seleccione Producto"
                                                                                persistent-hint return-object required
                                                                                @change="changeProduct" outlined dense
                                                                                :hint="editedItem.datosProducto != '' ? 'Stock: ' + editedItem.datosProducto.stock : 'Stock: 0'">
                                                                            </v-autocomplete>
                                                                        </v-col>
                                                                        <v-col cols="12" sm="6" md="6">
                                                                            <v-autocomplete color="primary"
                                                                                v-model="editedItem.datosPresentation"
                                                                                :items="presentations"
                                                                                :item-text="getPresentationText"
                                                                                item-value="id" label="Presentación"
                                                                                auto-select-first hide-no-data
                                                                                hide-selected
                                                                                placeholder="Seleccione Presentación"
                                                                                persistent-hint return-object
                                                                                @change="changePresentation" required
                                                                                outlined dense>
                                                                            </v-autocomplete>
                                                                        </v-col>
                                                                        <v-col cols="12" sm="6" md="3">
                                                                            <v-text-field v-model="editedItem.quantity"
                                                                                label="Cantidad" type="number" min="0"
                                                                                required outlined dense>
                                                                            </v-text-field>
                                                                        </v-col>
                                                                        <v-col cols="9" sm="6" md="3">
                                                                            <v-text-field
                                                                                v-model="editedItem.sale_price"
                                                                                label="Precio unitario" type="number"
                                                                                min="0" required outlined dense>
                                                                            </v-text-field>
                                                                        </v-col>
                                                                        <!--Dialog Lista de Precios -->
                                                                        <v-col cols="2" sm="2" md="2">

                                                                            <template>
                                                                                <div class="text-center">
                                                                                    <v-dialog v-model="dialogPrecios"
                                                                                        max-width="250">
                                                                                        <template
                                                                                            v-slot:activator="{ on, attrs }">
                                                                                            <v-btn color="#546E7A" small
                                                                                                class="ma-2 white--text"
                                                                                                fab v-bind="attrs"
                                                                                                v-on="on">
                                                                                                <v-icon dark>
                                                                                                    mdi-filter-variant
                                                                                                </v-icon>
                                                                                            </v-btn>
                                                                                        </template>
                                                                                        <v-card>
                                                                                            <v-card-title
                                                                                                class="text-h5 grey lighten-2">
                                                                                                LISTA DE PRECIOS
                                                                                            </v-card-title>
                                                                                            <v-card-text>
                                                                                                <v-container>
                                                                                                    <v-row>
                                                                                                        <v-col cols="9">
                                                                                                            <v-text-field
                                                                                                                label="Precio Menor"
                                                                                                                type="number"
                                                                                                                v-model="editedItem.price_by_unit"
                                                                                                                readonly
                                                                                                                outlined
                                                                                                                dense>
                                                                                                            </v-text-field>
                                                                                                        </v-col>
                                                                                                        <v-col cols="2">
                                                                                                            <v-btn
                                                                                                                color="amber accent-3"
                                                                                                                small
                                                                                                                class="ma-2 white--text"
                                                                                                                fab
                                                                                                                @click="addPrice1">
                                                                                                                <v-icon
                                                                                                                    dark>
                                                                                                                    mdi-bookmark-check
                                                                                                                </v-icon>
                                                                                                            </v-btn>
                                                                                                        </v-col>
                                                                                                        <v-col cols="9">
                                                                                                            <v-text-field
                                                                                                                label="Precio Mayor"
                                                                                                                type="number"
                                                                                                                v-model="editedItem.wholesale_price"
                                                                                                                readonly
                                                                                                                outlined
                                                                                                                dense>
                                                                                                            </v-text-field>
                                                                                                        </v-col>
                                                                                                        <v-col cols="2">
                                                                                                            <v-btn
                                                                                                                color="amber accent-3"
                                                                                                                small
                                                                                                                class="ma-2 white--text"
                                                                                                                fab
                                                                                                                @click="addPrice2">
                                                                                                                <v-icon
                                                                                                                    dark>
                                                                                                                    mdi-bookmark-check
                                                                                                                </v-icon>
                                                                                                            </v-btn>
                                                                                                        </v-col>
                                                                                                        <v-col cols="9">
                                                                                                            <v-text-field
                                                                                                                label="Precio Especial"
                                                                                                                type="number"
                                                                                                                v-model="editedItem.special_price"
                                                                                                                readonly
                                                                                                                outlined
                                                                                                                dense>
                                                                                                            </v-text-field>
                                                                                                        </v-col>
                                                                                                        <v-col cols="2">
                                                                                                            <v-btn
                                                                                                                color="amber accent-3"
                                                                                                                small
                                                                                                                class="ma-2 white--text"
                                                                                                                fab
                                                                                                                @click="addPrice3">
                                                                                                                <v-icon
                                                                                                                    dark>
                                                                                                                    mdi-bookmark-check
                                                                                                                </v-icon>
                                                                                                            </v-btn>
                                                                                                        </v-col>
                                                                                                    </v-row>
                                                                                                </v-container>
                                                                                            </v-card-text>
                                                                                        </v-card>
                                                                                    </v-dialog>
                                                                                </div>
                                                                            </template>
                                                                            <!--Dialog Lista de Precios end-->
                                                                        </v-col>
                                                                        <!--Dialog Lista de Precios END -->
                                                                    </v-row>
                                                                </div>
                                                            </v-container>
                                                        </v-card-text>
                                                        <v-divider></v-divider>
                                                        <v-card-actions>
                                                            <v-spacer></v-spacer>
                                                            <v-btn color="primary" text @click="save">
                                                                AÑADIR
                                                            </v-btn>
                                                            <v-btn color="red" text @click="close">
                                                                CERRAR
                                                            </v-btn>
                                                        </v-card-actions>
                                                    </v-card>
                                                </v-dialog>
                                            </div>
                                        </template>
                                    </v-col>
                                    <!-- Fin Dialog add Productos a carrito -->
                                    <v-col cols="12" sm="5">
                                        <v-textarea v-model="form.description" filled label="Comentario" type="text"
                                            rows="1" outlined>
                                        </v-textarea>
                                    </v-col>
                                    <!-- Tabla Carrito -->
                                    <v-col cols="12">
                                        <v-data-table :headers="headers" :items="desserts" sort-by="name"
                                            class="elevation-2" fixed-header :items-per-page="5">
                                            <template v-slot:[`item.full_name`]="{ item }">{{ item.datosProducto.name }}
                                                - {{ item.datosProducto.marks_name }} - {{
                                                        item.datosProducto.warehouses_name
                                                }}</template>
                                            <template v-slot:[`item.presentation`]="{ item }">{{ item.presentationName
                                            }}: {{ item.equivalence }} UND</template>
                                            <template v-slot:top>
                                                <v-toolbar flat>
                                                    <v-toolbar-title>Carrito de Productos</v-toolbar-title>
                                                    <v-divider class="mx-4" inset vertical></v-divider>
                                                    <v-spacer></v-spacer>
                                                    <v-spacer></v-spacer>
                                                    <!-- Mensaje de confirmacion de borrado de producto -->
                                                    <v-dialog v-model="dialogDelete" max-width="500px">
                                                        <v-card>
                                                            <v-card-title class="text-h5">¿Está seguro de querer
                                                                borrar el producto?
                                                            </v-card-title>
                                                            <v-card-actions>
                                                                <v-spacer></v-spacer>
                                                                <v-btn color="red darken-1" text @click="closeDelete">
                                                                    Cancelar</v-btn>
                                                                <v-btn color="blue darken-1" text
                                                                    @click="deleteItemConfirm">Aceptar</v-btn>
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
                                                <v-icon small @click="deleteItem(item)"> mdi-delete </v-icon>
                                            </template>
                                        </v-data-table>
                                    </v-col>
                                    <!-- Fin Tabla Carrito -->
                                </v-row>
                            </v-container>
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="red" dark @click="limpiarCarrito">
                                Cancelar
                            </v-btn>
                            <v-btn color="primary" @click="send_form()">
                                Confirmar
                            </v-btn>

                        </v-card-actions>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-container>
        <v-snackbar v-model="snackbar" :color="snackbar_color" right top>
            {{ snackbar_text }}
        </v-snackbar>
    </admin-layout>
</template>

<script>
import AdminLayout from '@/Layouts/AdminLayout'

export default {
    props: [
        'nro_cotizacion',
        'customers',
        'coins',
        'exchange_rate',
        'products',
        'presentations',
    ],
    components: {
        AdminLayout,
    },

    data() {
        return {
            datosCliente: '',
            simboloMoneda: 'S/',
            dialogAddProducts: false,
            dialogPago: false,
            dialogPrecios: false,
            dialogDelete: false,

            snackbar: false,
            snackbar_text: '',
            snackbar_color: '',

            igvs: [
                { tipo: 'IGV Incluido', id: 0 },
                { tipo: 'Más IGV', id: 1 }
            ],
            tipoCambio: this.exchange_rate,
            form: {
                companies_id: this.$page.props.user.companies_id,
                customers_id: '',
                date: (new Date(Date.now() - (new Date()).getTimezoneOffset() * 60000)).toISOString().substr(0, 10),
                nroCotizacion: this.nro_cotizacion,
                coins_id: this.coins[0].id,
                coins: this.coins[0],
                exchange_rate: '',
                tipo_igv: 'IGV Incluido',
                transporte: 0,
                descuento: 0,
                total: 0,
                description: '',
                products: '',
            },
            editedIndex: -1,
            desserts: [],
            editedItem: {
                datosProducto: '',
                productId: '',
                datosPresentation: '',
                datosPresentation: this.presentations[0],
                presentationId: this.presentations[0].id,
                presentationName: this.presentations[0].name,
                equivalence: this.presentations[0].equivalence,
                quantity: 1,
                igv: 0,
                sale_price: 0,
                price_by_unit: 0,
                wholesale_price: 0,
                special_price: 0,
                subTotal: 0,

            },
            defaultItem: {
                datosProducto: '',
                productId: '',
                datosPresentation: '',
                datosPresentation: this.presentations[0],
                presentationId: this.presentations[0].id,
                presentationName: this.presentations[0].name,
                quantity: 1,
                igv: 0,
                sale_price: 0,
                price_by_unit: 0,
                wholesale_price: 0,
                special_price: 0,
                subTotal: 0,

            },
            headers: [
                { text: 'PRODUCTO', value: 'full_name' },
                { text: 'PRESENTACIÓN', value: 'presentation' },
                { text: 'CANTIDAD', value: 'quantity' },
                { text: 'PRECIO UNITARIO', value: 'sale_price' },
                { text: 'IGV', value: 'igv' },
                { text: 'SUB TOTAL', value: 'subTotal' },
                { text: 'ACCIONES', value: 'actions', sortable: false },
            ],
        }
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Agregar Productos' : 'Editar Producto'
        }
    },
    watch: {
        dialog(val) {
            val || this.close();
        },
        dialogDelete(val) {
            val || this.closeDelete();
        },
    },
    created() {
        this.initialize()
    },
    methods: {
        initialize() {
            this.desserts = []
        },
        getProductText(item) {
            return `${item.bar_code} - ${item.name} - ${item.marks_name} - ${item.warehouses_name}`;
        },
        getPresentationText(item) {
            return `${item.name}: ${item.equivalence} UND`;
        },
        close() {
            this.dialogAddProducts = false;
            this.editedItem = Object.assign({}, this.defaultItem)
            this.editedIndex = -1
        },
        closeDialoPago() {
            this.dialogPago = false
        },
        closeDelete() {
            this.dialogDelete = false;
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem);
                this.editedIndex = -1;
            });
        },
        deleteItem(item) {
            this.editedIndex = this.desserts.indexOf(item);
            this.editedItem = Object.assign({}, item);
            this.dialogDelete = true;
        },
        deleteItemConfirm() {
            this.desserts.splice(this.editedIndex, 1);
            this.closeDelete();

            // reducir el monto total de la venta
            this.form.total -= this.editedItem.subTotal
            this.form.total = Number.parseFloat(this.form.total.toFixed(2))
        },
        changeMoneda() {
            if (this.form.coins.code == 'PEN') {
                this.simboloMoneda = 'S/'
                this.form.coins_id = this.form.coins.id
            } if (this.form.coins.code == 'USD') {
                this.simboloMoneda = '$'
                this.form.coins_id = this.form.coins.id
            }
        },
        changeProduct() {
            this.editedItem.sale_price = this.editedItem.datosProducto.sale_price
            this.editedItem.price_by_unit = this.editedItem.datosProducto.price_by_unit
            this.editedItem.wholesale_price = this.editedItem.datosProducto.wholesale_price
            this.editedItem.special_price = this.editedItem.datosProducto.special_price
            this.editedItem.productName = this.editedItem.datosProducto.name
            this.editedItem.productId = this.editedItem.datosProducto.id
            this.editedItem.type = this.editedItem.datosProducto.type
        },
        changePresentation() {
            this.editedItem.presentationId = this.editedItem.datosPresentation.id
            this.editedItem.presentationName = this.editedItem.datosPresentation.name
            this.editedItem.equivalence = this.editedItem.datosPresentation.equivalence
        },
        addPrice1() {
            this.editedItem.sale_price = this.editedItem.price_by_unit
            this.dialogPrecios = false
        },
        addPrice2() {
            this.editedItem.sale_price = this.editedItem.wholesale_price
            this.dialogPrecios = false
        },
        addPrice3() {
            this.editedItem.sale_price = this.editedItem.special_price
            this.dialogPrecios = false
        },
        editItem(item) {
            this.editedIndex = this.desserts.indexOf(item);
            this.editedItem = Object.assign({}, item);
            this.dialogAddProducts = true;
        },
        save() {
            if (this.editedIndex > -1) {
                // Actualizando precios segun compra
                this.form.total -= this.editedItem.subTotal
                this.form.total = Number.parseFloat(this.form.total.toFixed(2))
                // Calcular IGV incluido
                if (this.form.tipo_igv == 'IGV Incluido') {
                    var subTotalInicial = this.editedItem.quantity * this.editedItem.sale_price
                    var igvI = (subTotalInicial) * 0.18
                    this.editedItem.igv = Number.parseFloat(igvI.toFixed(2))
                    var subTotal = subTotalInicial
                } else {
                    var subTotalInicial = this.editedItem.quantity * this.editedItem.sale_price
                    var igvI = (subTotalInicial) * 0.18
                    var newSubtotal = subTotalInicial + igvI
                    this.editedItem.igv = Number.parseFloat(igvI.toFixed(2))
                    var subTotal = newSubtotal
                }
                // calculando nuevo precio
                if (this.form.coins.code == 'PEN') {
                    this.editedItem.subTotal = Number.parseFloat(subTotal.toFixed(2))
                } else {
                    this.editedItem.sale_price = Number.parseFloat(this.editedItem.sale_price / this.tipoCambio).toFixed(2)
                    this.editedItem.igv = Number.parseFloat(this.editedItem.igv / this.tipoCambio).toFixed(2)
                    this.editedItem.subTotal = Number.parseFloat((subTotal / Number.parseFloat(this.tipoCambio)).toFixed(2))
                }
                this.form.total += this.editedItem.subTotal
                this.form.total = Number.parseFloat(this.form.total.toFixed(2))
                Object.assign(this.desserts[this.editedIndex], this.editedItem)
                this.close()
            } else {
                // Calcular IGV incluido
                if (this.form.tipo_igv == 'IGV Incluido') {
                    var subTotalInicial = this.editedItem.quantity * this.editedItem.sale_price
                    var igvI = (subTotalInicial) * 0.18
                    this.editedItem.igv = Number.parseFloat(igvI.toFixed(2))
                    var subTotal = subTotalInicial
                } else {
                    var subTotalInicial = this.editedItem.quantity * this.editedItem.sale_price
                    var igvI = (subTotalInicial) * 0.18
                    var newSubtotal = subTotalInicial + igvI
                    this.editedItem.igv = Number.parseFloat(igvI.toFixed(2))
                    var subTotal = newSubtotal
                }
                // calculando nuevo precio               
                if (this.form.coins.code == 'PEN') {
                    this.editedItem.subTotal = Number.parseFloat(subTotal.toFixed(2))
                } else {
                    this.editedItem.sale_price = Number.parseFloat(this.editedItem.sale_price / this.tipoCambio).toFixed(2)
                    this.editedItem.igv = Number.parseFloat(this.editedItem.igv / this.tipoCambio).toFixed(2)
                    this.editedItem.subTotal = Number.parseFloat((subTotal / Number.parseFloat(this.tipoCambio)).toFixed(2))
                }
                this.form.total += this.editedItem.subTotal
                this.form.total = Number.parseFloat(this.form.total.toFixed(2))
                // fin agregar producto a editedItem

                // Valor inicial de precio de venta
                this.desserts.push(this.editedItem)
            }
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
            })
        },
        send_form() {
            if (this.form.coins.code == 'USD') {
                if (this.tipoCambio == '' || this.tipoCambio < 0) {
                    this.snackbar_text = 'Completar datos';
                    this.snackbar_color = 'red darken-4';
                    this.snackbar = true;
                    return;
                }
            }
            this.form.exchange_rate = this.tipoCambio
            this.form.customers_id = this.datosCliente.id
            this.form.products = this.desserts

            if (this.datosCliente == '') {
                this.snackbar_text = 'Datos de Cliente Vacio';
                this.snackbar_color = 'green darken-1';
                this.snackbar = true;
                return;
            }
            if (this.form.products == '') {
                this.snackbar_text = 'Carrito Vacio';
                this.snackbar_color = 'amber lighten-1';
                this.snackbar = true;
                return;
            }
            this.$inertia.post(route('quotations.store'), this.form)
        },
        limpiarCarrito() {
            this.desserts = []
            this.datosCliente = ''
            this.form.total = 0
            this.form.coins = this.coins[0]
            this.form.tipo_igv='IGV Incluido'
            this.tipoCambio=this.exchange_rate
            this.simboloMoneda = 'S/'
            this.form.description = ''
        },
    },
}
</script>