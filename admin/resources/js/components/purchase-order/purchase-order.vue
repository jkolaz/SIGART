<template>
    <div>
        <section class="hk-sec-wrapper">
            <h5 class="hk-sec-title">Ordenes de Compras</h5>
            <div class="row">
                <div class="col-sm">
                    <form class="form-inline">
                        <div class="form-row align-items-left">
                            <div class="col-auto">
                                <label class="sr-only" for="searchForm"></label>
                                <input type="text" id="searchForm" class="form-control mb-2" placeholder="Buscar" v-model="search" @keyup="list( 1, search )">
                            </div>
                            <div class="col-auto" >
                                <button type="submit" class="btn btn-primary mb-2" @click.prevent="list( 1, search )">
                                    <i class="fa fa-fw fa-lg fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <section class="hk-sec-wrapper">
            <div class="row">
                <div class="row">
                    <div class="col-sm">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link" :class="step === 1 ? 'active' : ''" href="#req" @click="changeTab( 1 )">Pendientes de aprobación</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="step === 2 ? 'active' : ''" href="#solc" @click="changeTab( 2 )">Aprobadas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="step === 3 ? 'active' : ''" href="#regQu" @click="changeTab( 3 )">Anuladas</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <section class="hk-sec-wrapper">
            <h6 class="hk-sec-title">Listado</h6>
            <div class="row">
                <div class="col-sm">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Opciones</th>
                                    <th>Código</th>
                                    <th>Proveedor</th>
                                    <th>Sub-Total</th>
                                    <th>IGV</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="( row, idx ) in arrData" :key="row.id">
                                    <td v-text="idx + 1"></td>
                                    <td>
                                        <button v-if="row.status === 1" type="button" class="btn btn-outline-info btn-sm" title="Aprobar Orden de Compra" @click.prevent="approve( row )">
                                            <i class="fa fa-fw fa-lg fa-check"></i> Aprobar
                                        </button>
                                        <button v-if="row.status !== 2" type="button"
                                                class="btn btn-outline-danger btn-sm" title="Anular Orden de Compra"
                                                @click.prevent="cancel( row )"
                                        >
                                            <i class="fa fa-fw fa-lg fa-close"></i> Anular
                                        </button>
                                        <button v-if="row.status !== 2" type="button" class="btn btn-outline-danger btn-sm"
                                                @click.prevent="generatePdf( row.id )"
                                                title="Cancelar Orden de Compra">
                                            <i class="fa fa-fw fa-lg fa-file-pdf-o"></i> Generar PDF
                                        </button>
                                        <button v-if="row.status !== 2 && row.status !== 1 && row.status !== 4" type="button" class="btn btn-outline-info btn-sm"
                                                title="Cancelar Orden de Compra"
                                                @click="forwardMailModal( row )"
                                        >
                                            <i class="fa fa-fw fa-lg fa-mail-forward"></i> Reenviar correo
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                @click.prevent="openModalShow( row.id )"
                                                title="Detalle - Orden de Compra">
                                            <i class="fa fa-fw fa-lg fa-search"></i> Detalle
                                        </button>
                                        <a v-if="row.pdf" :href="asset + '/' + row.pdf" target="_blank" class="btn btn-outline-info btn-sm" title="Ver PDF - Orden de Compra">
                                            <i class="fa fa-fw fa-lg fa-file-pdf-o"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td>{{ row.code }}</td>
                                    <td>
                                        {{ row.name }}
                                        <span class="badge badge-secondary">{{ row.document }}</span>
                                    </td>
                                    <td>{{ row.subtotal }}</td>
                                    <td>{{ row.igv }}</td>
                                    <td>{{ row.total }}</td>
                                    <td>
                                        <span v-if="row.status === 0"class="badge badge-warning"><i class="fa fa-ban"></i>Desactivado</span>
                                        <span v-if="row.status === 1" class="badge badge-info"><i class="fa fa-check fa-fw"></i>Pendiente Aprobación</span>
                                        <span v-if="row.status === 2" class="badge badge-danger"><i class="fa fa-check fa-fw"></i>Anulado</span>
                                        <span v-if="row.status === 3" class="badge badge-success"><i class="fa fa-shopping-bag fa-fw"></i>Aprobado</span>
                                        <span v-if="row.status === 4" class="badge badge-danger"><i class="fa fa-check fa-fw"></i>Concretado</span>
                                        <span v-if="row.status === 5" class="badge badge-primary"><i class="fa fa-close fa-fw"></i>Compra Generado</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="hk-sec-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item" v-if="pagination.current_page > 1">
                                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page-1, search)">Ant.</a>
                            </li>
                            <li class="page-item" v-for="page in pagesNumber" :key="page" :class="[page == isActived ? 'active' : '']">
                                <a class="page-link" href="#" @click.prevent="changePage(page, search)" v-text="page"></a>
                            </li>
                            <li class="page-item" v-if="pagination.current_page < pagination.last_page">
                                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page+1, search)">Sig.</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>
        <b-modal id="modalShow" size="lg" ref="modalShow" :title="titleModal" @ok="closeShow" @cancel="closeShow">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>PDF</th>
                                    <th>Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="info.purchaseOrder">
                                    <td>{{ info.purchaseOrder.code }}</td>
                                    <td>{{ info.purchaseOrder.date }}</td>
                                    <td>
                                        <a :href="info.purchaseOrder.pdf" target="_blank">
                                            <i class="fa fa-file-pdf-o"></i>&nbsp;{{ info.purchaseOrder.pdf }}
                                        </a>
                                    </td>
                                    <td>
                                        <span v-if="info.purchaseOrder.status === 0"class="badge badge-warning">
                                            <i class="fa fa-ban"></i>Desactivado
                                        </span>
                                        <span v-if="info.purchaseOrder.status === 1" class="badge badge-info">
                                            <i class="fa fa-check fa-fw"></i>Pendiente Aprobación
                                        </span>
                                        <span v-if="info.purchaseOrder.status === 2" class="badge badge-danger">
                                            <i class="fa fa-check fa-fw"></i>Anulado
                                        </span>
                                        <span v-if="info.purchaseOrder.status === 3" class="badge badge-success">
                                            <i class="fa fa-shopping-bag fa-fw"></i>Aprobado
                                        </span>
                                        <span v-if="info.purchaseOrder.status === 4" class="badge badge-danger">
                                            <i class="fa fa-check fa-fw"></i>Concretado
                                        </span>
                                        <span v-if="info.purchaseOrder.status === 5" class="badge badge-primary">
                                            <i class="fa fa-close fa-fw"></i>Compra Generado
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>N° Doc.</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="info.provider">
                                    <td>{{ info.provider.name }}</td>
                                    <td>{{ info.provider.doc }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th colspan="5">Detalle de orden de compra</th>
                                </tr>
                                <tr>
                                    <th>Item</th>
                                    <th>Detalle</th>
                                    <th>Cant.</th>
                                    <th>P/U</th>
                                    <th>Sub-Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-if="info.details" v-for="( det, idx ) in info.details" :key="det.id">
                                    <td>{{ idx + 1 }}</td>
                                    <td>{{ det.category }} {{ det.product }} {{ det.description }}</td>
                                    <td>{{ det.quantity }} {{ det.unity }}</td>
                                    <td>{{ det.price_unit }}</td>
                                    <td>{{ det.total }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        name: "purchase-order",
        data() {
            return {
                step: 1,
                search: '',
                arrData:    [],
                pagination  : {
                    'total'         : 0,
                    'current_page'  : 0,
                    'per_page'      : 0,
                    'last_page'     : 0,
                    'from'          : 0,
                    'to'            : 0,
                },
                offset      : 3,
                titleModal  : '',
                info: []
            }
        },
        props: [
            'asset'
        ],
        computed:{
            isActived: function(){
                return this.pagination.current_page;
            },
            pagesNumber: function() {
                if(!this.pagination.to) {
                    return [];
                }

                var from = this.pagination.current_page - this.offset;
                if(from < 1) {
                    from = 1;
                }

                var to = from + (this.offset * 2);
                if(to >= this.pagination.last_page){
                    to = this.pagination.last_page;
                }

                var pagesArray = [];
                while(from <= to) {
                    pagesArray.push(from);
                    from++;
                }
                return pagesArray;

            }
        },
        methods: {
            changeTab( tab ) {
                if( this.step !== tab ) {
                    this.step = tab;
                    this.list( 1, this.search );
                }
            },
            generatePdf( id ) {
                let me = this;
                let url = '/purchase-order/' + id + '/generatePDF/';
                axios.get( url ).then( function( response ) {
                    let result = response.data;
                    if( result.status ) {
                        me.list( 1, '' );
                    }
                }).catch( function ( errors ) {
                    console.log( errors );
                });
            },
            changePage( page, search ){
                let me = this;
                me.pagination.current_page = page;
                me.list( page, search );
            },
            list( page, search ){
                let me = this,
                    url = '/purchase-order/';

                axios.get( url, {
                    params: {
                        page: page,
                        search: search,
                        status: me.step
                    }
                }).then( function ( result ) {
                    let response    = result.data;
                    me.arrData      = response.records.data;
                    me.pagination   = response.pagination;
                }).catch( function ( errors ) {
                    console.log( errors );
                })
            },
            approve( data ){
                swal({
                    title: "Aprobar Orden de compra",
                    text: "Esta seguro de aprobar la orden de compra para el proveedor " + data.name + "?",
                    icon: "success",
                    button: "Aprobar",
                }).then( ( result ) => {
                    let me = this,
                        url = '/purchase-order/approve/';

                    axios.post( url, {
                        id: data.id
                    }).then( function( result ) {
                        let response = result.data;
                        if( response.status ) {
                            me.list( 1, me.search );
                            swal(
                                'Aprobado!',
                                'Se aprobó correctamente la orden de compra.',
                                'success'
                            )
                        } else {
                            swal(
                                'Error! :(',
                                'No se pudo realizar la operación',
                                'error'
                            );
                        }
                    }).catch( function( errors ) {
                        console.log( errors );
                    });
                })
            },
            forwardMailModal( data ) {
                let me = this;
                swal({
                    title: "Reenviar Correo!",
                    text: "Estas seguro de reenviar la orden de compra a " + data.name + "?",
                    icon: "success",
                    button: "Reenviar"
                }).then((result) => {
                    if (result) {
                        me.forwardMail( data.id );
                    }
                })
            },
            forwardMail( id ) {
                let me = this,
                    url = '/purchase-order/' + id + '/forward-mail';
                axios.post( url ).then( function( response ) {
                    let resp = response.data;
                    if( resp.status ) {
                        swal(
                            'Correo reenviado!',
                            'Se reenvio con éxito la orden de compra.',
                            'success'
                        )
                    } else {
                        swal(
                            'Error! :(',
                            'No se pudo realizar la operación.',
                            'error'
                        )
                    }
                }).catch( function ( errors ) {
                    console.log( errors );
                    swal(
                        'Error! :(',
                        'No se pudo realizar la operación.',
                        'error'
                    )
                })
            },
            openModalShow( id ) {
                let me = this,
                    url = '/purchase-order/' + id + '/show/';

                axios.get( url ).then(function (result) {
                    let response = result.data;
                    if( response.status ) {
                        me.titleModal = 'Orden de compra: ' + response.info.code;
                        me.info = response.info;
                        me.$refs.modalShow.show();
                    }
                }).catch(function (errors) {
                    console.log(errors);
                });
            },
            closeShow() {
                this.info = [];
                this.$refs.modalShow.hide();
            },
            cancel( data ) {
                swal({
                    title: "Anular!",
                    text: "Esta seguro de anular la Orden de entrada " + data.code + "?",
                    icon: "error",
                    dangerMode: true,
                    buttons: {
                        cancel: "Cancelar!",
                        suceess: {
                            text: "Eliminar",
                            value: true,
                        }
                    },
                }).then((result) => {
                    if (result) {
                        let me = this;
                        axios.get('/purchase-order/' + data.id + '/cancel').then(function ( response ) {
                            me.list( 1, me.search );
                            swal(
                                'Anulado!',
                                'El registro ha sido anulado con éxito.',
                                'success'
                            )
                        }).catch(function (error) {
                            console.log(error);
                        });


                    } else if (
                        // Read more about handling dismissals
                        result.dismiss === swal.DismissReason.cancel
                    ) {

                    }
                })
            }
        },
        mounted() {
            this.list( 1, '' );
        }
    }
</script>

<style scoped>

</style>
